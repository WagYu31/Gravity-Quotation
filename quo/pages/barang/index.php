<?php
include '../../includes/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
$result = $conn->query("SELECT * FROM barang WHERE deleted_at IS NULL ORDER BY kategori ASC");
$total_barang = $result->num_rows;
?>

<div class="gv-main-content" style="padding: 28px 36px;">

    <!-- Hero Banner -->
    <div class="dashboard-hero" style="margin-bottom: 28px;">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Manajemen Data Barang</h1>
                <p class="hero-subtitle">Total <?php echo $total_barang; ?> barang terdaftar dalam sistem</p>
            </div>
            <a href="create.php" class="btn btn-hero-create">
                <i class="bi bi-plus-lg"></i> Tambah Barang
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
                        <input type="text" id="search-input" class="form-control" placeholder="Cari barang..."
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
                        <th>Gambar</th>
                        <th>Kategori / Nama</th>
                        <th>Kode</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="barang-table-body">
                    <?php if ($result->num_rows > 0): $no = 1; while($row = $result->fetch_assoc()): ?>
                        <tr class="main-row">
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; font-weight: 700; color: #94a3b8; padding: 14px 18px;"><?php echo $no++; ?></td>
                            <td style="padding: 14px 18px;">
                                <?php 
                                $image_path = '../../assets/uploads/barang/' . $row['image'];
                                if (!empty($row['image']) && file_exists($image_path)): ?>
                                    <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['kategori']); ?>" 
                                         style="width: 44px; height: 44px; object-fit: cover; border-radius: 10px; border: 1.5px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #f9fafb; border: 1.5px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-card-image" style="color: #cbd5e1; font-size: 16px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 600; color: #1e293b; padding: 14px 18px;"><?php echo htmlspecialchars($row['kategori']); ?></td>
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #64748b; padding: 14px 18px;"><?php echo htmlspecialchars($row['code']); ?></td>
                            <td style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 800; color: #1e293b; padding: 14px 18px; white-space: nowrap; font-variant-numeric: tabular-nums;">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td style="padding: 14px 18px;">
                                <span class="status-pill status-default"><?php echo htmlspecialchars($row['satuan']); ?></span>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div class="action-group">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn action-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="actions.php?action=delete&id=<?php echo $row['id']; ?>" class="action-btn action-delete" title="Hapus" onclick="return confirm('Anda yakin ingin menghapus barang ini?')"><i class="bi bi-trash-fill"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding: 40px; font-family: 'Plus Jakarta Sans', sans-serif;">Belum ada data barang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9;">
            <span id="pagination-info" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #94a3b8;"></span>
        </div>
    </div>
</div>

<script>
// JavaScript untuk Search, Filter, dan Pagination Sederhana
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const lengthSelect = document.getElementById('length-select');
    const tableBody = document.getElementById('barang-table-body');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const paginationInfo = document.getElementById('pagination-info');

    function updateTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const displayLength = parseInt(lengthSelect.value, 10);
        
        let visibleRows = 0;

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const matchesSearch = textContent.includes(searchTerm);
            
            if (matchesSearch) {
                if (displayLength === -1 || visibleRows < displayLength) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            } else {
                row.style.display = 'none';
            }
        });

        paginationInfo.textContent = `Menampilkan ${visibleRows} dari ${rows.length} total data.`;
    }

    searchInput.addEventListener('keyup', updateTable);
    lengthSelect.addEventListener('change', updateTable);

    // Panggil sekali saat halaman dimuat
    updateTable();
});
</script>

<?php include '../../includes/footer.php'; ?>