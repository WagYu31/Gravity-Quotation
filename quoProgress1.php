<?php
include "conn.php";

$quonum = isset($_GET['quonum']) ? $_GET['quonum'] : null;
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
                                <a href="#">Quotations</a>
                            </li>
                        </ul>
                    </div>

                    <?php
                    $query = "
                        SELECT 
                          c.name AS customer_name,
                          q.quo_code,
                          q.status,
                          MAX(q.id) AS quoId,
                          q.updated_at,
                          SUM(qd.qty * qd.price) - SUM(qd.disc_item) AS total_amount,
                          SUM(qd.qty * qd.price) AS total_sub_amount,
                          SUM(qd.disc_item) AS total_discount,
                          q.disc_all,
                          q.disc_type,
                          q.from,
                          q.quo_num,
                          q.kegiatan_kode
                        FROM quo_detail qd
                        JOIN quo q ON qd.quo_id = q.id
                        JOIN customer c ON q.customer_id = c.id
                        WHERE q.deleted_at IS NULL AND qd.deleted_at IS NULL AND q.status = 'saved'
                        GROUP BY q.quo_num, c.name, q.quo_code, q.status, q.disc_all, q.disc_type, q.from
                        ORDER BY q.updated_at DESC";
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quotations</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            id="multi-filter-select"
                                            class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Quotation</th>
                                                    <th>Quo</th>
                                                    <th>SO</th>
                                                    <th>SJ</th>
                                                    <th>BAST</th>
                                                    <th>Invoice</th>
                                                    <th>Valid</th>
                                                    <th>Request</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Execute the query
                                                $result = $conn->query($query);

                                                while ($row = $result->fetch_assoc()) {
                                                    $customer_name = $row['customer_name'];
                                                    $quo_code = $row['quo_code'];
                                                    $quoId = $row['quoId'];
                                                    $status = $row['status'];

                                                    if ($status == 'saved') {
                                                        $status = 'Saved';
                                                    } elseif ($status == 'temp') {
                                                        $status = 'Unsaved';
                                                    } elseif ($status == 'edit') {
                                                        $status = 'Edited';
                                                    }

                                                    $updated_at = $row['updated_at'];

                                                    // Convert the timestamp to a DateTime object
                                                    $date = new DateTime($updated_at);

                                                    // Format the date as M, d Y - H:i:s
                                                    $formatted_date = $date->format('M, d Y - H:i:s');
                                                    $from = $row['from'] ?? null;
                                                    $quo_num = $row['quo_num'];
                                                    
                                                    $getquonum = "
                                                        SELECT * 
                                                        FROM quo 
                                                        WHERE quo_num = '$quo_num' 
                                                            AND deleted_at IS NULL 
                                                            AND status = 'saved' 
                                                        ORDER BY id DESC 
                                                        LIMIT 1";
                                                    
                                                    $resquonum = $conn->query($getquonum);
                                                    $rowQN = $resquonum->fetch_assoc();
                                                    $r_qId = $rowQN['id'];
                                                    $kegiatan_kode = $rowQN['kegiatan_kode'];
                                                    
                                                    echo "<tr>
                                                        <td style='font-size:12px;'>{$customer_name}</td>
                                                        <td>{$quo_code}<br>
                                                            <span  style='font-size:12px;'>{$formatted_date}</span>
                                                        </td>
                                                        <td>
                                                            <div class='form-button-action'>
                                                                <a href='previewQuotation.php?quonum={$quo_num}&id={$r_qId}'
                                                                   class='btn btn-icon btn-round btn-primary p-2'
                                                                   data-bs-toggle='tooltip'
                                                                   title='Preview Quotation' 
                                                                   target='_blank'>
                                                                    <i class='fa fa-eye text-sm' style='font-size:12px;'></i>
                                                                </a>
                                                            </div>
                                                        </td>";

                                                       // Query untuk mengecek data di tabel progress
                                                        $queryProgress = "SELECT * FROM progress WHERE quo_id = '{$quoId}'";
                                                        $resultProgress = $conn->query($queryProgress);
                                                        
                                                        // Inisialisasi nilai default
                                                        $no_so = '';
                                                        $tanggal_perencanaan = '';
                                                        $alamat_instalasi = '';
                                                        $contact_person = '';
                                                        $no_sj = '';
                                                        $tgl_sj = '';
                                                        $bast = '';
                                                        $tgl_bast = '';
                                                        $keterangan = '';
                                                        $invoice = '';
                                                        $tgl_inv = '';
                                                        $isiValid = '';
                                                        
                                                        // Jika data ditemukan, ambil nilai atribut
                                                        if ($resultProgress && $resultProgress->num_rows > 0) {
                                                            $progressData = $resultProgress->fetch_assoc();
                                                            $no_so = $progressData['so'];
                                                            $tanggal_perencanaan = $progressData['tanggal_rencana_pengerjaan'];
                                                            $alamat_instalasi = $progressData['alamat_instalasi'];
                                                            $contact_person = $progressData['contact_person'];
                                                            $no_sj = $progressData['sj'];
                                                            $tgl_sj = $progressData['tanggal_sj'];
                                                            $bast = $progressData['bast'];
                                                            $tgl_bast = $progressData['tanggal_bast'];
                                                            $keterangan = $progressData['keterangan'];
                                                            $invoice = $progressData['invoice'];
                                                            $tgl_inv = $progressData['tanggal_inv'];
                                                            $isiValid = $progressData['valid'];
                                                        }
                                                        
                                                        echo "
                                                            <td class='text-center'>
                                                                <div class='form-button-action'>
                                                                    <button class='btn btn-icon btn-round " . (!empty($no_so) ? "btn-success" : "btn-warning") . "' data-bs-toggle='modal' data-bs-target='#modalSO_{$quoId}'>
                                                                        <i class='fa " . (!empty($no_so) ? "fa-check" : "fa-pen") . "' style='font-size:12px;'></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class='text-center'>
                                                                <div class='form-button-action'>
                                                                    <button class='btn btn-icon btn-round " . (!empty($no_sj) ? "btn-success" : "btn-warning") . "' data-bs-toggle='modal' data-bs-target='#modalSJ_{$quoId}'>
                                                                        <i class='fa " . (!empty($no_sj) ? "fa-check" : "fa-pen") . "' style='font-size:12px;'></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class='text-center'>
                                                                <div class='form-button-action'>
                                                                    <button class='btn btn-icon btn-round " . (!empty($bast) ? "btn-success" : "btn-warning") . "' data-bs-toggle='modal' data-bs-target='#modalBAST_{$quoId}'>
                                                                        <i class='fa " . (!empty($bast) ? "fa-check" : "fa-pen") . "' style='font-size:12px;'></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class='text-center'>
                                                                <div class='form-button-action'>
                                                                    <button class='btn btn-icon btn-round " . (!empty($invoice) ? "btn-success" : "btn-warning") . "' data-bs-toggle='modal' data-bs-target='#modalInv_{$quoId}'>
                                                                        <i class='fa " . (!empty($invoice) ? "fa-check" : "fa-pen") . "' style='font-size:12px;'></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        ";
                                                        
                                                        // Tampilkan modal dengan nilai input yang sesuai
                                                        echo "
                                                        <div class='modal fade' id='modalSO_{$quoId}' tabindex='-1' aria-labelledby='modalSOLabel' aria-hidden='true'>
                                                            <div class='modal-dialog'>
                                                                <div class='modal-content'>
                                                                    <form action='isi-progress.php' method='POST'>
                                                                        <div class='modal-header'>
                                                                            <h5 class='modal-title' id='modalSOLabel'>Isi SO {$quoId}</h5>
                                                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                        </div>
                                                                        <div class='modal-body'>
                                                                            <input type='hidden' id='quoIdInputSO' name='quo_id' value='{$quoId}'>
                                                                            <input type='hidden' value='so' name='type'>
                                                                            <div class='mb-3'>
                                                                                <label for='no_so' class='form-label'>Nomor SO</label>
                                                                                <input type='text' class='form-control' name='no_so' value='{$no_so}' required>
                                                                            </div>
                                                                            <div class='mb-3'>
                                                                                <label for='tanggal_perencanaan' class='form-label'>Tanggal Perencanaan Pengerjaan</label>
                                                                                <input type='date' class='form-control' name='tanggal_perencanaan' value='{$tanggal_perencanaan}' required>
                                                                            </div>
                                                                            <div class='mb-3'>
                                                                                <label for='alamat_instalasi' class='form-label'>Alamat Instalasi</label>
                                                                                <textarea class='form-control' name='alamat_instalasi' required>{$alamat_instalasi}</textarea>
                                                                            </div>
                                                                            <div class='mb-3'>
                                                                                <label for='contact_person' class='form-label'>Contact Person</label>
                                                                                <input type='text' class='form-control' name='contact_person' value='{$contact_person}' required>
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

                                                            
                                                                <div class='modal fade' id='modalSJ_{$quoId}' tabindex='-1' aria-labelledby='modalSJLabel' aria-hidden='true'>
                                                                    <div class='modal-dialog'>
                                                                        <div class='modal-content'>
                                                                            <form action='isi-progress.php' method='POST'>
                                                                                <div class='modal-header'>
                                                                                    <h5 class='modal-title' id='modalSJLabel'>Isi SJ</h5>
                                                                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                                </div>
                                                                                <div class='modal-body'>
                                                                                    <input type='hidden' id='quoIdInputSJ' name='quo_id' value='{$quoId}'>
                                                                                    <input type='hidden' value='sj' name='type'>
                                                                                    <div class='mb-3'>
                                                                                        <label for='no_sj' class='form-label'>Nomor SJ</label>
                                                                                        <input type='text' class='form-control' name='no_sj' value='{$no_sj}' required>
                                                                                    </div>
                                                                                    <div class='mb-3'>
                                                                                        <label for='tanggal_sj' class='form-label'>Tanggal SJ</label>
                                                                                        <input type='date' class='form-control' name='tanggal_sj' value='{$tgl_sj}' required>
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
                                                                
                                                                <div class='modal fade' id='modalBAST_{$quoId}' tabindex='-1' aria-labelledby='modalBASTLabel' aria-hidden='true'>
                                                                    <div class='modal-dialog'>
                                                                        <div class='modal-content'>
                                                                            <form action='isi-progress.php' method='POST'>
                                                                                <div class='modal-header'>
                                                                                    <h5 class='modal-title' id='modalBASTLabel'>Isi BAST</h5>
                                                                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                                </div>
                                                                                <div class='modal-body'>
                                                                                    <input type='hidden' id='quoIdInputBAST' name='quo_id' value='{$quoId}'>
                                                                                    <input type='hidden' value='bast' name='type'>
                                                                                    <div class='mb-3'>
                                                                                        <label for='no_bast' class='form-label'>Nomor</label>
                                                                                        <input type='text' class='form-control' name='no_bast' value='{$bast}' required>
                                                                                    </div>
                                                                                    <div class='mb-3'>
                                                                                        <label for='tanggal_bast' class='form-label'>Tanggal</label>
                                                                                        <input type='date' class='form-control' name='tanggal_bast' value='{$tgl_bast}' required>
                                                                                    </div>
                                                                                    <div class='mb-3'>
                                                                                        <label for='keterangan' class='form-label'>Keterangan</label>
                                                                                        <textarea class='form-control' name='keterangan' required>{$keterangan}</textarea>
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
                                                                
                                                                <div class='modal fade' id='modalInv_{$quoId}' tabindex='-1' aria-labelledby='modalInvoiceLabel' aria-hidden='true'>
                                                                    <div class='modal-dialog'>
                                                                        <div class='modal-content'>
                                                                            <form action='isi-progress.php' method='POST'>
                                                                                <div class='modal-header'>
                                                                                    <h5 class='modal-title' id='modalInvoiceLabel'>Isi Invoice</h5>
                                                                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                                                </div>
                                                                                <div class='modal-body'>
                                                                                    <input type='hidden' id='quoIdInputInv' name='quo_id' value='{$quoId}'>
                                                                                    <input type='hidden' value='invoice' name='type'>
                                                                                    <div class='mb-3'>
                                                                                        <label for='no_invoice' class='form-label'>Nomor Invoice</label>
                                                                                        <input type='text' class='form-control' name='no_invoice' value='{$invoice}' required>
                                                                                    </div>
                                                                                    <div class='mb-3'>
                                                                                        <label for='tanggal_invoice' class='form-label'>Tanggal Invoice</label>
                                                                                        <input type='date' class='form-control' name='tanggal_invoice' value='{$tgl_inv}' required>
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
                                                                ";
                                                                
                                                        if ($isiValid == NULL) {
                                                            // Tombol Terima dan Tolak
                                                            echo "<td class='text-center'>
                                                                <div class='form-button-action'>
                                                                    <form action='process_valid.php' method='POST' style='display:inline;'>
                                                                        <input type='hidden' name='quo_id' value='{$quoId}'>
                                                                        <input type='hidden' name='valid_status' value='diterima'>
                                                                        <button type='submit' name='update_valid' class='btn btn-primary btn-sm'>Terima</button>
                                                                    </form>
                                                                    <form action='process_valid.php' method='POST' style='display:inline; margin-left: 5px;'>
                                                                        <input type='hidden' name='quo_id' value='{$quoId}'>
                                                                        <input type='hidden' name='valid_status' value='ditolak'>
                                                                        <button type='submit' name='update_valid' class='btn btn-danger btn-sm'>Tolak</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                            ";
                                                        }
                                                        else{
                                                            echo "<td class='text-center'><span class='badge badge-count' style='text-transform:uppercase;'>{$isiValid}</span></td>";
                                                        }
                                                    
                                                    echo "
                                                    <td class='text-center'>
                                                        <div class='form-button-action'>";
                                                    
                                                    // Cek apakah $kegiatan_kode tidak NULL atau kosong
                                                    if (!empty($kegiatan_kode)) {
                                                        echo "
                                                            <a class='btn btn-primary btn-sm' href='view-request.php?id={$kegiatan_kode}'>
                                                                <i class='fa fa-eye' style='font-size:10px; margin-right:3px;'></i>
                                                            </a>";
                                                    } else {
                                                        echo "
                                                            <a class='btn btn-secondary btn-sm' href='request.php?id={$quoId}'>
                                                                <i class='fa fa-pen' style='font-size:8px; margin-right:3px;'></i> Req
                                                            </a>";
                                                    }
                                                    
                                                    echo "
                                                        </div>
                                                    </td>";
                                                        
                                                    }


                                                    echo "</tr>";
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
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


        <!-- Custom template | don't include it in your project! -->

        <?php
            // include 'custom-temp.php'
        ; ?>
    </div>

    <?php include 'core-scripts.php'; ?>

    <script>
        $(document).ready(function() {
            $("#basic-datatables").DataTable({});

            $("#multi-filter-select").DataTable({
                pageLength: 10,
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function() {
                            var column = this;
                            var select = $(
                                    '<select class="form-select"><option value=""></option></select>'
                                )
                                .appendTo($(column.footer()).empty())
                                .on("change", function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                    column
                                        .search(val ? "^" + val + "$" : "", true, false)
                                        .draw();
                                });

                            column
                                .data()
                                .unique()
                                .sort()
                                .each(function(d, j) {
                                    select.append(
                                        '<option value="' + d + '">' + d + "</option>"
                                    );
                                });
                        });
                },
            });

            // Add Row
            $("#add-row").DataTable({
                pageLength: 10,
            });

            var action =
                '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

            $("#addRowButton").click(function() {
                $("#add-row")
                    .dataTable()
                    .fnAddData([
                        $("#addName").val(),
                        $("#addPosition").val(),
                        $("#addOffice").val(),
                        action,
                    ]);
                $("#addRowModal").modal("hide");
            });
        });
    </script>
</body>

</html>