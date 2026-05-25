<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// LOGIKA SORTING
$allowed_sort_columns = ['quotation_code', 'customer_name', 'updated_at', 'status', 'grand_total'];
$sort_column = 'updated_at'; $sort_order = 'DESC';
if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_columns)) { $sort_column = $_GET['sort']; }
if (isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC'])) { $sort_order = strtoupper($_GET['order']); }

// QUERY RECURSIVE UNTUK GROUPING
$query_all = "
    WITH RECURSIVE QuotationHierarchy AS (
        SELECT id, parent_quotation_id, id as root_id FROM quotations WHERE parent_quotation_id IS NULL
        UNION ALL
        SELECT q.id, q.parent_quotation_id, h.root_id FROM quotations q INNER JOIN QuotationHierarchy h ON q.parent_quotation_id = h.id
    )
    SELECT q.*, c.name AS customer_name, h.root_id
    FROM quotations q
    JOIN customer c ON q.customer_id = c.id
    JOIN QuotationHierarchy h ON q.id = h.id
    WHERE q.deleted_at IS NULL
    ORDER BY h.root_id, q.updated_at DESC;
";
$result_all = $conn->query($query_all);
if (!$result_all) die("Error: " . $conn->error);

// Proses data mentah menjadi array yang terkelompok
$grouped_quotations = [];
while ($row = $result_all->fetch_assoc()) {
    $grouped_quotations[$row['root_id']][] = $row;
}

// Lakukan sorting pada array hasil grouping di PHP
usort($grouped_quotations, function($a, $b) use ($sort_column, $sort_order) {
    $val_a = $a[0][$sort_column];
    $val_b = $b[0][$sort_column];
    if ($sort_order == 'ASC') {
        return $val_a <=> $val_b;
    } else {
        return $val_b <=> $val_a;
    }
});
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Dashboard Penawaran</h2>
        <a href="pages/quotation/create.php" class="btn btn-primary">+ Buat Penawaran Baru</a>
    </div>

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <?php
                        // FUNGSI SORT_LINK YANG DIPERBAIKI: Menggunakan 'return' bukan 'echo'
                        function sort_link($label, $column, $current_col, $current_order) {
                            $order = ($current_col == $column && $current_order == 'ASC') ? 'DESC' : 'ASC';
                            $icon = ($current_col == $column) ? ($current_order == 'ASC' ? ' ▲' : ' ▼') : '';
                            return "<a href=\"?sort=$column&order=$order\" class=\"text-decoration-none text-dark\">$label$icon</a>";
                        }
                        ?>
                        <th><?php echo sort_link('Kode Penawaran', 'quotation_code', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Customer', 'customer_name', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Tanggal Update', 'updated_at', $sort_column, $sort_order); ?></th>
                        <th><?php echo sort_link('Status', 'status', $sort_column, $sort_order); ?></th>
                        <th class="text-end"><?php echo sort_link('Grand Total', 'grand_total', $sort_column, $sort_order); ?></th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouped_quotations)): ?>
                        <?php foreach ($grouped_quotations as $root_id => $versions): ?>
                            <?php $latest_version = $versions[0]; ?>
                            <?php $history_versions = array_slice($versions, 1); ?>
                            <tr>
                                <td>
                                    <a class="fw-bold text-decoration-none" data-bs-toggle="collapse" href="#history-<?php echo $root_id; ?>">
                                        <?php echo htmlspecialchars($latest_version['quotation_code'] ?? 'DRAFT-'.$latest_version['id']); ?>
                                        <?php if (!empty($history_versions)): ?>
                                            <span class="badge bg-dark rounded-pill ms-1"><?php echo count($versions); ?> Versi</span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($latest_version['customer_name']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($latest_version['updated_at'])); ?></td>
                                <td>
                                    <?php $status=$latest_version['status']; $badge_class='bg-secondary'; if($status=='FINAL')$badge_class='bg-info'; if($status=='APPROVED')$badge_class='bg-success'; if($status=='REJECTED')$badge_class='bg-danger'; ?>
                                    <span class="badge <?php echo $badge_class; ?> status-badge"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td class="text-end">Rp <?php echo number_format($latest_version['grand_total'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="pages/quotation/print.php?id=<?php echo $latest_version['id']; ?>" class="btn btn-sm btn-info" title="Lihat/Cetak" target="_blank">👁️</a>
                                        <a href="pages/quotation/edit.php?id=<?php echo $latest_version['id']; ?>" class="btn btn-sm btn-warning" title="Edit Versi Ini">✏️</a>
                                        <a href="pages/quotation/delete.php?id=<?php echo $latest_version['id']; ?>" class="btn btn-sm btn-danger" title="Hapus Seluruh Riwayat" onclick="return confirm('Anda yakin ingin menghapus seluruh riwayat penawaran ini?')">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                            <?php if (!empty($history_versions)): ?>
                            <tr class="collapse" id="history-<?php echo $root_id; ?>"><td colspan="6" class="p-0" style="background-color:#f8f9fa;"><div class="p-3">
                                <h6 class="mb-2 ms-1">Riwayat untuk penawaran ini:</h6>
                                <table class="table table-sm table-bordered bg-white mb-0">
                                <thead class="table-light"><tr><th>Kode/ID</th><th>Tgl Update</th><th>Status</th><th class="text-end">Total</th><th class="text-center">Aksi</th></tr></thead>
                                <tbody>
                                <?php foreach ($history_versions as $history_row): ?>
                                    <tr class="opacity-75">
                                        <td class="fw-light"><?php echo htmlspecialchars($history_row['quotation_code'] ?? 'DRAFT-'.$history_row['id']); ?></td>
                                        <td class="fw-light"><?php echo date('d M Y, H:i', strtotime($history_row['updated_at'])); ?></td>
                                        <td><span class="badge <?php echo ($history_row['status']=='FINAL')?'bg-info':'bg-secondary'; ?>"><?php echo htmlspecialchars($history_row['status']); ?></span></td>
                                        <td class="text-end fw-light">Rp <?php echo number_format($history_row['grand_total'],0,',','.'); ?></td>
                                        <td class="text-center">
                                            <a href="pages/quotation/print.php?id=<?php echo $history_row['id']; ?>" class="btn btn-xs btn-outline-secondary" title="Lihat/Cetak Riwayat" target="_blank">👁️</a>
                                            <a href="pages/quotation/edit.php?id=<?php echo $history_row['id']; ?>" class="btn btn-xs btn-outline-secondary" title="Edit Dari Versi Ini">✏️</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody></table></div></td></tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data penawaran. <br><a href="pages/quotation/create.php">Buat penawaran pertama Anda!</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div></div>
</div>

<?php include 'includes/footer.php'; ?>