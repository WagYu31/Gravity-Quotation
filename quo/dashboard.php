<?php
// Memanggil file-file penting
include 'includes/db.php';
include 'includes/header.php';

// Keamanan: Pastikan hanya user yang sudah login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil role dan ID user yang sedang login dari session
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'admin'; // Default ke 'admin' jika tidak ada


// --- 1. LOGIKA UNTUK SORTING ---
$allowed_sort_columns = ['issuer', 'quotation_code', 'customer_name', 'user_name', 'updated_at', 'status', 'grand_total'];
$sort_column = 'updated_at'; 
$sort_order = 'DESC';
if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_columns)) {
    $sort_column = $_GET['sort'];
}
if (isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC'])) {
    $sort_order = strtoupper($_GET['order']);
}

// --- 2. LOGIKA HAK AKSES (WHERE CLAUSE) ---
$where_clause = '';
if ($user_role !== 'superadmin') {
    // Jika bukan superadmin, tambahkan kondisi untuk hanya menampilkan data milik sendiri
    $where_clause = "AND q.user_id = " . (int)$user_id;
}

// --- 3. QUERY UTAMA DENGAN RECURSIVE CTE UNTUK MENGAMBIL SEMUA DATA ---
$query_all = "
    WITH RECURSIVE QuotationHierarchy AS (
        SELECT id, parent_quotation_id, id as root_id FROM quotations WHERE parent_quotation_id IS NULL
        UNION ALL
        SELECT q.id, q.parent_quotation_id, h.root_id FROM quotations q INNER JOIN QuotationHierarchy h ON q.parent_quotation_id = h.id
    )
    SELECT 
        q.*, 
        c.name AS customer_name,
        u.name AS user_name, -- Mengambil nama user yang membuat penawaran
        h.root_id
    FROM quotations q
    JOIN customer c ON q.customer_id = c.id
    JOIN users u ON q.user_id = u.id -- JOIN dengan tabel users
    JOIN QuotationHierarchy h ON q.id = h.id
    WHERE q.deleted_at IS NULL {$where_clause} -- Terapkan filter hak akses di sini
    ORDER BY h.root_id, q.updated_at DESC;
";
$result_all = $conn->query($query_all);
if (!$result_all) {
    die("Error saat mengambil data penawaran: " . $conn->error);
}

// --- 4. PROSES DATA MENTAH MENJADI ARRAY YANG TERKELOMPOK ---
$grouped_quotations = [];
while ($row = $result_all->fetch_assoc()) {
    $grouped_quotations[$row['root_id']][] = $row;
}

// --- 5. LAKUKAN SORTING PADA ARRAY HASIL GROUPING DI PHP ---
usort($grouped_quotations, function($a, $b) use ($sort_column, $sort_order) {
    $val_a = $a[0][$sort_column];
    $val_b = $b[0][$sort_column];
    if ($sort_order == 'ASC') {
        return strcasecmp((string)$val_a, (string)$val_b);
    } else {
        return strcasecmp((string)$val_b, (string)$val_a);
    }
});
?>

<?php
// Count stats
$total_count = 0; $draft_count = 0; $approved_count = 0; $total_revenue = 0;
foreach ($grouped_quotations as $versions) {
    $latest = $versions[0];
    $total_count++;
    if ($latest['status'] === 'DRAFT') $draft_count++;
    if ($latest['status'] === 'APPROVED') $approved_count++;
    $total_revenue += $latest['grand_total'];
}
?>

