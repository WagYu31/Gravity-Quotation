<?php include 'conn.php'; ?>

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
                            <form action="proses_insert.php" method="POST" enctype="multipart/form-data">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Form Tambah Product</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="kode">Kode Barang</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="code"
                                                        name="code"
                                                        placeholder="Kode Barang"
                                                        required />
                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Masukan kode barang sesuai dengan Accurate</i></small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="kode">Nama Barang</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="kategori"
                                                        name="kategori"
                                                        placeholder="Nama Barang"
                                                        required />
                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Masukan Nama Barang contoh : IP Camera Indoor 4MP<br>LX-2230IP</i></small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="kode">Satuan</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="satuan"
                                                        name="satuan"
                                                        placeholder="Satuan Barang"
                                                        required />
                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Unit / Pcs / Node / Lainnya</i></small>
                                                </div>

                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="comment">Harga</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text">Rp</span>
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            name="price"
                                                            aria-label="Amount (to the nearest dollar)"
                                                            />
                                                        <span class="input-group-text">,-</span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="exampleFormControlFile1">Gambar Produk</label>
                                                    <input
                                                        type="file"
                                                        class="form-control form-control-file"
                                                        id="exampleFormControlFile1"
                                                        name="exampleFormControlFile1"
                                                        onchange="previewImage(event)"
                                                         />
                                                    <img
                                                        id="preview"
                                                        src=""
                                                        alt="Preview Gambar"
                                                        style="display: none; margin-top: 10px; height: 80px; width: auto;" />
                                                </div>

                                                <script>
                                                    function previewImage(event) {
                                                        const fileInput = event.target;
                                                        const preview = document.getElementById('preview');
                                                        const file = fileInput.files[0];

                                                        if (file) {
                                                            const reader = new FileReader();
                                                            reader.onload = function(e) {
                                                                preview.src = e.target.result;
                                                                preview.style.display = 'block';
                                                            };
                                                            reader.readAsDataURL(file);
                                                        } else {
                                                            preview.style.display = 'none';
                                                            preview.src = '';
                                                        }
                                                    }
                                                </script>

                                                <div class="form-group">
                                                    <label for="comment">Spesifikasi</label>
                                                    <textarea class="form-control" id="comment" name="comment" rows="5" ></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-4 mt-md-0 mt-4">
                                                <label class="mb-2"><b>Form Link Product</b></label>
                                                <div class="form-group">
                                                    <label for="basic-url">Link 1</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text" id="basic-addon3">https://</span>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="basic-url"
                                                            name="link_1"
                                                            aria-describedby="basic-addon3" />
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="code"
                                                        name="name_link_1"
                                                        placeholder="Nama Link" />
                                                    <small id="Kode" class="form-text text-muted">Contoh : Video Hasil Rekaman</small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="basic-url">Link 2</label>
                                                    <div class="input-group mb-0">
                                                        <span class="input-group-text" id="basic-addon3">https://</span>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="basic-url"
                                                            name="link_2"
                                                            aria-describedby="basic-addon3" />
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        id="code"
                                                        name="name_link_2"
                                                        placeholder="Nama Link" />
                                                    <small id="Kode" class="form-text text-muted">Contoh : Video Live Streaming</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action">
                                        <button type="submit" class="btn btn-success">Submit</button>
                                        <button type="reset" class="btn btn-danger">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>


            <?php 
                    include "floating-menu.php";
                    include 'footer.php'; ?>
        </div>

        <!-- Custom template | don't include it in your project! -->

        <?php
            // include 'custom-temp.php'
        ; ?>
    </div>

    <?php include 'core-scripts.php'; ?>
</body>

</html>