<?php
// Memanggil file-file penting
include '../../includes/db.php';
include '../../includes/header.php';

// Memastikan hanya user yang sudah login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// 1. Ambil ID dari URL dan validasi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID Penawaran tidak valid.");
}
$quotationId = (int)$_GET['id'];

// 2. Ambil data utama penawaran dari database
$stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();

if (!$quote) {
    die("Error: Penawaran tidak ditemukan.");
}

// 3. Ambil data item-item penawaran
$items_stmt = $conn->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
$items_stmt->bind_param("i", $quotationId);
$items_stmt->execute();
$quote_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Ambil data master untuk dropdown
$customers = $conn->query("SELECT id, name FROM customer WHERE deleted_at IS NULL ORDER BY name ASC");
$items_master = $conn->query("SELECT id, code, kategori, `desc`, price, satuan FROM barang WHERE deleted_at IS NULL ORDER BY kategori ASC");

// 5. Tentukan parent_id untuk riwayat. Jika belum ada, gunakan ID saat ini.
$parent_id = $quote['parent_quotation_id'] ?? $quote['id'];

?>

<div class="container-fluid mt-4">
    <form id="quotation-form" onsubmit="return false;">
        <div class="row">
            <div class="col-xl-9 col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Penawaran #<?php echo htmlspecialchars($quote['quotation_code'] ?? $quote['id']); ?></h4>
                        <a href="../../dashboard.php" class="btn btn-sm btn-outline-secondary">Kembali ke Dashboard</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer" class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($conn->query("SELECT name FROM customer WHERE id = ".$quote['customer_id'])->fetch_assoc()['name']); ?>" disabled>
                                <input type="hidden" id="customer" name="customer_id" value="<?php echo $quote['customer_id']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="issuer" class="form-label fw-bold">Diterbitkan oleh <span class="text-danger">*</span></label>
                                <select id="issuer" name="issuer" class="form-select" required>
                                    <option value="Loewix" <?php echo ($quote['issuer'] == 'Loewix') ? 'selected' : ''; ?>>Loewix (Tanpa PPN)</option>
                                    <option value="CV" <?php echo ($quote['issuer'] == 'CV') ? 'selected' : ''; ?>>CV (Dengan PPN 11%)</option>
                                </select>
                            </div>
                        </div>
                        
                        <input type="hidden" id="quotation_id" name="quotation_id" value="<?php echo $quote['id']; ?>">
                        <input type="hidden" id="parent_quotation_id" name="parent_quotation_id" value="<?php echo $parent_id; ?>">
                        <hr class="my-4">

                        <h5>Daftar Barang</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="quotation-items-table">
                                <colgroup><col style="width: 40px;"><col style="width: auto;"><col style="width: 60px;"><col style="width: 130px;"><col style="width: 160px;"><col style="width: 140px;"><col style="width: 46px;"></colgroup>
                                <thead class="table-light"><tr><th class="text-center">No</th><th>Nama Barang</th><th class="text-center">Qty</th><th class="text-center">Harga Satuan</th><th class="text-center">Diskon</th><th class="text-center">Total</th><th class="text-center">Aksi</th></tr></thead>
                                <tbody>
                                    <?php $rowNum = 0; foreach ($quote_items as $item): $rowNum++; ?>
                                    <tr data-item-id="<?php echo $item['barang_id']; ?>">
                                        <td class="text-center row-number"><?php echo $rowNum; ?></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm item-name" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                            <textarea class="form-control form-control-sm mt-1 item-desc" style="font-size:10px;" disabled><?php echo htmlspecialchars($item['item_description']); ?></textarea>
                                        </td>
                                        <td class="text-center"><input type="number" class="form-control form-control-sm quantity text-center" value="<?php echo $item['quantity']; ?>" min="1"></td>
                                        <td class="text-center"><input type="number" class="form-control form-control-sm price text-center" value="<?php echo $item['item_price']; ?>" min="0"></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control discount-value text-end" value="<?php echo $item['discount_value']; ?>" min="0">
                                                <select class="form-select discount-type" style="flex-grow:0; width:44px;">
                                                    <option value="AMOUNT" <?php echo ($item['discount_type'] == 'AMOUNT') ? 'selected' : ''; ?>>Rp</option>
                                                    <option value="PERCENT" <?php echo ($item['discount_type'] == 'PERCENT') ? 'selected' : ''; ?>>%</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="text-center row-total fw-bold"></td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">+ Tambah Barang</button>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-12">
                <div class="card sticky-top" id="summary-card" style="top: 80px;">
                    <div class="card-header summary-header">
                        <div class="summary-header-icon">📋</div>
                        <h5 class="mb-0">Ringkasan Penawaran</h5>
                    </div>
                    <div class="card-body summary-body">
                        <!-- Catatan -->
                        <div class="summary-section">
                            <label for="notes" class="summary-label">
                                <span class="label-icon">📝</span> Catatan Penawaran
                            </label>
                            <textarea id="notes" name="notes" class="form-control summary-textarea" rows="3" placeholder="Contoh: Harga berlaku selama 14 hari"><?php echo htmlspecialchars($quote['notes']); ?></textarea>
                        </div>

                        <!-- Summary Lines -->
                        <div class="summary-lines">
                            <div class="summary-row">
                                <span class="summary-label-text">Subtotal</span>
                                <span class="summary-value" id="summary-subtotal">Rp 0</span>
                            </div>

                            <div class="summary-row summary-discount-row">
                                <span class="summary-label-text">Diskon Tambahan</span>
                                <div class="summary-discount-input">
                                    <select class="form-select" id="overall-discount-type">
                                        <option value="AMOUNT" <?php echo ($quote['overall_discount_type'] == 'AMOUNT') ? 'selected' : ''; ?>>Rp</option>
                                        <option value="PERCENT" <?php echo ($quote['overall_discount_type'] == 'PERCENT') ? 'selected' : ''; ?>>%</option>
                                    </select>
                                    <input type="number" id="overall-discount-value" class="form-control" value="<?php echo $quote['overall_discount_value']; ?>" min="0">
                                </div>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label-text">PPN (11%)</span>
                                <span class="summary-value" id="summary-ppn">Rp 0</span>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="summary-grandtotal-box">
                            <span class="grandtotal-label">Grand Total</span>
                            <span class="grandtotal-value" id="summary-grandtotal">Rp 0</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="summary-actions">
                            <button type="button" id="save-draft-btn" class="btn summary-btn-draft">
                                <span class="btn-icon">💾</span> Simpan sebagai DRAFT
                            </button>
                            <button type="button" id="save-final-btn" class="btn summary-btn-final">
                                <span class="btn-icon">✅</span> Simpan & Finalisasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered draggable-modal" style="max-width: 520px;">
        <div class="modal-content premium-modal">
            <div class="modal-header premium-modal-header" id="modal-drag-handle">
                <div class="modal-header-left">
                    <span class="modal-drag-icon" title="Geser modal">⠿</span>
                    <div>
                        <h5 class="modal-title">Pilih Barang</h5>
                        <p class="modal-subtitle">Cari dan pilih barang dari daftar di bawah</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body premium-modal-body">
                <label class="modal-search-label">
                    <span class="search-icon">🔍</span> Cari Barang
                </label>
                <select id="item-selector" class="form-select">
                    <option value="">-- Ketik untuk cari barang --</option>
                    <?php mysqli_data_seek($items_master, 0); ?>
                    <?php while ($row = $items_master->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" 
                        data-price="<?php echo $row['price']; ?>" 
                        data-name="<?php echo htmlspecialchars($row['kategori']); ?>" 
                        data-desc="<?php echo htmlspecialchars($row['desc']); ?>" 
                        data-unit="<?php echo htmlspecialchars($row['satuan']); ?>">
                        <?php echo htmlspecialchars($row['kategori'] . " (" . $row['code'] . ")"); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="modal-footer premium-modal-footer">
                <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">
                    ✕ Batal
                </button>
                <button type="button" class="btn modal-btn-add" id="add-item-btn">
                    ＋ Tambahkan ke Penawaran
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>