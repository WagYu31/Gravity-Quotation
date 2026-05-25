<?php
include '../../includes/db.php';
include '../../includes/header.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../../login.php'); exit(); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("ID tidak valid.");
$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM barang WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $id);
$stmt->execute();
$barang = $stmt->get_result()->fetch_assoc();
if (!$barang) die("Barang tidak ditemukan.");
?>

<div class="gv-main-content" style="padding: 28px 36px;">

    <!-- Hero Banner -->
    <div class="dashboard-hero" style="margin-bottom: 28px;">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Edit Data Barang</h1>
                <p class="hero-subtitle">Perbarui informasi untuk: <strong><?php echo htmlspecialchars($barang['kategori']); ?></strong></p>
            </div>
            <a href="index.php" class="btn btn-hero-create">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="dashboard-table-card">
        <div style="padding: 32px;">
            <form action="actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $barang['id']; ?>">
                
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label for="kategori" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Kategori / Nama <span style="color: #ef4444;">*</span></label>
                                <input type="text" class="form-control" id="kategori" name="kategori" value="<?php echo htmlspecialchars($barang['kategori']); ?>" required
                                    style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                    onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="code" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Kode Barang</label>
                                <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($barang['code']); ?>"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                    onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="desc" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Deskripsi</label>
                            <textarea class="form-control" id="desc" name="desc" rows="3"
                                style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s; resize: vertical;"
                                onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"><?php echo htmlspecialchars($barang['desc']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="price" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Harga <span style="color: #ef4444;">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $barang['price']; ?>" required
                                    style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                    onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="satuan" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Satuan <span style="color: #ef4444;">*</span></label>
                                <input type="text" class="form-control" id="satuan" name="satuan" value="<?php echo htmlspecialchars($barang['satuan']); ?>" required
                                    style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                    onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Image -->
                    <div class="col-md-4">
                        <div style="background: #f9fafb; border-radius: 14px; border: 1.5px dashed #d1d5db; padding: 24px; text-align: center;">
                            <label for="image" style="cursor: pointer; display: block;">
                                <div style="margin-bottom: 16px;">
                                    <?php 
                                    $image_path = '../../assets/uploads/barang/' . $barang['image'];
                                    if (!empty($barang['image']) && file_exists($image_path)): ?>
                                        <img id="image-preview" src="<?php echo $image_path; ?>" alt="Preview" 
                                            style="max-height: 160px; max-width: 100%; object-fit: contain; border-radius: 10px;">
                                    <?php else: ?>
                                        <img id="image-preview" src="../../assets/img/placeholder.png" alt="Preview" 
                                            style="max-height: 160px; max-width: 100%; object-fit: contain; border-radius: 10px;">
                                    <?php endif; ?>
                                </div>
                                <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; color: #94a3b8; margin-bottom: 8px;">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 20px; color: #cbd5e1; display: block; margin-bottom: 6px;"></i>
                                    Klik untuk ganti gambar
                                </div>
                                <input class="form-control" type="file" id="image" name="image" onchange="previewImage(event)" style="display: none;">
                                <span style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 10px; color: #cbd5e1;">JPG, PNG, WEBP — Max 2MB</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Link Section -->
                <div style="border-top: 1.5px solid #f1f5f9; margin-top: 8px; padding-top: 24px;">
                    <h5 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; font-weight: 800; color: #1e293b; margin-bottom: 16px;">
                        <i class="bi bi-link-45deg" style="color: #94a3b8;"></i> Link Tambahan
                    </h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name_link_1" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Nama Link 1</label>
                            <input type="text" class="form-control" id="name_link_1" name="name_link_1" value="<?php echo htmlspecialchars($barang['name_link_1']); ?>"
                                style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="link_1" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">URL Link 1</label>
                            <input type="url" class="form-control" id="link_1" name="link_1" value="<?php echo htmlspecialchars($barang['link_1']); ?>"
                                style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name_link_2" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">Nama Link 2</label>
                            <input type="text" class="form-control" id="name_link_2" name="name_link_2" value="<?php echo htmlspecialchars($barang['name_link_2']); ?>"
                                style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="link_2" class="form-label" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b;">URL Link 2</label>
                            <input type="url" class="form-control" id="link_2" name="link_2" value="<?php echo htmlspecialchars($barang['link_2']); ?>"
                                style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; border-radius: 10px !important; border: 1.5px solid #e2e8f0 !important; padding: 12px 16px !important; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#facc15'; this.style.boxShadow='0 0 0 3px rgba(250,204,21,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div style="margin-top: 24px; display: flex; gap: 10px;">
                    <button type="submit" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 50px; background: linear-gradient(135deg, #facc15, #eab308); color: #1e293b; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 3px 12px rgba(234,179,8,0.3);"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(234,179,8,0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 12px rgba(234,179,8,0.3)'">
                        <i class="bi bi-check-lg"></i> Update Barang
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

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('image-preview');
        output.src = reader.result;
    };
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>