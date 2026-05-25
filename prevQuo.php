<?php
include "conn.php";

// Ambil data quonum dari URL
$quonum = $_GET['quonum'] ?? null;  // Gunakan operator null coalescing untuk set nilai default
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
        .borderBlack {
            /* border-color: black; */
            border-style: ridge;
            border-width: thin;
        }

        .card {
            box-shadow: none !important;
        }
    </style>


</head>

<body>
    <div class="wrapper py-0 my-0 mt-n1">

        <div class="card pt-0 my-0" id="printArea">
            <div class="card-header pt-2">
                <div class="row">
                    <div class="col-8 d-flex align-items-start justify-content-end flex-column">
                        <?php
                        if ($quoFrom == "CV") {
                            $quoFrom = "CV. GRAVITTI TECHNOLOGY";
                            $quoAddress = "Harco Mangga Dua Blok Blok A3 No 156 Sawah Besar, Jakarta Pusat";
                        }
                        ?>
                        <div class="card-title"><?php echo $quoFrom; ?></div>
                        <small><?php echo $quoAddress; ?></small>
                    </div>
                    <div class="col-4 text-end">
                        <img src="assets/img/loewix.png" alt="Loewix" class="img-responsive img-fluid w-60 me-3" style="max-height:70px;">
                    </div>
                </div>
            </div>
            <div class="card-body mt-0">
                <div class="row">
                    <div class="col-12 text-center my-2">
                        <h2>QUOTATION</h2>
                    </div>
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
                    <div class="col-4" style="text-transform:capitalize; font-size:12px;">
                        <div class="row">
                            <div class="col-3">Date</div>
                            <div class="col-9">: <?php echo date('d F Y', strtotime($updated_at)); ?></div>
                        </div>
                        <!--<div class="row">-->
                        <!--    <div class="col-3">Phone</div>-->
                        <!--    <div class="col-9">: <?php echo htmlspecialchars($phone_number, ENT_QUOTES, 'UTF-8'); ?></div>-->
                        <!--</div>-->
                    </div>


                    <?php
                    include "quo_data.php";
                    $no = 1;
                    ?>

                    <div class="col-12 mt-4">
                        <div class="row borderBlack">
                            <!-- Header -->
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 6%; max-width: 6%;">
                                <strong>No</strong>
                            </div>
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 43%; max-width: 43%;">
                                <strong>Description</strong>
                            </div>
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 14%; max-width: 14%;">
                                <strong>Image</strong>
                            </div>
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 8%; max-width: 8%;">
                                <strong>Qty</strong>
                            </div>
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 14%; max-width: 14%;">
                                <strong>Price</strong>
                            </div>
                            <div class="text-center py-2 px-1 borderBlack" style="flex-basis: 15%; max-width: 15%;">
                                <strong>Amount</strong>
                            </div>

                            <!-- Dynamic Rows -->
                            <?php while ($row = $result->fetch_assoc()): ?>
                            
                                <div class="text-center borderBlack p-1 py-3" style="flex-basis: 6%; max-width: 6%; font-size:11px; text-transform:capitalize;">
                                    <?= htmlspecialchars($no); ?>
                                </div>
                                
                                <div class="borderBlack p-2" style="flex-basis: 43%; max-width: 43%; text-align:justify;">
                                    <b><?= htmlspecialchars($row['kategori']); ?></b>
                                    <br>
                                    <span style="font-size:11px; line-height:1.0; text-align:justify; margin-bottom:10px;">
                                        <?= htmlspecialchars($row['description']); ?>
                                    </span>
                                    <br>
                                    <?php
                                    // Periksa apakah fungsi sudah ada
                                    if (!function_exists('addHttps')) {
                                        // Fungsi untuk menambahkan https:// jika tidak ada
                                        function addHttps($url)
                                        {
                                            if (!empty($url) && !preg_match('/^(https?:\/\/)/', $url)) {
                                                return 'https://' . $url;
                                            }
                                            return $url;
                                        }
                                    }
                                    ?>


                                    <?php if (!empty($row['link_1'])): ?>
                                        <a class="btn btn-light btn-sm" style="border:1px solid darkgrey; font-size:10px; margin-top:5px;padding:4px; padding-top:1px; padding-bottom:1px;" href="<?= htmlspecialchars(addHttps($row['link_1'])); ?>">
                                            <?= htmlspecialchars($row['name_link_1']); ?>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($row['link_2'])): ?>
                                        <a class="btn btn-light btn-sm ms-2" style="border:1px solid darkgrey; font-size:10px; margin-top:5px;padding:4px; padding-top:1px; padding-bottom:1px;" href="<?= htmlspecialchars(addHttps($row['link_2'])); ?>">
                                            <?= htmlspecialchars($row['name_link_2']); ?>
                                        </a>
                                    <?php endif; ?>

                                </div>
                                <div class="borderBlack p-2" style="flex-basis: 14%; max-width: 14%; text-align:center;">
                                    <img src="uploads/products/<?= htmlspecialchars($row['image']); ?>"
                                        class="img-fluid"
                                        style="max-height:60px; width:auto; object-fit:contain;">
                                </div>
                                <div class="text-center borderBlack p-1 py-3" style="flex-basis: 8%; max-width: 8%; font-size:11px; text-transform:capitalize;">
                                    <?= htmlspecialchars($row['qty']); ?> <?= htmlspecialchars($row['satuan']); ?>
                                </div>
                                <div class="text-start borderBlack p-2 py-3 d-flex justify-content-between align-items-start" style="flex-basis: 14%; max-width: 14%; font-size:11px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($row['price'], 0); ?>
                                    </span>
                                </div>
                                <div class="text-start borderBlack p-2 py-3 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:11px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($row['amount'], 0); ?>
                                    </span>
                                </div>
                            <?php
                            $no++;
                            endwhile; ?>

                            <!-- Subtotal -->
                            <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                <strong>Sub Total</strong>
                            </div>
                            <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                <span class="text-start">Rp</span>
                                <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                    <?= number_format($total_sub_amount, 0); ?>
                                </span>
                            </div>

                            <!-- Discount -->
                            <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                <strong>Discount Product</strong>
                            </div>
                            <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                <span class="text-start">Rp</span>
                                <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                    <?= number_format($total_discount, 0); ?>
                                </span>
                            </div>

                            <?php
                            if ($disc_all != 0 || $disc_all != NULL) {
                            ?>

                                <!-- Total -->
                                <!-- <div class="col-8 borderBlack text-end p-2" style="font-size:12px;">
                                    <strong>Total</strong>
                                </div>
                                <div class="col-4 borderBlack text-right p-2" style="font-size:12px;">
                                    Rp <?= number_format($total_amount, 0); ?>
                                </div> -->
                                <!-- Special Discount -->
                                <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                    <strong>Special Discount</strong>
                                </div>
                                <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($disc_all, 0); ?>
                                    </span>
                                </div>
                            <?php
                            }
                            ?>


                            <!-- PPN and Grand Total -->
                            <?php if ($from === 'CV'): ?>
                                <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                    <strong>PPN 11%</strong>
                                </div>
                                <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($ppn, 0); ?>
                                    </span>
                                </div>
                                <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                    <strong>Grand Total</strong>
                                </div>
                                <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($total_ppn, 0); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="text-end borderBlack p-2" style="flex-basis: 85%; max-width: 85%; font-size:12px; text-transform:capitalize;">
                                    <strong>Grand Total</strong>
                                </div>
                                <div class="text-start borderBlack p-2 d-flex justify-content-between align-items-start" style="flex-basis: 15%; max-width: 15%; font-size:12px; text-transform:capitalize;">
                                    <span class="text-start">Rp</span>
                                    <span class="text-end" style="margin-left: auto; text-align: right; width: calc(100% - 20px);">
                                        <?= number_format($total_non_ppn, 0); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $stmt->close();
                    $totalStmt->close();
                    ?>

                    <div class="col-12 mt-5">
                        <div class="row d-flex justify-content-center align-items-start">
                            <div class="w-50">
                                <p><b>Note : </b></p>
                                <span class="padding-left:20px;"><?php echo !empty($addNote) ? nl2br($addNote) : '- Price subject to change without prior notice'; ?></span>
                            </div>

                            <div class="w-50 text-center">
                                <p><b>Best Regards,</b></p>
                                <?php

                                if (empty($ttdData)) {
                                    $ttdData = "ttd.png";
                                }
                                ?>
                                <!-- Tampilkan tanda tangan -->
                                <img src="uploads/signature/<?php echo $ttdData; ?>" class="img-responsive img-fluid" alt="TTD" style="max-height:50px">
                                <p style="text-decoration: underline; font-weight:bold;"><?php echo htmlspecialchars($aliasData); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3 text-center" style="text-transform:uppercase;">
                        <p><b>Thank You For Your Business</b></p>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <?php include 'core-scripts.php'; ?>

</body>

</html>