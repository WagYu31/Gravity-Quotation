<?php
include "conn.php";

$quoID = isset($_GET['id']) ? $_GET['id'] : null;
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

                    <?php
                    // Query untuk mengambil data
                    $query = "
                        SELECT 
                            c.name AS customer_name,
                            c.address,
                            c.phone_number,
                            q.*, 
                            u.name AS user_name
                        FROM quo q
                        JOIN customer c ON q.customer_id = c.id
                        JOIN users u ON q.users_id = u.id
                        WHERE q.deleted_at IS NULL AND q.id = '$quoID'
                    ";
                    
                    $result = mysqli_query($conn, $query);
                    $data = mysqli_fetch_assoc($result);
                    
                    // Format nomor telepon
                    $phone_number = preg_replace('/\D/', '', $data['phone_number']); // Hapus semua karakter selain angka
                    if (strpos($phone_number, '08') !== 0) {
                        if (strpos($phone_number, '8') === 0) {
                            $phone_number = '0' . $phone_number;
                        } elseif (strpos($phone_number, '62') === 0) {
                            $phone_number = '0' . substr($phone_number, 2);
                        } elseif (strpos($phone_number, '+62') === 0) {
                            $phone_number = '0' . substr($phone_number, 3);
                        }
                    }
                    
                    $namauser = $data['user_name'];
                    $custname = $data['customer_name'];
                    $custaddress = $data['address'];
                    ?>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quotations</h4>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nama User:</strong> <?php echo htmlspecialchars($data['user_name']); ?></p>
                                    <p><strong>Nama Customer:</strong> <?php echo htmlspecialchars($data['customer_name']); ?></p>
                                    <p><strong>Alamat Customer:</strong> <?php echo htmlspecialchars($data['address']); ?></p>
                                    <p><strong>No Telepon Customer:</strong> <?php echo htmlspecialchars($phone_number); ?></p>
                    
                                    <form action="proses_req.php" method="POST">
                                        <div class="mb-3">
                                            <label for="service_type" class="form-label">Jenis Layanan</label>
                                            <select class="form-select" id="service_type" name="service_type" required>
                                                <option value="survey">Survey</option>
                                                <option value="pasang baru">Pasang Baru</option>
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
                                        <input type="hidden" name="namauser" value="<?php echo $namauser;?>">
                                        <input type="hidden" name="custname" value="<?php echo $custname;?>">
                                        <input type="hidden" name="custaddress" value="<?php echo $custaddress;?>">
                                        <input type="hidden" name="phone" value="<?php echo $phone_number;?>">
                                        <input type="hidden" name="quoid" value="<?php echo $quoID;?>">
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
</body>

</html>