<?php
include 'conn.php';
$jadwal_host = 'localhost';
$jadwal_user = 'u836263092_rootJadwal';
$jadwal_password = 'Eddie@1819';
$jadwal_database = 'u836263092_jadwal';

$jadwal_conn = mysqli_connect($jadwal_host, $jadwal_user, $jadwal_password, $jadwal_database);
if (!$jadwal_conn) {
    die("Koneksi ke database jadwal gagal: " . mysqli_connect_error());
}

if ($user_role == 'superadmin') {
    $query = "SELECT k.id, k.kegiatan, k.jadwal, k.request, k.kode,
                     c.nama as customer_name, c.telp as customer_phone
              FROM kegiatan k
              JOIN customer c ON k.customer_id = c.id
              WHERE k.deleted_at IS NULL 
                AND c.deleted_at IS NULL 
                AND k.kegiatan != 'service' 
                AND k.kode LIKE 'qqq%'
              ORDER BY k.jadwal DESC";
} elseif ($user_role == 'admin') {
    $query = "SELECT k.id, k.kegiatan, k.jadwal, k.request, k.kode,
                     c.nama as customer_name, c.telp as customer_phone
              FROM kegiatan k
              JOIN customer c ON k.customer_id = c.id
              WHERE k.deleted_at IS NULL 
                AND c.deleted_at IS NULL 
                AND k.kegiatan != 'service' 
                AND k.kode LIKE 'qqq%'
                AND k.request = '$user_name'
              ORDER BY k.jadwal DESC";
}

$result = mysqli_query($jadwal_conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include 'head.php'; ?>

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
                                <a href="#">Request</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Survey atau Pasang Baru</h5>
                                    <a href="req-survey.php" class="btn btn-primary btn-sm">Request Baru</a>
                                </div>
                                
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="multi-filter-select" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Nomor Telepon</th>
                                                    <th>Kegiatan</th>
                                                    <th>Jadwal</th>
                                                    <th>Request</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $phone = $row['customer_phone'];
                                                    $phone_number = preg_replace('/\D/', '', $phone); // Hapus semua karakter selain angka
                                                    if (strpos($phone_number, '08') !== 0) {
                                                        if (strpos($phone_number, '8') === 0) {
                                                            $phone_number = '0' . $phone_number;
                                                        } elseif (strpos($phone_number, '62') === 0) {
                                                            $phone_number = '0' . substr($phone_number, 2);
                                                        } elseif (strpos($phone_number, '+62') === 0) {
                                                            $phone_number = '0' . substr($phone_number, 3);
                                                        }
                                                    }
                                                    
                                                    $jadwal = $row['jadwal'];
                                                    $formatted_jadwal = $jadwal ? date('d M Y H:i', strtotime($jadwal)) : '-';
                                                    
                                                    echo "
                                                    <tr>
                                                        <td style='font-size:13px;'>{$row['customer_name']}</td>
                                                        <td style='font-size:13px;'>{$phone_number}</td>
                                                        <td style='font-size:13px; text-transform:capitalize;'>{$row['kegiatan']}</td>
                                                        <td style='font-size:13px;'>{$formatted_jadwal}</td>
                                                        <td style='font-size:13px;'>{$row['request']}</td>
                                                        <td style='font-size:13px;' class='d-flex align-items-center'>
                                                            <a class='btn btn-primary btn-sm me-2' href='view-request.php?id={$row['kode']}'>
                                                                <i class='fas fa-eye'></i>
                                                            </a>
                                                            <a class='btn btn-sm btn-secondary me-2' href='proses_pasang.php?" . http_build_query($row) . "'>
                                                                <i class='fas fa-arrow-up'></i>
                                                            </a>
                                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>
                                                                <i class='fas fa-trash-alt'></i>
                                                            </button>
                                                        </td>
                                                    </tr>";
                                                }
                                                
                                                if (mysqli_num_rows($result) == 0) {
                                                    echo "<tr><td colspan='6' class='text-center'>Tidak ada data ditemukan</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <?php
                                    mysqli_close($jadwal_conn);
                                    ?>
                                    
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
            // Handle delete button
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            window.location.href = 'delete_kegiatan.php?id=' + id;
        }
    });
    </script>
</body>

</html>