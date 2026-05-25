<?php
include "conn.php";

// 1. OPTIMASI QUERY: Ambil semua data dalam satu panggilan database.
// Ini jauh lebih efisien daripada query di dalam loop.
$query = "
    SELECT 
        q.id AS quoId,
        q.quo_num,
        q.quo_code,
        q.updated_at,
        c.name AS customer_name,
        p.so AS no_so,
        p.tanggal_rencana_pengerjaan,
        p.alamat_instalasi,
        p.contact_person,
        p.sj AS no_sj,
        p.tanggal_sj AS tgl_sj,
        p.bast,
        p.tanggal_bast AS tgl_bast,
        p.keterangan,
        p.invoice,
        p.tanggal_inv AS tgl_inv,
        p.valid AS isiValid,
        q.kegiatan_kode
    FROM (
        -- Subquery untuk mendapatkan ID terakhir dari setiap `quo_num` yang statusnya 'saved'
        SELECT MAX(id) as max_id
        FROM quo
        WHERE deleted_at IS NULL AND status = 'saved'
        GROUP BY quo_num
    ) AS latest_quo
    JOIN quo q ON q.id = latest_quo.max_id
    JOIN customer c ON q.customer_id = c.id
    LEFT JOIN progress p ON q.id = p.quo_id
    WHERE q.deleted_at IS NULL AND q.status = 'saved'
    ORDER BY q.updated_at DESC
";

