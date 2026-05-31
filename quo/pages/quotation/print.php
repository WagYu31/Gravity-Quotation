<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID Penawaran tidak valid.");
}
$quotationId = (int)$_GET['id'];

$stmt = $conn->prepare("
    SELECT q.*, c.name as customer_name, c.address, u.alias as user_alias, u.ttd 
    FROM quotations q 
    JOIN customer c ON q.customer_id = c.id
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ? AND q.deleted_at IS NULL
");
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();
if (!$quote) die("Error: Penawaran tidak ditemukan.");

$items_stmt = $conn->prepare("
    SELECT qi.*, b.image, b.link_1, b.name_link_1, b.link_2, b.name_link_2, b.kategori, b.satuan 
    FROM quotation_items qi 
    LEFT JOIN barang b ON qi.barang_id = b.id 
    WHERE qi.quotation_id = ?
");
$items_stmt->bind_param("i", $quotationId);
$items_stmt->execute();
$quote_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$gross_subtotal = 0;
$subtotal_after_item_discounts = 0;
foreach ($quote_items as $item) {
    $gross_subtotal += (float)$item['item_price'] * (int)$item['quantity'];
    $subtotal_after_item_discounts += (float)$item['total_amount'];
}

$overall_discount_amount = 0;
if ($quote['overall_discount_type'] == 'PERCENT') {
    $overall_discount_amount = $subtotal_after_item_discounts * ((float)$quote['overall_discount_value'] / 100);
} else {
    $overall_discount_amount = (float)$quote['overall_discount_value'];
}

$total_discount_to_display = ($gross_subtotal - $subtotal_after_item_discounts) + $overall_discount_amount;
$final_subtotal = $subtotal_after_item_discounts - $overall_discount_amount;

$ppn = 0;
if ($quote['issuer'] == 'CV') {
    $ppn = $final_subtotal * 0.11;
}

$grand_total = $final_subtotal + $ppn;

$signature_path = '../../assets/uploads/signature/signAll.png';
if (!empty($quote['ttd'])) {
    $user_signature_path = '../../assets/uploads/signature/' . $quote['ttd'];
    if (file_exists($user_signature_path)) {
        $signature_path = $user_signature_path;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Penawaran <?php echo htmlspecialchars($quote['quotation_code']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Arial', sans-serif; font-size: 9pt; }
        .page-container {
            display: flex;
            justify-content: center;
        }
        .card { 
            box-shadow: none !important; 
            border: none;
            width: 21cm;
            min-height: 29.7cm;
        }
        .table-bordered th, .table-bordered td {
            padding: 2px;
            vertical-align: top;
            border: 1px solid #7f7f7f;
            line-height: 10pt;
            align-items: middle;
        }
        .table-bordered th{
            font-weight: 700;
        }
        .table-bordered {
            line-height: 1.2;
            border: 1px solid #7f7f7f;
            table-layout: fixed;
            width: 100%;
        }
        .card-title { font-size: 13pt; margin-bottom: 2px; }
        b, strong { font-size: 9pt; }
        .controls { text-align: center; margin: 1rem 0; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }
            body { margin: 0; padding: 0; background-color: white; font-size: 9pt; }
            .controls, .modal, .modal-backdrop { display: none !important; }
            .page-container { display: block; }

            /* Print options: hide images */
            body.hide-images .col-picture { display: none !important; }
            body.hide-images .col-desc-header { width: 55% !important; }
            body.hide-images .col-price-header { width: 15% !important; }
            body.hide-images .col-amount-header { width: 15% !important; }
            body.hide-images .colspan-toggle { /* handled via JS colspan */ }

            /* Print options: hide links */
            body.hide-links .print-links { display: none !important; }
        }
    </style>
</head>
<body style="margin:0px; padding:0px;">
<div class="controls text-center my-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#printOptionsModal">Cetak Penawaran</button>
    <a href="../../dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>
<div class="page-container">
    <div class="card pt-0 my-0" id="printArea">
        <div class="card-header bg-white pt-2 mb-0 pb-2" style="border-bottom: 1px solid #afafaf;">
            <div class="row">
                <div class="col-9 d-flex align-items-start justify-content-center flex-column">
                    <?php
                    $quoFrom = $quote['issuer'];
                    $quoAddress = '';
                    if ($quoFrom == "CV") {
                        $quoFrom = "CV. GRAVITTI TECHNOLOGY";
                        $quoAddress = "Harco Mangga Dua Blok A3 No 156 Sawah Besar, Jakarta Pusat";
                    } else {
                        $quoFrom = "LOEWIX";
                    }
                    ?>
                    <div class="card-title fw-bold"><?php echo $quoFrom; ?></div>
                    <?php if(!empty($quoAddress)): ?><small style="font-size:8pt;"><?php echo $quoAddress; ?></small><?php endif; ?>
                </div>
                <div class="col-3 text-end">
                    <img src="../../assets/img/logo3.png" alt="Loewix" class="img-responsive img-fluid" style="max-height:40px;">
                </div>
            </div>
        </div>
        <div class="card-body mt-0 pt-2">
            <div class="row">
                <div class="col-6 small" style="font-size:9pt;">
                    <div class="row">
                        <div class="col-3">Ref. No</div>
                        <div class="col-9">: <?php echo htmlspecialchars($quote['quotation_code'] ?? 'DRAFT-'.$quote['id']); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-3">To</div>
                        <div class="col-9">: <?php echo htmlspecialchars($quote['customer_name']); ?></div>
                    </div>
                </div>
                <div class="col-2"></div>
                <div class="col-4 small" style="font-size:9pt;">
                    <div class="row">
                        <div class="col-12" style="font-size:12pt;"><b>QUOTATION</b></div>
                    </div>
                    <div class="row" style="margin-top:-2px;">
                        <div class="col-3">Date</div>
                        <div class="col-9">: <?php echo date('d F Y', strtotime($quote['quotation_date'])); ?></div>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="row">
                        <table class="table-bordered" style="color:black;">
                            <thead>
                                <tr class="text-center">
                                    <th class="col-no-header" style="width: 5%;">NO</th>
                                    <th class="col-desc-header" style="width: 48%;">DESCRIPTION</th>
                                    <th class="col-picture" style="width: 12%;">PICTURE</th>
                                    <th class="col-qty-header" style="width: 10%;">QTY</th>
                                    <th class="col-price-header" style="width: 12%;">PRICE</th>
                                    <th class="col-amount-header" style="width: 13%;">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($quote_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?= $no; ?></td>
                                    <td>
                                        <b style="font-size:9pt; text-transform:uppercase; font-weight:600;"><?= htmlspecialchars($item['item_name'] ?? $item['kategori'] ?? ''); ?></b><br>
                                        <?php if (!empty($item['item_description'])): ?>
                                            <span style="font-size:8pt; line-height:0.8; white-space: pre-wrap;"><?= htmlspecialchars($item['item_description']); ?></span>
                                        <?php endif; ?>
                                        <span class="print-links mt-0">
                                            <?php if (!empty($item['link_1']) || !empty($item['link_2'])): ?><br><?php endif; ?>
                                            <?php if (!empty($item['link_1'])): ?>
                                                <a style="font-size:8pt;" href="<?= htmlspecialchars($item['link_1']); ?>" target="_blank">
                                                    <?= htmlspecialchars($item['name_link_1'] ?? 'Link 1'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['link_2'])): ?>
                                                <a style="font-size:8pt; margin-left:5px;" href="<?= htmlspecialchars($item['link_2']); ?>" target="_blank">
                                                    <?= htmlspecialchars($item['name_link_2'] ?? 'Link 2'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="text-center col-picture">
                                        <?php if (!empty($item['image']) && file_exists('../../assets/uploads/barang/' . $item['image'])): ?>
                                            <img src="../../assets/uploads/barang/<?= htmlspecialchars($item['image']); ?>" style="max-height:40px; width:100%; object-fit:contain;">
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="text-transform:uppercase;"><?= htmlspecialchars($item['quantity']); ?> <?= htmlspecialchars($item['satuan']); ?></td>
                                    <td class="text-end px-2">
                                        <span class="float-start">Rp</span>
                                        <?= number_format($item['item_price'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-end px-2">
                                        
                                        <?php
                                        $amount = $item['item_price'] * $item['quantity'];
                                    
                                        echo '<span class="float-start">Rp</span> ' . number_format($amount, 0, ',', '.');
                                        ?>
                                    </td>
                                </tr>
                                <?php $no++; endforeach; ?>
                                <tr>
                                    <td colspan="5" class="text-end px-2 colspan-toggle"><strong>Subtotal</strong></td>
                                    <td class="text-end px-2">
                                        <span class="float-start">Rp</span>
                                        <?= number_format($gross_subtotal, 0, ',', '.'); ?>
                                    </td>
                                </tr>
                                <?php if ($total_discount_to_display > 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-end px-2 colspan-toggle"><strong>Discount</strong></td>
                                        <td class="text-end px-2">
                                            <span class="float-start">Rp</span>
                                            <?= number_format($total_discount_to_display, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($quote['issuer'] === 'CV'): ?>
                                    <tr>
                                        <td colspan="5" class="text-end px-2 colspan-toggle"><strong>PPN 11%</strong></td>
                                        <td class="text-end px-2">
                                            <span class="float-start">Rp</span>
                                            <?= number_format($ppn, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="5" class="text-end px-2 colspan-toggle"><strong>Grand Total</strong></td>
                                    <td class="text-end px-2 fw-bold">
                                        <span class="float-start">Rp</span>
                                        <?= number_format($grand_total, 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <div class="row d-flex justify-content-between align-items-start">
                        <div class="col-8">
                            <b>Note : </b><br>
                            <div style="white-space: pre-wrap; font-size: 8pt;"><?php echo !empty(trim($quote['notes'])) ? htmlspecialchars($quote['notes']) : '- Price subject to change without prior notice'; ?></div>
                        </div>
                        <div class="col-3 text-center">
                            <b>Best Regards,</b><br>
                            <img src="<?php echo $signature_path; ?>" alt="TTD" style="max-height:35px; margin: 5px 0;">
                            <div style="text-decoration: underline; font-weight:bold;"><?php echo htmlspecialchars($quote['user_alias']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-1 text-center" style="text-transform:uppercase;">
                    <p style="font-size: 8pt;"><b>Thank You For Your Business</b></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="printOptionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Opsi Cetak</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p>Pilih elemen tambahan yang ingin disertakan:</p>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="includeImages" checked><label class="form-check-label" for="includeImages">Sertakan Kolom Gambar</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="includeLinks" checked><label class="form-check-label" for="includeLinks">Sertakan Link Produk</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="confirmPrintBtn">Cetak Sekarang</button></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Persistent dynamic print stylesheet — survives afterprint on mobile
var printStyleEl = document.createElement('style');
printStyleEl.id = 'dynamic-print-css';
document.head.appendChild(printStyleEl);

document.getElementById('confirmPrintBtn').addEventListener('click', function() {
    var includeImages = document.getElementById('includeImages').checked;
    var includeLinks = document.getElementById('includeLinks').checked;

    // Build @media print CSS — persistent, cannot be undone
    var css = '@media print {\n';
    if (!includeImages) {
        css += '  .col-picture { display: none !important; }\n';
        css += '  .col-desc-header { width: 55% !important; }\n';
        css += '  .col-price-header { width: 15% !important; }\n';
        css += '  .col-amount-header { width: 15% !important; }\n';
    }
    if (!includeLinks) {
        css += '  .print-links { display: none !important; }\n';
    }
    css += '}\n';
    printStyleEl.textContent = css;

    // Apply layout changes to DOM (both screen + print)
    document.querySelectorAll('.col-picture').forEach(function(cell) {
        cell.style.display = includeImages ? 'table-cell' : 'none';
    });
    document.querySelectorAll('.print-links').forEach(function(div) {
        div.style.display = includeLinks ? 'block' : 'none';
    });
    document.querySelector('.col-desc-header').style.width = includeImages ? '48%' : '55%';
    document.querySelector('.col-price-header').style.width = includeImages ? '12%' : '15%';
    document.querySelector('.col-amount-header').style.width = includeImages ? '13%' : '15%';

    var newColspan = includeImages ? 5 : 4;
    document.querySelectorAll('.colspan-toggle').forEach(function(cell) {
        cell.setAttribute('colspan', newColspan);
    });

    // Close modal
    var modalEl = document.getElementById('printOptionsModal');
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    // Delay for mobile
    setTimeout(function() {
        window.print();
    }, 500);
});

// NO afterprint handler — this is a print page, no need to restore state.
// User can re-open the modal and toggle options if needed.
</script>
</body>
</html>