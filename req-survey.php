<?php
include "conn.php";
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
                                <a href="#">Survey Request</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Survey</h4>
                                </div>
                                <div class="card-body">
                                    <form action="proses_req_survey.php" method="POST">
                                        <p><input type="hidden" value="<?php echo $user_name;?>" name="namauser"></p>
                                        <?php
                                        $query = "SELECT * FROM customer WHERE deleted_at IS NULL";
                                        $result = mysqli_query($conn, $query);
                                        
                                        $customers = array();
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            // Format nomor telepon
                                            $phone_number = preg_replace('/\D/', '', $row['phone_number']);
                                            if (strpos($phone_number, '08') !== 0) {
                                                if (strpos($phone_number, '8') === 0) {
                                                    $phone_number = '0' . $phone_number;
                                                } elseif (strpos($phone_number, '62') === 0) {
                                                    $phone_number = '0' . substr($phone_number, 2);
                                                } elseif (strpos($phone_number, '+62') === 0) {
                                                    $phone_number = '0' . substr($phone_number, 3);
                                                }
                                            }
                                            
                                            $customers[] = array(
                                                'id' => $row['id'],
                                                'name' => $row['name'],
                                                'phone' => $phone_number,
                                                'address' => $row['address']
                                            );
                                        }
                                        $customers_json = json_encode($customers);
                                        ?>
                                        
                                        <!-- Bagian select customer (perubahan pada option value) -->
                                        <div class="form-group">
                                            <label for="customer_select"><strong>Nama Customer:</strong></label>
                                            <select name="custname" id="customer_select" class="form-control" onchange="updateCustomerDetails()">
                                                <option value="">-- Pilih Customer --</option>
                                                <?php foreach ($customers as $customer): ?>
                                                    <option value="<?php echo htmlspecialchars($customer['id']); ?>" 
                                                            data-phone="<?php echo htmlspecialchars($customer['phone']); ?>"
                                                            data-address="<?php echo htmlspecialchars($customer['address']); ?>">
                                                        <?php echo htmlspecialchars($customer['name'] . ' - ' . $customer['phone']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Input fields untuk alamat dan telepon -->
                                            <input type="hidden" id="customer_address" name="custaddress" class="form-control" readonly value="-">
                                        
                                            <input type="hidden" id="customer_phone" name="phone" class="form-control" readonly value="-">
                                            
                                        <script>
                                        function updateCustomerDetails() {
                                            var select = document.getElementById('customer_select');
                                            var selectedOption = select.options[select.selectedIndex];
                                            
                                            if (select.value === "") {
                                                document.getElementById('customer_address').value = '-';
                                                document.getElementById('customer_phone').value = '-';
                                            } else {
                                                document.getElementById('customer_address').value = selectedOption.getAttribute('data-address');
                                                document.getElementById('customer_phone').value = selectedOption.getAttribute('data-phone');
                                            }
                                        }
                                        
                                        document.addEventListener('DOMContentLoaded', function() {
                                            updateCustomerDetails();
                                        });
                                        </script>
                                        
                                        <div class="form-group">
                                            <label for="service_type" class="form-label">Jenis Layanan</label>
                                            <input type="text" class="form-control" value="Survey" name="service_type" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="date_time" class="form-label">Tanggal & Jam</label>
                                            <input type="datetime-local" class="form-control" id="date_time" name="date_time">
                                        </div>
                                        <div class="form-group">
                                            <label for="description" class="form-label">Keterangan</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary ms-3">Submit</button>
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