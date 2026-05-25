<?php
include '../../includes/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
$result = $conn->query("SELECT * FROM customer WHERE deleted_at IS NULL ORDER BY name ASC");
$total_customer = $result->num_rows;
?>

<div class="gv-main-content" style="padding: 28px 36px;">

    <!-- Hero Banner -->
    <div class="dashboard-hero" style="margin-bottom: 28px;">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Manajemen Data Customer</h1>
                <p class="hero-subtitle">Total <?php echo $total_customer; ?> customer terdaftar dalam sistem</p>
            </div>
            <a href="create.php" class="btn btn-hero-create">
                <i class="bi bi-plus-lg"></i> Tambah Customer
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="dashboard-table-card">
        <!-- Filter Bar -->
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select id="length-select" class="form-select" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px; color: #64748b;">
                        <option value="10">Tampilkan 10 data</option>
                        <option value="25">Tampilkan 25 data</option>
                        <option value="50">Tampilkan 50 data</option>
                        <option value="100">Tampilkan 100 data</option>
                        <option value="-1">Tampilkan Semua</option>
                    </select>
                </div>
                <div class="col-md-4 ms-auto">
                    <div style="position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                        <input type="text" id="search-input" class="form-control" placeholder="Cari nama, alamat, email, no. telp..."
                            style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 10px 14px 10px 40px !important; color: #1e293b;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle dashboard-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Customer</th>
                        <th>Alamat</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    <?php if ($result->num_rows > 0): $no = 1; while($row = $result->fetch_assoc()): ?>
                        <tr class="main-row">
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; font-weight: 700; color: #94a3b8; padding: 14px 18px;"><?php echo $no++; ?></td>
                            <td style="padding: 14px 18px;">
                                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['name']); ?></div>
                                <?php if (!empty($row['store_name'])): ?>
                                    <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; color: #94a3b8; margin-top: 2px;"><?php echo htmlspecialchars($row['store_name']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #64748b; padding: 14px 18px; max-width: 250px;"><?php echo htmlspecialchars($row['address']); ?></td>
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #64748b; padding: 14px 18px;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding: 14px 18px;">
                                <?php
                                $raw_phone = trim($row['phone_number']);
                                if (!empty($raw_phone)) {
                                    $sanitized_for_link = preg_replace('/[^0-9]/', '', $raw_phone);
                                    $whatsapp_number = $sanitized_for_link;
                                    if (substr($sanitized_for_link, 0, 1) === '0') {
                                        $whatsapp_number = '62' . substr($sanitized_for_link, 1);
                                    }
                                    $display_number = $raw_phone;
                                    if (substr($sanitized_for_link, 0, 2) === '62') {
                                        $display_number = '0' . substr($sanitized_for_link, 2);
                                    }
                                    echo '<a href="https://wa.me/' . $whatsapp_number . '" target="_blank" style="text-decoration: none; font-family: Plus Jakarta Sans, sans-serif; font-size: 12px; color: #1e293b; font-weight: 600;">';
                                    echo '<i class="bi bi-whatsapp" style="color: #22c55e; margin-right: 4px;"></i>';
                                    echo htmlspecialchars($display_number);
                                    echo '</a>';
                                } else {
                                    echo '<span style="color: #cbd5e1;">-</span>';
                                }
                                ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div class="action-group">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="actions.php?action=delete&id=<?php echo $row['id']; ?>" class="action-btn action-delete" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus customer ini?')"><i class="bi bi-trash-fill"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding: 40px; font-family: 'Plus Jakarta Sans', sans-serif;">Belum ada data customer.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <span id="pagination-info" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #94a3b8;"></span>
            <nav>
                <ul class="pagination mb-0" id="pagination-controls" style="gap: 4px;"></ul>
            </nav>
        </div>
    </div>
</div>

<style>
    #pagination-controls .page-item .page-link {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        padding: 6px 12px;
        transition: all 0.2s;
    }
    #pagination-controls .page-item.active .page-link {
        background: linear-gradient(135deg, #facc15, #eab308);
        border-color: transparent;
        color: #1e293b;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(234,179,8,0.3);
    }
    #pagination-controls .page-item .page-link:hover {
        background: #fefce8;
        border-color: #facc15;
        color: #1e293b;
    }
    #pagination-controls .page-item.disabled .page-link {
        color: #cbd5e1;
        background: #f9fafb;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const lengthSelect = document.getElementById('length-select');
    const tableBody = document.getElementById('customer-table-body');
    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    const paginationInfo = document.getElementById('pagination-info');
    const paginationControls = document.getElementById('pagination-controls');
    
    let currentPage = 1;
    let filteredRows = [];

    function renderPagination() {
        paginationControls.innerHTML = '';
        const itemsPerPage = parseInt(lengthSelect.value, 10);
        if (itemsPerPage === -1) {
            paginationInfo.textContent = `Menampilkan ${filteredRows.length} dari ${allRows.length} total data.`;
            return;
        }

        const pageCount = Math.ceil(filteredRows.length / itemsPerPage);
        if (pageCount <= 1) {
            paginationInfo.textContent = `Menampilkan ${filteredRows.length} dari ${allRows.length} total data.`;
            return;
        }

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}"><i class="bi bi-chevron-left"></i></a>`;
        paginationControls.appendChild(prevLi);

        for (let i = 1; i <= pageCount; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationControls.appendChild(pageLi);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === pageCount ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}"><i class="bi bi-chevron-right"></i></a>`;
        paginationControls.appendChild(nextLi);
    }

    function updateTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const itemsPerPage = parseInt(lengthSelect.value, 10);
        
        filteredRows = allRows.filter(row => {
            return row.textContent.toLowerCase().includes(searchTerm);
        });

        allRows.forEach(row => row.style.display = 'none');
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = itemsPerPage === -1 ? filteredRows.length : startIndex + itemsPerPage;
        const paginatedRows = filteredRows.slice(startIndex, endIndex);
        
        paginatedRows.forEach(row => row.style.display = '');

        paginationInfo.textContent = `Menampilkan ${paginatedRows.length} dari ${filteredRows.length} data (Total ${allRows.length} data).`;
        renderPagination();
    }

    searchInput.addEventListener('keyup', () => { currentPage = 1; updateTable(); });
    lengthSelect.addEventListener('change', () => { currentPage = 1; updateTable(); });
    paginationControls.addEventListener('click', (e) => {
        e.preventDefault();
        if (e.target.closest('a') && !e.target.closest('.page-item').classList.contains('disabled')) {
            currentPage = parseInt(e.target.closest('a').dataset.page);
            updateTable();
        }
    });

    updateTable();
});
</script>

<?php include '../../includes/footer.php'; ?>