$result = $conn->query($query);
$quotations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $quotations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'head.php'; ?>
</head>
<body>
    <div class="wrapper">
        
        <?php include 'nav/sidebar.php'; ?>

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
                        <h3 class="fw-bold mb-3">Quotation Progress</h3>
                        <ul class="breadcrumbs mb-3">
                            <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Data</a></li>
                            <li class="separator"><i class="icon-arrow-right"></i></li>
                            <li class="nav-item"><a href="#">Quotation Progress</a></li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Daftar Quotation</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="quotationTable" class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Customer</th>
                                                    <th>Quotation</th>
                                                    <th>Preview</th>
                                                    <th>SO</th>
                                                    <th>SJ</th>
                                                    <th>BAST</th>
                                                    <th>Invoice</th>
                                                    <th>Validasi</th>
                                                    <th>Request</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quotations as $row) : ?>
                                                <?php
                                                    $quoId = $row['quoId'];
                                                    $formatted_date = (new DateTime($row['updated_at']))->format('d M Y, H:i');
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row['quo_code']); ?></strong><br>
                                                        <small class="text-muted"><?php echo $formatted_date; ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="previewQuotation.php?quonum=<?php echo $row['quo_num']; ?>&id=<?php echo $quoId; ?>" class="btn btn-primary btn-icon btn-round action-button" data-bs-toggle="tooltip" title="Preview Quotation" target="_blank">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn <?php echo !empty($row['no_so']) ? 'btn-success' : 'btn-warning'; ?> btn-icon btn-round action-button" data-bs-toggle="modal" data-bs-target="#modalSO_<?php echo $quoId; ?>" data-bs-toggle="tooltip" title="Input SO">
                                                            <i class="fa <?php echo !empty($row['no_so']) ? 'fa-check' : 'fa-pen'; ?>"></i>
                                                        </button>
                                                    </td>
                                                    <td class="text-center">
                                                         <button class="btn <?php echo !empty($row['no_sj']) ? 'btn-success' : 'btn-warning'; ?> btn-icon btn-round action-button" data-bs-toggle="modal" data-bs-target="#modalSJ_<?php echo $quoId; ?>" data-bs-toggle="tooltip" title="Input SJ">
                                                            <i class="fa <?php echo !empty($row['no_sj']) ? 'fa-check' : 'fa-pen'; ?>"></i>
                                                        </button>
                                                    </td>
                                                    <td class="text-center">
                                                         <button class="btn <?php echo !empty($row['bast']) ? 'btn-success' : 'btn-warning'; ?> btn-icon btn-round action-button" data-bs-toggle="modal" data-bs-target="#modalBAST_<?php echo $quoId; ?>" data-bs-toggle="tooltip" title="Input BAST">
                                                            <i class="fa <?php echo !empty($row['bast']) ? 'fa-check' : 'fa-pen'; ?>"></i>
                                                        </button>
                                                    </td>
                                                     <td class="text-center">
                                                         <button class="btn <?php echo !empty($row['invoice']) ? 'btn-success' : 'btn-warning'; ?> btn-icon btn-round action-button" data-bs-toggle="modal" data-bs-target="#modalInv_<?php echo $quoId; ?>" data-bs-toggle="tooltip" title="Input Invoice">
                                                            <i class="fa <?php echo !empty($row['invoice']) ? 'fa-check' : 'fa-pen'; ?>"></i>
                                                        </button>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($row['isiValid'] == NULL): ?>
                                                            <div class="btn-group" role="group">
                                                                <form action="process_valid.php" method="POST" style="display:inline;">
                                                                    <input type="hidden" name="quo_id" value="<?php echo $quoId; ?>">
                                                                    <input type="hidden" name="valid_status" value="diterima">
                                                                    <button type="submit" name="update_valid" class="btn btn-success btn-sm">Terima</button>
                                                                </form>
                                                                <form action="process_valid.php" method="POST" style="display:inline; margin-left: 5px;">
                                                                    <input type="hidden" name="quo_id" value="<?php echo $quoId; ?>">
                                                                    <input type="hidden" name="valid_status" value="ditolak">
                                                                    <button type="submit" name="update_valid" class="btn btn-danger btn-sm">Tolak</button>
                                                                </form>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge <?php echo $row['isiValid'] == 'diterima' ? 'bg-success' : 'bg-danger'; ?> status-badge"><?php echo htmlspecialchars($row['isiValid']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                         <?php if (!empty($row['kegiatan_kode'])): ?>
                                                            <a class="btn btn-info btn-sm" href="view-request.php?id=<?php echo $row['kegiatan_kode']; ?>">
                                                                <i class='fa fa-eye'></i> View
                                                            </a>
                                                         <?php else: ?>
                                                            <a class="btn btn-secondary btn-sm" href="request.php?id=<?php echo $quoId; ?>">
                                                                <i class='fa fa-plus'></i> Request
                                                            </a>
                                                         <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include "nav/footer.php"; ?>
        </div>
    </div>

    <?php foreach ($quotations as $row) : ?>
    <?php $quoId = $row['quoId']; ?>
    <div class='modal fade' id='modalSO_<?php echo $quoId; ?>' tabindex='-1' aria-labelledby='modalSOLabel_<?php echo $quoId; ?>' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <form action='isi-progress.php' method='POST'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='modalSOLabel_<?php echo $quoId; ?>'>Input Data Sales Order (SO)</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <input type='hidden' name='quo_id' value='<?php echo $quoId; ?>'>
                        <input type='hidden' value='so' name='type'>
                        <div class='mb-3'>
                            <label for='no_so' class='form-label'>Nomor SO</label>
                            <input type='text' class='form-control' name='no_so' value='<?php echo htmlspecialchars($row['no_so']); ?>' required>
                        </div>
                        <div class='mb-3'>
                            <label for='tanggal_perencanaan' class='form-label'>Tanggal Perencanaan Pengerjaan</label>
                            <input type='date' class='form-control' name='tanggal_perencanaan' value='<?php echo htmlspecialchars($row['tanggal_rencana_pengerjaan']); ?>' required>
                        </div>
                        <div class='mb-3'>
                            <label for='alamat_instalasi' class='form-label'>Alamat Instalasi</label>
                            <textarea class='form-control' name='alamat_instalasi' required><?php echo htmlspecialchars($row['alamat_instalasi']); ?></textarea>
                        </div>
                        <div class='mb-3'>
                            <label for='contact_person' class='form-label'>Contact Person</label>
                            <input type='text' class='form-control' name='contact_person' value='<?php echo htmlspecialchars($row['contact_person']); ?>' required>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                        <button type='submit' class='btn btn-primary'>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class='modal fade' id='modalSJ_<?php echo $quoId; ?>' tabindex='-1' aria-labelledby='modalSJLabel_<?php echo $quoId; ?>' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <form action='isi-progress.php' method='POST'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='modalSJLabel_<?php echo $quoId; ?>'>Input Data Surat Jalan (SJ)</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <input type='hidden' name='quo_id' value='<?php echo $quoId; ?>'>
                        <input type='hidden' value='sj' name='type'>
                        <div class='mb-3'><label class='form-label'>Nomor SJ</label><input type='text' class='form-control' name='no_sj' value='<?php echo htmlspecialchars($row['no_sj']); ?>' required></div>
                        <div class='mb-3'><label class='form-label'>Tanggal SJ</label><input type='date' class='form-control' name='tanggal_sj' value='<?php echo htmlspecialchars($row['tgl_sj']); ?>' required></div>
                    </div>
                    <div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-primary'>Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class='modal fade' id='modalBAST_<?php echo $quoId; ?>' tabindex='-1' aria-labelledby='modalBASTLabel_<?php echo $quoId; ?>' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <form action='isi-progress.php' method='POST'>
                    <div class='modal-header'><h5 class='modal-title' id='modalBASTLabel_<?php echo $quoId; ?>'>Input Data BAST</h5><button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button></div>
                    <div class='modal-body'>
                        <input type='hidden' name='quo_id' value='<?php echo $quoId; ?>'><input type='hidden' value='bast' name='type'>
                        <div class='mb-3'><label class='form-label'>Nomor BAST</label><input type='text' class='form-control' name='no_bast' value='<?php echo htmlspecialchars($row['bast']); ?>' required></div>
                        <div class='mb-3'><label class='form-label'>Tanggal BAST</label><input type='date' class='form-control' name='tanggal_bast' value='<?php echo htmlspecialchars($row['tgl_bast']); ?>' required></div>
                        <div class='mb-3'><label class='form-label'>Keterangan</label><textarea class='form-control' name='keterangan' required><?php echo htmlspecialchars($row['keterangan']); ?></textarea></div>
                    </div>
                    <div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-primary'>Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class='modal fade' id='modalInv_<?php echo $quoId; ?>' tabindex='-1' aria-labelledby='modalInvoiceLabel_<?php echo $quoId; ?>' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <form action='isi-progress.php' method='POST'>
                    <div class='modal-header'><h5 class='modal-title' id='modalInvoiceLabel_<?php echo $quoId; ?>'>Input Data Invoice</h5><button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button></div>
                    <div class='modal-body'>
                        <input type='hidden' name='quo_id' value='<?php echo $quoId; ?>'><input type='hidden' value='invoice' name='type'>
                        <div class='mb-3'><label class='form-label'>Nomor Invoice</label><input type='text' class='form-control' name='no_invoice' value='<?php echo htmlspecialchars($row['invoice']); ?>' required></div>
                        <div class='mb-3'><label class='form-label'>Tanggal Invoice</label><input type='date' class='form-control' name='tanggal_invoice' value='<?php echo htmlspecialchars($row['tgl_inv']); ?>' required></div>
                    </div>
                    <div class='modal-footer'><button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button><button type='submit' class='btn btn-primary'>Save</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php include 'js/core-scripts.php'; ?>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable. Fitur sorting, search, dan pagination otomatis aktif.
            $('#quotationTable').DataTable({
                "pageLength": 10,
            });

            // Inisialisasi Tooltip dari Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
</body>
</html>