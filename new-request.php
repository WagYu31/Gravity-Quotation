<?php
include "conn.php";

$quoID = isset($_GET['id']) ? $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CSS Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                <?php include 'navbar-header.php'; ?>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="page-header">
                        <h3 class="fw-bold mb-3">Data</h3>
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
                                <a href="#">Data</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Quotations Request</a>
                            </li>
                        </ul>
                    </div>

                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quotations</h4>
                                </div>
                                <div class="card-body">
                    
                                    <form action="new_proses_req.php" method="POST">
                                        <div class="mb-3">
                                            <label for="customer" class="form-label">Pilih Customer</label>
                                            <select class="form-select select2" id="customer" name="customer" required>
                                                <option value="">Cari customer...</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="service_type" class="form-label">Jenis Layanan</label>
                                            <select class="form-select" id="service_type" name="service_type" required>
                                                <option value="Survey">Survey</option>
                                                <option value="Pasang Baru">Pasang Baru</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date_time" class="form-label">Tanggal & Jam</label>
                                            <input type="datetime-local" class="form-control" id="date_time" name="date_time">
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Keterangan</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>


            <?php 
            
                    include "floating-menu.php";
                    include 'footer.php'; ?>
        </div>

    </div>

    <?php include 'core-scripts.php'; ?>
    <script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Cari customer...", // Placeholder text
            allowClear: true, // Memungkinkan menghapus pilihan
            ajax: {
                url: 'search_cust_request.php', // File PHP untuk mencari customer
                type: 'POST',
                dataType: 'json',
                delay: 250, // Delay saat mengetik (ms)
                data: function(params) {
                    return {
                        search: params.term // Kata kunci pencarian
                    };
                },
                processResults: function(data) {
                    return {
                        results: data // Hasil pencarian
                    };
                },
                cache: true // Cache hasil pencarian
            },
            minimumInputLength: 1 // Minimal karakter untuk memulai pencarian
        });
    });
</script>
</body>

</html>