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
                          q.id AS quoId,
                          q.quo_code,
                          q.status,
                          MAX(q.updated_at) AS updated_at,
                          SUM(qd.qty * qd.price) - SUM(qd.disc_item) AS total_amount,
                          SUM(qd.qty * qd.price) AS total_sub_amount,
                          SUM(qd.disc_item) AS total_discount,
                          q.disc_all,
                          q.disc_type,
                          q.from,
                          q.quo_num
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

                                                    echo "<tr>
                                                        <td style='font-size:12px;'>{$customer_name}</td>
                                                        <td>{$quo_code}<br>
                                                            <span  style='font-size:12px;'>{$formatted_date}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class='form-button-action'>
                                                                <a href='previewQuotation.php?quonum={$quo_num}&id={$quoId}'
                                                                class='btn btn-primary me-2 btn-sm'
                                                                data-bs-toggle='tooltip'
                                                                title='Preview Quotation'>
                                                                    <i class='fa fa-eye'></i>
                                                                </a>
                                                            </div>
                                                        </td>";

                                                    // Menampilkan tabel dengan tombol upload atau view
                                                    $getProgress = "SELECT * FROM progress WHERE quo_id = '{$quoId}'";
                                                    $resultProgress = $conn->query($getProgress);

                                                    if ($resultProgress && $rowProgress = $resultProgress->fetch_assoc()) {
                                                        $fields = ['so', 'sj', 'bast', 'invoice'];
                                                        $isiValid = $rowProgress['valid'];
                                                        foreach ($fields as $field) {
                                                            $disabled = '';
                                                            if ($field === 'sj' && empty($rowProgress['so'])) {
                                                                $disabled = 'disabled';
                                                            } elseif ($field === 'invoice' && (empty($rowProgress['so']) || empty($rowProgress['sj']))) {
                                                                $disabled = 'disabled';
                                                            }

                                                            echo "<td class='text-center'>
                                                                <div class='form-button-action'>";

                                                            if (empty($rowProgress[$field])) {
                                                                if ($field === 'so') {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showIsiModal('$field', '{$quoId}')\">
                                                                        <i class='fa fa-pen' style='font-size:9px; margin-right:3px;'></i> Isi
                                                                    </button>";
                                                                } else {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showUploadForm('$field', '{$quoId}')\">
                                                                        <i class='fa fa-upload'></i> Upload
                                                                    </button>";
                                                                }
                                                            } else {
                                                                if ($field === 'so') {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showIsiSoModal('$field', '{$quoId}')\">
                                                                        <i class='fa fa-pen' style='font-size:9px; margin-right:3px;'></i> Edit
                                                                    </button>";
                                                                } else {
                                                                    echo "<a href='uploads/progress/$field/{$rowProgress[$field]}' target='_blank' class='btn btn-primary btn-sm'>
                                                                            <i class='fa fa-eye'></i>
                                                                          </a>";
                                                                }
                                                            }

                                                            echo "</div>
                                                                </td>";
                                                        }

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
                                                        
                                                    } else {
                                                        $fields = ['so', 'sj', 'bast', 'invoice'];
                                                        foreach ($fields as $field) {
                                                            $disabled = '';
                                                            if ($field === 'sj' && empty($rowProgress['so'])) {
                                                                $disabled = 'disabled';
                                                            } elseif ($field === 'invoice' && (empty($rowProgress['so']) || empty($rowProgress['sj']))) {
                                                                $disabled = 'disabled';
                                                            }

                                                            echo "<td class='text-center'>
                                                                    <div class='form-button-action'>";

                                                            if (empty($rowProgress[$field])) {
                                                                if ($field === 'so') {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showIsiModal('$field', '{$quoId}')\">
                                                                        <i class='fa fa-pen' style='font-size:9px; margin-right:3px;'></i> Isi
                                                                    </button>";
                                                                } else {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showUploadForm('$field', '{$quoId}')\">
                                                                        <i class='fa fa-upload'></i> Upload
                                                                    </button>";
                                                                }
                                                            } else {
                                                                if ($field === 'so') {
                                                                    echo "<button class='btn btn-warning btn-sm' onclick=\"showIsiSoModal('$field', '{$quoId}')\">
                                                                        <i class='fa fa-pen' style='font-size:9px; margin-right:3px;'></i> Edit
                                                                    </button>";
                                                                } else {
                                                                    echo "<a href='uploads/progress/$field/{$rowProgress[$field]}' target='_blank' class='btn btn-primary btn-sm'>
                                                                            <i class='fa fa-eye'></i>
                                                                          </a>";
                                                                }
                                                            }

                                                            echo "</div>
                                                                </td>";
                                                                
                                                                ?>
                                                                <!-- Form untuk isi -->
                                                                <div class="modal fade" id="isiSoModal" tabindex="-1" role="dialog" aria-labelledby="isiSoModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="isiSoModalLabel">Isi SO</h5>
                                                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                                <div class="modal-body">
                                                                                    <!-- Input Nomor SO -->
                                                                                    <div class="form-group">
                                                                                        <label for="noso">Nomor SO</label>
                                                                                        <label for="noso"><?= htmlspecialchars($rowProgress['so'] ?? '') ?></label>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label for="tgl">Tanggal Rencana Pengerjaan</label>
                                                                                        <label for="noso"><?= htmlspecialchars($rowProgress['tanggal_rencana_pengerjaan'] ?? '') ?></label>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label for="noso">Alamat Instalasi</label>
                                                                                        <label for="noso"><?= htmlspecialchars($rowProgress['alamat_instalasi'] ?? '') ?></label>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label for="cp">Contact Person</label>
                                                                                        <label for="noso"><?= htmlspecialchars($rowProgress['contact_person'] ?? '') ?></label>
                                                                                    </div>
                                                                
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <?php
                                                        }

                                                        
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
                                                    }

                                                ?>
                                                    <!-- Form untuk upload -->
                                                    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="uploadModalLabel">Upload File</h5>
                                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="upload-progress.php" method="POST" enctype="multipart/form-data">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="quo_id" id="quoId">
                                                                        <input type="hidden" name="type" id="fileType">

                                                                        <div class="form-group">
                                                                            <label for="file">Choose File</label>
                                                                            <input type="file" class="form-control" name="file" id="file" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="upload_file" class="btn btn-primary">Upload</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Form untuk isi -->
                                                    <div class="modal fade" id="isiModal" tabindex="-1" role="dialog" aria-labelledby="isiModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="isiModalLabel">Isi SO</h5>
                                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="isi-progress.php" method="POST" enctype="multipart/form-data">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="quo_id" id="quoIdIsi">
                                                                        <input type="hidden" name="type" id="fileTypeIsi">

                                                                        <div class="form-group">
                                                                            <label for="noso">Nomor SO</label>
                                                                            <input type="text" class="form-control" name="noso" id="noso" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="tgl">Tanggal Rencana Pengerjaan</label>
                                                                            <input type="date" class="form-control" name="tgl" id="tgl" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="alamat">Alamat Instalasi</label>
                                                                            <input type="text" class="form-control" name="alamat" id="alamat" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="cp">Contact Person</label>
                                                                            <input type="text" class="form-control" name="cp" id="cp" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="upload_file" class="btn btn-primary">Upload</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <script>
                                                        function showUploadForm(type, quoId) {
                                                            // Set tipe file dan quo_id ke dalam form
                                                            document.getElementById('fileType').value = type;
                                                            document.getElementById('quoId').value = quoId;

                                                            // Tampilkan modal upload
                                                            $('#uploadModal').modal('show');
                                                        }
                                                        function showIsiModal(type, quoId) {
                                                            document.getElementById('fileTypeIsi').value = type;
                                                            document.getElementById('quoIdIsi').value = quoId;
                                                        
                                                            // Tampilkan modal isiModal
                                                            $('#isiModal').modal('show');
                                                        }
                                                        function showIsiSoModal() {
                                                            // Tampilkan modal isiModal
                                                            $('#isiSoModal').modal('show');
                                                        }
                                                    </script>
                                                <?php

                                                    echo "
                                                    </tr>";
                                                }

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


            <?php include 'footer.php'; ?>
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
                pageLength: 5,
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
                pageLength: 5,
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