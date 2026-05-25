<?php
// Panggil file-file penting dan mulai sesi
session_start(); // Pastikan sesi dimulai di file ini atau di 'db.php'
include '../../includes/db.php';

// Keamanan sesi & Validasi Input
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID Penawaran tidak valid.");
}
$quotationId = (int)$_GET['id'];

// Ambil data utama penawaran, termasuk nama user dan file ttd-nya
$stmt = $conn->prepare("
    SELECT q.*, c.name as customer_name, u.name as user_name, u.ttd 
    FROM quotations q 
    JOIN customer c ON q.customer_id = c.id
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ? AND q.deleted_at IS NULL
");
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();
if (!$quote) die("Error: Penawaran tidak ditemukan.");

// Ambil item-item penawaran, gabungkan dengan data dari tabel master barang
$items_stmt = $conn->prepare("
    SELECT qi.*, b.image, b.link_1, b.name_link_1, b.link_2, b.name_link_2 
    FROM quotation_items qi 
    LEFT JOIN barang b ON qi.barang_id = b.id 
    WHERE qi.quotation_id = ?
");
$items_stmt->bind_param("i", $quotationId);
$items_stmt->execute();
$quote_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$items_stmt->close();
$conn->close();

// === PERBAIKAN BARU: Kalkulasi Subtotal dan Total Diskon ===
$gross_subtotal = 0;
$subtotal_after_item_discounts = 0;

foreach ($quote_items as $item) {
    // Subtotal kotor sebelum diskon apa pun
    $gross_subtotal += (float)$item['item_price'] * (int)$item['quantity'];
    // Subtotal setelah diskon per-item
    $subtotal_after_item_discounts += (float)$item['total_amount'];
}

// 1. Total diskon dari semua item individual
$total_item_discounts = $gross_subtotal - $subtotal_after_item_discounts;

// 2. Diskon keseluruhan (overall) yang diterapkan pada subtotal kotor
$overall_discount_amount = 0;
if ($quote['overall_discount_type'] == 'PERCENT') {
    // Diskon persentase dihitung dari subtotal kotor
    $overall_discount_amount = $gross_subtotal * ((float)$quote['overall_discount_value'] / 100);
} else { // Asumsi 'AMOUNT' atau null
    $overall_discount_amount = (float)$quote['overall_discount_value'];
}

// 3. Jumlahkan kedua jenis diskon untuk ditampilkan
$total_discount_to_display = $total_item_discounts + $overall_discount_amount;
// ====================================================================


// Tentukan path untuk signature
$signature_path = '../../assets/uploads/signature/signAll.png'; // Default universal
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
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; font-size: 10pt; }
        .page {
            background: white; width: 21cm; min-height: 29.7cm;
            padding: 0.4cm; margin: 0.2cm auto; border: 1px #D3D3D3 solid;
        }
        .controls { text-align: center; margin: 20px; }
        main { flex-grow: 1; }
        
        .header-container, .info-container, .footer-container, .signature-container { display: flex; justify-content: space-between; align-items: flex-start; }
        
        .item-list { border: 1px solid #000; margin-top: 20px; padding:0; }
        .item-row { display: flex; }
        .item-row + .item-row { border-top: 1px solid #000; }
        .item-row > div { padding: 4px 6px; border-left: 1px solid #000; }
        .item-row > div:first-child { border-left: none; }
        .item-header { background-color: #E6E6E6; font-weight: bold; text-align: center; }
        
        .col-no { width: 4%; text-align: center; }
        .col-desc { width: 41%; }
        .col-pic { width: 15%; text-align: center; }
        .col-qty { width: 10%; text-align: center; }
        .col-price { width: 15%; text-align: center; }
        .col-amount { width: 15%; text-align: center; }
        
        .total-area {
            width: 40%;
            border: 1px solid #000;
        }
        .total-area .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 6px;
        }
        .total-area .total-row + .total-row {
            border-top: 1px solid #000;
        }
        .total-area .grand-total-row {
            background-color:#E6E6E6;
            font-weight: bold;
        }
        
        .item-description { white-space: pre-wrap; font-size: 9pt; color: #333; }
        .item-links a { display: block; font-size: 8pt; margin-top: 5px; color: #0066cc; text-decoration: none; }
        .item-image { max-width: 80px; max-height: 80px; object-fit: contain; }
        .fw-bold { font-weight: bold; }
    
        @media print {
            body, .page { margin: 0.2cm; padding: 0.4cm; border: none; box-shadow: none; }
            .controls, .modal, .modal-backdrop { display: none !important; }
            @page { size: A4; margin: 0.7cm; }
            .no-print-links .print-links { display: none !important; }
            footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="controls">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#printOptionsModal">Cetak Penawaran</button>
    <a href="../../dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>

<div id="printable-area" class="page">
    <header>
        <div class="header-container" style="margin-bottom: 10px;">
            <div class="company-info" style="width: 50%;">
                <?php if ($quote['issuer'] == 'CV'): ?>
                    <h1 style="font-size: 14pt; margin: 0; font-weight: bold;">CV. GRAVITTI TECHNOLOGY</h1>
                    <p style="margin-top: 5px; font-size: 9pt; color: #333;">Harco Mangga Dua Blok A3 No 156<br>Sawah Besar, Jakarta Pusat</p>
                <?php else: ?>
                    <h1 style="font-size: 14pt; margin: 0; font-weight: bold;">LOEWIX</h1>
                <?php endif; ?>
            </div>
            <div class="logo" style="width: 50%; text-align: right;">
                <img src="../../assets/img/logo3.png" alt="Loewix Logo" style="width: 120px;">
            </div>
        </div>
        <div class="info-container" style="margin-bottom: 20px; font-size: 9pt;">
            <div style="width: 65%;">
                <div><span style="display: inline-block; width: 60px;">Ref. No</span>: <?php echo htmlspecialchars($quote['quotation_code'] ?? 'DRAFT-'.$quote['id']); ?></div>
                <div><span style="display: inline-block; width: 60px;">To</span>: <?php echo htmlspecialchars($quote['customer_name']); ?></div>
            </div>
            <div style="width: 35%;">
                <div><span style="font-weight: bold;">QUOTATION</span></div>
                <div><span style="display: inline-block; width: 40px;">Date</span>: <?php echo date('d F Y', strtotime($quote['quotation_date'])); ?></div>
            </div>
        </div>
    </header>

    <main>
        <div class="item-list">
            <div class="item-row item-header">
                <div class="col-no">NO</div>
                <div class="col-desc">DESCRIPTION</div>
                <div class="col-pic col-picture">PICTURE</div>
                <div class="col-qty">QTY</div>
                <div class="col-price" style="text-align:right;">PRICE</div>
                <div class="col-amount" style="text-align:right;">AMOUNT</div>
            </div>
            <?php $no = 1; foreach ($quote_items as $item): ?>
            <div class="item-row">
                <div class="col-no"><?php echo $no++; ?></div>
                <div class="col-desc">
                    <div class="fw-bold"><?php echo htmlspecialchars($item['item_name']); ?></div>
                    <?php if(!empty(trim($item['item_description']))): ?><div class="item-description"><?php echo htmlspecialchars($item['item_description']); ?></div><?php endif; ?>
                    <div class="print-links">
                        <?php if(!empty($item['link_1'])): ?><a href="<?php echo $item['link_1']; ?>" target="_blank"><?php echo htmlspecialchars($item['name_link_1'] ?? 'Katalog Produk'); ?></a><?php endif; ?>
                        <?php if(!empty($item['link_2'])): ?><a href="<?php echo $item['link_2']; ?>" target="_blank"><?php echo htmlspecialchars($item['name_link_2'] ?? 'Video Produk'); ?></a><?php endif; ?>
                    </div>
                </div>
                <div class="col-pic col-picture">
                    <?php if(!empty($item['image']) && file_exists('../../assets/uploads/barang/' . $item['image'])): ?>
                        <img src="../../assets/uploads/barang/<?php echo $item['image']; ?>" class="item-image" alt="Produk">
                    <?php endif; ?>
                </div>
                <div class="col-qty"><?php echo $item['quantity'] . ' ' . htmlspecialchars($item['unit']); ?></div>
                <div class="col-price d-flex justify-content-between"><span>Rp </span><span><?php echo number_format($item['item_price'], 0, ',', '.'); ?></span></div>
                <div class="col-amount d-flex justify-content-between"><span>Rp </span><span><?php echo number_format($item['total_amount'], 0, ',', '.'); ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer style="font-size: 9pt;">
        <div class="footer-container">
            <div class="note-area" style="width: 60%; vertical-align: bottom; padding-top:10px;">
                 <?php if(!empty(trim($quote['notes']))): ?>
                    <p style="margin: 0 0 10px 0; font-weight: bold;">Note:</p>
                    <p style="margin: 0; white-space: pre-wrap;"><?php echo htmlspecialchars($quote['notes']); ?></p>
                <?php endif; ?>
                 <p style="margin-top: 20px; font-weight: bold;">THANK YOU FOR YOUR BUSINESS</p>
            </div>
            
            <div class="total-area" style="border-top:0px;">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp <?php echo number_format($gross_subtotal, 0, ',', '.'); ?></span>
                </div>
                <div class="total-row">
                    <span>Discount</span>
                    <span>
                        Rp <?php echo number_format($total_discount_to_display, 0, ',', '.'); ?>
                    </span>
                </div>
                <?php 
                if ($quote['issuer'] == 'CV'): 
                    $totalAfterAllDiscounts = $gross_subtotal - $total_discount_to_display;
                    $ppn = $totalAfterAllDiscounts * 0.11; 
                ?>
                <div class="total-row">
                    <span>PPN (11%)</span>
                    <span><?php echo 'Rp '.number_format($ppn, 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row grand-total-row justify-content">
                    <span>Grand Total</span>
                    <span>Rp <?php echo number_format($quote['grand_total'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        <div class="signature-container" style="margin-top: 20px; justify-content: flex-end;">
            <div style="text-align: center;">
                <p style="margin: 0;">Best Regards,</p>
                <img src="<?php echo $signature_path; ?>" alt="Signature" style="height: 50px; margin-top: 10px; margin-bottom: 5px;">
                <p style="margin: 0; font-weight: bold;"><?php echo htmlspecialchars($quote['user_name']); ?></p>
            </div>
        </div>
    </footer>
</div>

<div class="modal fade" id="printOptionsModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Opsi Cetak</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <p>Pilih elemen tambahan yang ingin disertakan:</p>
    <div class="form-check"><input class="form-check-input" type="checkbox" id="includeImages" checked><label class="form-check-label" for="includeImages">Sertakan Kolom Gambar</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" id="includeLinks" checked><label class="form-check-label" for="includeLinks">Sertakan Link Produk</label></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="confirmPrintBtn">Cetak Sekarang</button></div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('confirmPrintBtn').addEventListener('click', function() {
    var includeImages = document.getElementById('includeImages').checked;
    var includeLinks = document.getElementById('includeLinks').checked;
    
    document.querySelectorAll('.col-pic').forEach(function(cell) {
        cell.style.display = includeImages ? '' : 'none';
    });
    
    document.querySelectorAll('.print-links').forEach(function(div) {
        div.style.display = includeLinks ? '' : 'none';
    });

    var descCells = document.querySelectorAll('.col-desc');
    var picWidth = '15%'; 
    var descWidth = '41%'; 
    
    if (!includeImages) {
        descCells.forEach(function(cell) {
            cell.style.width = `calc(${descWidth} + ${picWidth})`;
        });
    } else {
         descCells.forEach(function(cell) {
            cell.style.width = descWidth;
        });
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('printOptionsModal'));
    modal.hide();
    
    setTimeout(function() {
        window.print();
    }, 500);
});
</script>

</body>
</html>