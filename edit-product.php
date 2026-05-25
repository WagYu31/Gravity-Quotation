<?php
include 'conn.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <div class="wrapper">

        <?php include 'sidebar.php'; ?>

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <?php include 'logo-header.php'; ?>
                </div>
                <!-- Navbar Header -->
                <?php include 'navbar-header.php'; ?>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Forms</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home">
                                <a href="#">
                                    <i class="icon-home"></i>
                                </a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Forms</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Tambah Product</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Ambil ID produk dari GET
                            $id = $_GET['id'] ?? null;

                            // Ambil data produk berdasarkan ID
                            if ($id) {
                                $query = $conn->prepare("SELECT * FROM barang WHERE id = ?");
                                $query->bind_param("i", $id);
                                $query->execute();
                                $result = $query->get_result();
                                $product = $result->fetch_assoc();
                                if (!$product) {
                                    die("Produk tidak ditemukan.");
                                }
                            } else {
                                die("ID produk tidak ditemukan.");
                            }
                            ?>
                            <form action="proses_update_product.php" method="POST" enctype="multipart/form-data">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Form Edit Produk</div>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="id" value="<?= $product['id']; ?>" />
                                        <div class="row">
                                            <!-- Kolom Kiri -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="code">Kode Barang</label>
                                                    <input type="text" class="form-control" id="code" name="code" placeholder="Kode Barang" value="<?= $product['code']; ?>" required />
                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Masukan kode barang sesuai dengan Accurate</i></small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="code">Nama Barang</label>
                                                    <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Nama Barang" value="<?= $product['kategori']; ?>" required />
                                                </div>

                                                <div class="form-group">
                                                    <label for="code">Satuan Barang</label>
                                                    <input type="text" class="form-control" id="satuan" name="satuan" placeholder="Satuan Produk" value="<?= $product['satuan']; ?>" required />
                                                </div>

                                                <div class="form-group">
                                                    <label for="price">Harga</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="number" class="form-control" name="price" value="<?= $product['price']; ?>" required />
                                                        <span class="input-group-text">,-</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Kolom Tengah -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="image">Gambar Produk</label>
                                                    <input type="file" class="form-control" id="image" name="image" onchange="previewImage(event)" />
                                                    <img id="preview" src="uploads/products/<?= $product['image']; ?>" alt="Preview Gambar" style="margin-top: 10px; height: 80px; width: auto;" />
                                                </div>

                                                <script>
                                                    // Fungsi untuk mempreview gambar yang dipilih
                                                    function previewImage(event) {
                                                        const preview = document.getElementById('preview'); // Elemen img untuk preview
                                                        const file = event.target.files[0]; // File yang dipilih

                                                        // Jika ada file yang dipilih
                                                        if (file) {
                                                            const reader = new FileReader();

                                                            reader.onload = function(e) {
                                                                preview.src = e.target.result; // Mengubah src elemen img
                                                            };

                                                            // Membaca file yang dipilih sebagai URL data
                                                            reader.readAsDataURL(file);
                                                        } else {
                                                            // Reset preview jika tidak ada file
                                                            preview.src = "uploads/products/<?= $product['image']; ?>";
                                                        }
                                                    }
                                                </script>

                                                <div class="form-group">
                                                    <label for="desc">Spesifikasi</label>
                                                    <textarea class="form-control" id="desc" name="desc" rows="5" required><?= $product['desc']; ?></textarea>
                                                </div>
                                            </div>

                                            <!-- Kolom Kanan -->
                                            <div class="col-md-6 col-lg-4">
                                                <label class="mb-3"><b>Form Link Produk</b></label>
                                                <div class="form-group">
                                                    <label for="link_1">Link 1</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">https://</span>
                                                        <input type="text" class="form-control" name="link_1" value="<?= $product['link_1']; ?>" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name_link_1">Nama Link 1</label>
                                                    <input type="text" class="form-control" name="name_link_1" value="<?= $product['name_link_1']; ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="link_2">Link 2</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">https://</span>
                                                        <input type="text" class="form-control" name="link_2" value="<?= $product['link_2']; ?>" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name_link_2">Nama Link 2</label>
                                                    <input type="text" class="form-control" name="name_link_2" value="<?= $product['name_link_2']; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action">
                                        <button type="submit" class="btn btn-success">Update</button>
                                        <a href="products.php" class="btn btn-danger">Batal</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>


            <?php include 'footer.php'; ?>
        </div>

        <!-- Custom template | don't include it in your project! -->

        <?php
            // include 'custom-temp.php'
        ; ?>
    </div>

    <?php include 'core-scripts.php'; ?>
</body>

</html>