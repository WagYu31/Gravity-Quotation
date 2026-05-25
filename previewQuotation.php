<?php
include "conn.php";

// Ambil data quonum dari URL
$quonum = $_GET['quonum'] ?? null;  // Gunakan operator null coalescing untuk set nilai default
$pict = $_GET['pict'] ?? null;
$link = $_GET['link'] ?? null;
$quoId = isset($_GET['id']) ? $_GET['id'] : null;
$customerName = '';
$customerId = '';
$quoFrom = null;
$quo_code = '';
$updated_at = '';

// Jika quonum tersedia, ambil data customer dan quotation dalam satu query
if ($quonum) {
    // Ambil data customer dan quo dalam satu query
    $sql = "SELECT c.id AS customer_id, c.address, c.phone_number, c.name AS customer_name, q.from, q.add_note, q.quo_code, q.updated_at, q.users_id, u.alias, u.ttd 
            FROM quo q 
            JOIN customer c ON q.customer_id = c.id 
            JOIN users u ON u.id = q.users_id
            WHERE q.quo_num = ? AND q.id = ?";

    // Prepare dan eksekusi query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('si', $quonum, $quoId);
        $stmt->execute();
        $stmt->bind_result($customerId, $address, $phone_number, $customerName, $quoFrom, $addNote, $quo_code, $updated_at, $user_id_it, $aliasData, $ttdData);
        $stmt->fetch();  // Mengambil hasil pertama
        $stmt->close();
    }
}

// Menentukan apakah input readonly atau tidak
$readonly = $quonum ? '' : 'readonly';  // Menambahkan atribut readonly hanya jika $quonum tidak ada

?>

<script>
    window.onload = function() {
        window.print();
    };
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
    <style>
        body{
            background-color: white;
        }
        .borderBlack {
            border:  1px solid; 
            border-style: ridge;
            /*border-width: thin;*/
        }

        .card {
            box-shadow: none !important;
        }
        @media print {
            @page {
                size: A4 portrait; /* Mengatur ukuran kertas A4 dan orientasi potret */
                margin: 1cm 0.5cm 0.5cm 0.5cm; /* Margin: atas 1 cm, kanan 0.5 cm, bawah 0.5 cm, kiri 0.5 cm */
            }
            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            header, footer {
                display: none; /* Hilangkan header dan footer default browser */
            }
        }
    </style>


</head>

<body style="margin:0px; padding:0px;">
    <div class="wrapper py-0 my-0 mt-n1">

        <div class="card pt-0 my-0" id="printArea">
            <div class="card-header pt-2 mb-0 pb-3">
                <div class="row">
                    <div class="col-9 d-flex align-items-start justify-content-center flex-column">
                        <?php
                        if ($quoFrom == "CV") {
                            $quoFrom = "CV. GRAVITTI TECHNOLOGY";
                            $quoAddress = "Harco Mangga Dua Blok Blok A3 No 156 Sawah Besar, Jakarta Pusat";
                        }
                        ?>
                        <div class="card-title"><?php echo $quoFrom; ?></div>
                        <small><?php echo $quoAddress; ?></small>
                    </div>
                    <div class="col-3 text-center">
                        <img src="assets/img/loewix.png" alt="Loewix" class="img-responsive img-fluid w-60 me-3" style="max-height:50px;">
                    </div>
                </div>
            </div>
            <div class="card-body mt-0 pt-2">
                <div class="row">
                    <div class="col-6 small" style="text-transform:capitalize; font-size:12px;">
                        <div class="row">
                            <div class="col-3">Ref. No</div>
                            <div class="col-9">: <?php echo htmlspecialchars($quo_code, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-3">To</div>
                            <div class="col-9">: <?php echo htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <!--<div class="row">-->
                        <!--    <div class="col-3">Address</div>-->
                        <!--    <div class="col-9">: <?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?></div>-->
                        <!--</div>-->
                    </div>

                    <div class="col-2" id="noPrint">
                    </div>
                    <div class="col-4 small" style="text-transform:capitalize; font-size:12px;">
                        <div class="row">
                            <div class="col-12" style="font-size:14px; padding-bottom:0px;"><b>QUOTATION</b></div>
                        </div>
                        <div class="row" style="margin-top:-3px;">
                            <div class="col-3">Date</div>
                            <div class="col-9">: <?php echo date('d F Y', strtotime($updated_at)); ?></div>
                        </div>
                    </div>


                    <?php
                    include "quo_data.php";
                    $no = 1;
                    
                    if($pict == "Y"){
                        include "withPic.php";
                    }
                    else{
                        include "noPic.php";
                    }
                    
                    $stmt->close();
                    $totalStmt->close();
                    ?>

                    <div class="col-12 mt-3">
                        <div class="row d-flex justify-content-center align-items-start">
                            <div class="col-9">
                                <b>Note : </b><br>
                                <span class="padding-left:20px;"><?php echo !empty($addNote) ? nl2br($addNote) : '- Price subject to change without prior notice'; ?></span>
                            </div>

                            <div class="col-3 text-center">
                                <b>Best Regards,</b><br>
                                <?php

                                if (empty($ttdData)) {
                                    $ttdData = "ttd.png";
                                }
                                ?>
                                <!-- Tampilkan tanda tangan -->
                                <img src="uploads/signature/<?php echo $ttdData; ?>" class="img-responsive img-fluid" alt="TTD" style="max-height:40px">
                                <p style="text-decoration: underline; font-weight:bold;"><?php echo htmlspecialchars($aliasData); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-1 text-center" style="text-transform:uppercase;">
                        <p><b>Thank You For Your Business</b></p>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <?php include 'core-scripts.php'; ?>

</body>

</html>