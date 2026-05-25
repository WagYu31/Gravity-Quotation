<?php
include '../../includes/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
?>

<div class="gv-main-content" style="padding: 28px 36px;">

    <!-- Hero Banner -->
    <div class="dashboard-hero" style="margin-bottom: 28px;">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Tambah Customer Baru</h1>
                <p class="hero-subtitle">Isi form di bawah untuk menambahkan customer baru</p>
            </div>
            <a href="index.php" class="btn btn-hero-create">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="dashboard-table-card">
        <div style="padding: 32px;">
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="name" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Nama Customer <span style="color: #ef4444;">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                            style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="store_name" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Nama Toko / Perusahaan <span style="color: #94a3b8; font-weight: 500;">(Opsional)</span></label>
                        <input type="text" class="form-control" id="store_name" name="store_name"
                            style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Alamat Lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="3"
                        style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s; resize: vertical;"
                        onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="phone_number" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;"><i class="bi bi-whatsapp" style="color: #22c55e;"></i> No. Telepon</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number"
                            style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;"><i class="bi bi-envelope" style="color: #94a3b8;"></i> Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="ket" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">PIC / Keterangan <span style="color: #94a3b8; font-weight: 500;">(Opsional)</span></label>
                    <input type="text" class="form-control" id="ket" name="ket" placeholder="e.g., Bpk. Budi, Bagian Pembelian"
                        style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                        onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                </div>

                <!-- Submit Buttons -->
                <div style="margin-top: 24px; display: flex; gap: 10px;">
                    <button type="submit" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 50px; background: linear-gradient(135deg, #facc15, #eab308); color: #1e293b; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 3px 12px rgba(234,179,8,0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(234,179,8,0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 12px rgba(234,179,8,0.3)'">
                        <i class="bi bi-check-lg"></i> Simpan Customer
                    </button>
                    <a href="index.php" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; font-size: 13px; padding: 12px 24px; border-radius: 50px; background: #f9fafb; color: #64748b; border: 1.5px solid #d1d5db; text-decoration: none; transition: all 0.3s; display: inline-flex; align-items: center;"
                        onmouseover="this.style.background='#f1f5f9'"
                        onmouseout="this.style.background='#f9fafb'">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>