<div class="container-fluid mt-4 px-4" id="dashboard-page">
    <!-- Hero Header -->
    <div class="dashboard-hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Dashboard Penawaran</h1>
                <p class="hero-subtitle">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</p>
            </div>
            <a href="pages/quotation/create.php" class="btn btn-hero-create">
                <i class="bi bi-plus-lg"></i> Buat Penawaran Baru
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-card stat-total">
            <div class="stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $total_count; ?></span>
                <span class="stat-label">Total Penawaran</span>
            </div>
        </div>
        <div class="stat-card stat-draft">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $draft_count; ?></span>
                <span class="stat-label">Draft</span>
            </div>
        </div>
        <div class="stat-card stat-approved">
            <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?php echo $approved_count; ?></span>
                <span class="stat-label">Approved</span>
            </div>
        </div>
        <div class="stat-card stat-revenue">
            <div class="stat-icon"><i class="bi bi-currency-exchange"></i></div>
            <div class="stat-info">
                <span class="stat-number"><?php echo 'Rp ' . number_format($total_revenue, 0, ',', '.'); ?></span>
                <span class="stat-label">Total Revenue</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="dashboard-toolbar">
        <div class="toolbar-search">
            <span class="search-prefix-icon"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="toolbar-search-input" placeholder="Cari kode, customer, atau status...">
        </div>
        <div class="toolbar-filters">
            <button type="button" class="filter-pill active" data-filter="all">Semua</button>
            <button type="button" class="filter-pill" data-filter="DRAFT">Draft</button>
            <button type="button" class="filter-pill" data-filter="FINAL">Final</button>
            <button type="button" class="filter-pill" data-filter="APPROVED">Approved</button>
            <button type="button" class="filter-pill" data-filter="COMPLETED">Completed</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="dashboard-table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle dashboard-table">
                <thead>
                    <tr>
                        <?php if (!function_exists('sort_link')) { function sort_link($label, $column, $current_col, $current_order) { $order = ($current_col == $column && $current_order == 'ASC') ? 'DESC' : 'ASC'; $icon = ($current_col == $column) ? ($current_order == 'ASC' ? ' ▲' : ' ▼') : ''; return "<a href=\"?sort=$column&order=$order\" class=\"sort-link\">$label$icon</a>"; } } ?>
                        <th><?php echo sort_link('Issuer', 'issuer', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Kode Penawaran', 'quotation_code', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Customer', 'customer_name', $sort_column, $sort_order); ?></th>
                        <?php if ($user_role === 'superadmin'): ?>
                            <th><?php echo sort_link('Dibuat Oleh', 'user_name', $sort_column, $sort_order); ?></th>
                        <?php endif; ?>
                        <th><?php echo sort_link('Tanggal Update', 'updated_at', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Status', 'status', $sort_column, $sort_order); ?></th>
                        <th class="text-end"><?php echo sort_link('Grand Total', 'grand_total', $sort_column, $sort_order); ?></th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="dashboard-table-body">
                    <?php if (!empty($grouped_quotations)): foreach ($grouped_quotations as $root_id => $versions): ?>
                        <?php $latest_version = $versions[0]; $history_versions = array_slice($versions, 1); ?>
                        <tr class="main-row">
                            <td><span class="issuer-badge"><?php echo htmlspecialchars($latest_version['issuer']); ?></span></td>
                            <td>
                                <a class="code-link" data-bs-toggle="collapse" href="#history-<?php echo $root_id; ?>" title="Klik untuk lihat riwayat">
                                    <?php echo htmlspecialchars($latest_version['quotation_code'] ?? 'DRAFT-'.$latest_version['id']); ?>
                                    <?php if (!empty($history_versions)): ?><span class="version-badge"><?php echo count($versions); ?> Versi</span><?php endif; ?>
                                </a>
                            </td>
                            <td class="customer-name"><?php echo htmlspecialchars($latest_version['customer_name']); ?></td>
                            <?php if ($user_role === 'superadmin'): ?>
                                <td class="user-name"><?php echo htmlspecialchars($latest_version['user_name']); ?></td>
                            <?php endif; ?>
                            <td class="date-cell"><?php echo date('d M Y, H:i', strtotime($latest_version['updated_at'])); ?></td>
                            <td>
                                <?php $status=$latest_version['status']; $badge_class='status-default'; if($status=='DRAFT')$badge_class='status-draft'; if($status=='FINAL')$badge_class='status-final'; if($status=='APPROVED')$badge_class='status-approved'; if($status=='REJECTED'||$status=='CANCELED')$badge_class='status-rejected'; if($status=='COMPLETED')$badge_class='status-completed'; ?>
                                <span class="status-pill <?php echo $badge_class; ?> status-badge"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td class="text-end grand-total-cell">Rp <?php echo number_format($latest_version['grand_total'], 0, ',', '.'); ?></td>
                            <td class="text-center action-cell">
                                <div class="action-group">
                                    <a href="pages/quotation/print.php?id=<?php echo $latest_version['id']; ?>" class="action-btn action-print" title="Cetak" target="_blank"><i class="bi bi-printer"></i></a>
                                    <a href="pages/quotation/edit.php?id=<?php echo $latest_version['id']; ?>" class="action-btn action-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <?php if ($latest_version['status'] == 'FINAL'): ?>
                                        <a href="pages/quotation/actions.php?action=approve&id=<?php echo $latest_version['id']; ?>" class="action-btn action-approve" title="Setujui" onclick="return confirm('Anda yakin ingin menyetujui penawaran ini?')"><i class="bi bi-check-lg"></i></a>
                                    <?php elseif (in_array($latest_version['status'], ['APPROVED', 'COMPLETED', 'CANCELED'])): ?>
                                        <a href="pages/quotation/progress.php?id=<?php echo $latest_version['id']; ?>" class="action-btn action-progress" title="Lihat Progress"><i class="bi bi-rocket-takeoff"></i></a>
                                    <?php endif; ?>
                                    <a href="pages/quotation/delete.php?id=<?php echo $latest_version['id']; ?>" class="action-btn action-delete" title="Hapus" onclick="return confirm('PERHATIAN: Seluruh riwayat untuk penawaran ini akan dihapus. Lanjutkan?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($history_versions)): ?>
                        <tr class="collapse history-row" id="history-<?php echo $root_id; ?>">
                            <td colspan="<?php echo ($user_role === 'superadmin') ? '8' : '7'; ?>" class="p-0 history-cell">
                                <div class="history-panel">
                                    <h6 class="history-title"><i class="bi bi-clock-history"></i> Riwayat Versi</h6>
                                    <table class="table table-sm history-table mb-0">
                                        <thead><tr><th>Kode/ID</th><th>Tgl Update</th><th>Status</th><th class="text-end">Total</th><th class="text-center">Aksi</th></tr></thead>
                                        <tbody><?php foreach ($history_versions as $history_row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($history_row['quotation_code'] ?? 'DRAFT-'.$history_row['id']); ?></td>
                                                <td><?php echo date('d M Y, H:i', strtotime($history_row['updated_at'])); ?></td>
                                                <td><span class="status-pill status-mini <?php echo ($history_row['status']=='FINAL')?'status-final':'status-default'; ?>"><?php echo htmlspecialchars($history_row['status']); ?></span></td>
                                                <td class="text-end">Rp <?php echo number_format($history_row['grand_total'],0,',','.'); ?></td>
                                                <td class="text-center">
                                                    <a href="pages/quotation/print.php?id=<?php echo $history_row['id']; ?>" class="action-btn action-btn-sm" title="Cetak" target="_blank"><i class="bi bi-printer"></i></a>
                                                    <a href="pages/quotation/edit.php?id=<?php echo $history_row['id']; ?>" class="action-btn action-btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?></tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; else: ?>
                        <tr><td colspan="<?php echo ($user_role === 'superadmin') ? '8' : '7'; ?>" class="text-center text-muted py-5 empty-state">
                            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                            <p class="empty-text">Belum ada data penawaran ditemukan.</p>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// JavaScript untuk Search dan Filter Status
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-pill');
    const tableBody = document.getElementById('dashboard-table-body');
    const mainRows = tableBody.querySelectorAll('tr.main-row');

    function performFilter() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-pill.active').getAttribute('data-filter').toUpperCase();

        mainRows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const statusBadge = row.querySelector('.status-badge').textContent.toUpperCase().trim();

            const matchesSearch = textContent.includes(searchTerm);
            const matchesFilter = (activeFilter === 'ALL' || statusBadge === activeFilter);

            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                const collapseLink = row.querySelector('[data-bs-toggle="collapse"]');
                if (collapseLink) {
                    const historyRow = document.querySelector(collapseLink.getAttribute('href'));
                    if(historyRow) historyRow.classList.remove('show');
                }
            }
        });
    }

    searchInput.addEventListener('keyup', performFilter);
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            performFilter();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>