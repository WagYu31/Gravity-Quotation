<?php
// Menggunakan file header dan footer yang Anda tentukan
include '../../includes/db.php';
include '../../includes/header-create.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="container-fluid mt-4 px-4" id="create-quotation-page">
    <!-- Page Header -->
    <div class="gv-page-header">
        <div class="gv-page-header-left">
            <h1 class="gv-page-title"><i class="bi bi-file-earmark-plus"></i> Buat Penawaran Baru</h1>
            <p class="gv-page-subtitle">Isi detail penawaran dan tambahkan barang untuk customer Anda</p>
        </div>
        <a href="../../dashboard.php" class="gv-btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form id="quotation-form" onsubmit="return false;">
        <div class="row g-4">
            <div class="col-xl-9 col-lg-12">
                <!-- Customer & Issuer Section -->
                <div class="gv-form-card">
                    <div class="gv-form-card-header">
                        <i class="bi bi-person-vcard"></i> Informasi Penawaran
                    </div>
                    <div class="gv-form-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer" class="gv-form-label">Pilih Customer <span class="text-danger">*</span></label>
                                <select id="customer" name="customer_id" class="form-select" required></select>
                            </div>
                            <div class="col-md-6">
                                <label for="issuer" class="gv-form-label">Diterbitkan oleh <span class="text-danger">*</span></label>
                                <select id="issuer" name="issuer" class="form-select" required>
                                    <option value="Loewix">Loewix (Tanpa PPN)</option>
                                    <option value="CV">CV (Dengan PPN 11%)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="quotation_id" name="quotation_id" value="">

                <!-- Items Section -->
                <div class="gv-form-card">
                    <div class="gv-form-card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-box-seam"></i> Daftar Barang</span>
                        <button type="button" class="gv-btn-add-item" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle"></i> Tambah Barang
                        </button>
                    </div>
                    <div class="gv-form-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered gv-items-table" id="quotation-items-table">
                                <colgroup><col style="width: 40px;"><col style="width: auto;"><col style="width: 60px;"><col style="width: 130px;"><col style="width: 160px;"><col style="width: 140px;"><col style="width: 46px;"></colgroup>
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-center">Diskon</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-12">
                <div class="gv-summary-card sticky-top" id="summary-card" style="top: 80px;">
                    <div class="gv-summary-header">
                        <i class="bi bi-receipt-cutoff"></i> Ringkasan Penawaran
                    </div>
                    <div class="gv-summary-body">
                        <div class="mb-3">
                            <label for="notes" class="gv-form-label">Catatan Penawaran</label>
                            <textarea id="notes" name="notes" class="form-control gv-textarea" rows="3" placeholder="Contoh: Harga berlaku selama 14 hari"></textarea>
                        </div>

                        <div class="gv-summary-divider"></div>

                        <div class="gv-summary-row">
                            <span class="gv-summary-label">Subtotal</span>
                            <span class="gv-summary-value" id="summary-subtotal">Rp 0</span>
                        </div>

                        <div class="gv-summary-row">
                            <span class="gv-summary-label">Diskon Tambahan</span>
                        </div>
                        <div class="gv-discount-input">
                            <select class="form-select gv-discount-select" id="overall-discount-type">
                                <option value="AMOUNT">Rp</option>
                                <option value="PERCENT">%</option>
                            </select>
                            <input type="number" id="overall-discount-value" class="form-control gv-discount-value" value="0" min="0">
                        </div>

                        <div class="gv-summary-row mt-3">
                            <span class="gv-summary-label">PPN (11%)</span>
                            <span class="gv-summary-value" id="summary-ppn">Rp 0</span>
                        </div>

                        <div class="gv-summary-divider"></div>

                        <div class="gv-grand-total-row">
                            <span class="gv-grand-total-label">Grand Total</span>
                            <span class="gv-grand-total-value" id="summary-grandtotal">Rp 0</span>
                        </div>

                        <div class="gv-summary-divider"></div>

                        <div class="gv-action-buttons">
                            <button type="button" id="save-draft-btn" class="gv-btn-draft">
                                <i class="bi bi-file-earmark-arrow-down"></i> Simpan sebagai DRAFT
                            </button>
                            <button type="button" id="save-final-btn" class="gv-btn-finalize">
                                <i class="bi bi-check-circle"></i> Simpan & Finalisasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: #fafbfc; border-bottom: 1px solid rgba(0,0,0,0.06); padding: 16px 24px;">
        <h5 class="modal-title" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px;"><i class="bi bi-search" style="color: var(--gv-primary);"></i> Pilih Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding: 24px;">
        <select id="item-selector" class="form-select" style="width: 100%;"></select>
      </div>
      <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.06); padding: 16px 24px; gap: 8px;">
        <button type="button" class="gv-btn-draft" data-bs-dismiss="modal" style="width: auto; padding: 10px 20px;">Batal</button>
        <button type="button" class="gv-btn-finalize" id="add-item-btn" style="width: auto; padding: 10px 20px;"><i class="bi bi-plus-circle"></i> Tambahkan ke Penawaran</button>
      </div>
    </div>
  </div>
</div>

<?php 
// Menggunakan file footer yang Anda tentukan
include '../../includes/footer-create.php'; 
?>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 untuk Customer
    $('#customer').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik untuk mencari customer...',
        minimumInputLength: 2,
        ajax: {
            url: '/quo/pages/quotation/ajax_search_customer.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { term: params.term }; },
            processResults: function (data) { return { results: data.results }; },
            cache: true
        }
    });

    // Inisialisasi Select2 untuk Barang di dalam Modal
    $('#item-selector').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik kode, nama, atau deskripsi barang...',
        minimumInputLength: 2,
        dropdownParent: $('#addItemModal'), 
        ajax: {
            url: '/quo/pages/quotation/ajax_search_item.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { term: params.term }; },
            processResults: function (data) {
                return {
                    results: $.map(data.results, function (item) {
                        return {
                            text: item.text, id: item.id, price: item.price,
                            name: item.name, desc: item.desc, unit: item.unit
                        }
                    })
                };
            },
            cache: true
        }
    });
    
    // --- INI ADALAH PERBAIKAN FINAL ---
    $('#item-selector').on('select2:select', function (e) {
        var data = e.params.data;
        // Cari elemen <option> yang sedang dipilih di dalam <select>
        var selectedOption = $(this).find('option:selected');
        
        // Tempelkan semua data (harga, nama, dll) ke atribut data-*
        // dari elemen <option> tersebut.
        selectedOption.data('price', data.price);
        selectedOption.data('name', data.name);
        selectedOption.data('desc', data.desc);
        selectedOption.data('unit', data.unit);
    });
});
</script>