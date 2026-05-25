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
    <?php
    // --- LOGIKA KONTROL MODAL ---
    
    // 1. Tentukan tanggal kedaluwarsa untuk pengumuman ini
    $announcement_expiry_date = new DateTime('2025-08-01');
    $today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    
    // 2. Cek apakah hari ini masih dalam periode pengumuman
    $show_announcement_period = ($today < $announcement_expiry_date);
    
    // 3. Cek apakah cookie "sudah lihat modal hari ini" BELUM ada
    $show_modal_today = !isset($_COOKIE['quotation_update_modal_shown']);
    
    // Hanya tampilkan modal jika kedua kondisi terpenuhi
    if ($show_announcement_period && $show_modal_today):
    ?>
    
<div class="modal fade" id="updateAnnouncementModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-body text-center p-4">
        
        <i class="bi bi-megaphone-fill text-primary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
        
        <h4 class="modal-title fw-bold mb-3" id="updateModalLabel">Pembaruan Penting Sistem Quotation</h4>
        
        <p class="text-muted">
          Selamat datang di sistem penawaran versi terbaru! Untuk meningkatkan performa dan database sistem, kami telah melakukan pembaruan signifikan pada struktur aplikasi.
        </p>

        <div class="alert alert-info text-start mt-4">
            <h6 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Harap Diperhatikan:</h6>
            <ul class="mb-0 ps-3">
                <li>Semua penawaran **<b>Baru</b>** kini <b>WAJIB</b> dibuat menggunakan sistem ini <a href="https://quo.grav-tech.com/quo/dashboard.php" target="_blank">Website Quotation Versi 2.0</a>.</li>
                <li>Riwayat penawaran **<b>sebelum tanggal 14 Juli 2025</b>** masih dapat diakses pada <a href="https://quo.grav-tech.com/listQuotation.php" target="_blank">Website Quotation Versi 1.0</a>.</li>
            </ul>
        </div>
        
        <p class="small text-muted mt-3">
            <b>NOTE : </b>Perubahan struktur database yang besar membuat pemindahan data lama tidak memungkinkan untuk saat ini. Terima kasih atas pengertian Anda.
        </p>

        <button type="button" class="btn btn-primary mt-3 pt-1" data-bs-dismiss="modal">Saya Mengerti</button>

      </div>
    </div>
  </div>
</div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil elemen modal dari HTML
        var announcementModalElement = document.getElementById('updateAnnouncementModal');
        if (announcementModalElement) {
            var announcementModal = new bootstrap.Modal(announcementModalElement);
            
            // Tampilkan modal secara otomatis
            announcementModal.show();
            
            // Saat modal ditutup, set cookie agar tidak muncul lagi hari ini
            announcementModalElement.addEventListener('hidden.bs.modal', function () {
                // Buat tanggal kedaluwarsa cookie di akhir hari ini
                const expiryDate = new Date();
                expiryDate.setHours(23, 59, 59, 999);
                
                // Set cookie yang berlaku untuk seluruh website
                document.cookie = "quotation_update_modal_shown=true; expires=" + expiryDate.toUTCString() + "; path=/";
            });
        }
    });
    </script>
    
    <?php
    endif;
    // --- AKHIR DARI BLOK KODE MODAL ---
    ?>
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
                    $condition = ""; 
                    
                    if ($user_role == 'admin') {
                        $condition = " AND q.users_id = $user_id";
                    }
                    
                    $query = "
                        SELECT 
                            c.name AS customer_name,
                            q.id AS quoId,
                            q.quo_code,
                            q.status,
                            q.updated_at,
                            q.users_id,
                            SUM(qd.qty * qd.price) - SUM(qd.disc_item) AS total_amount,
                            SUM(qd.qty * qd.price) AS total_sub_amount,
                            SUM(qd.disc_item) AS total_discount,
                            q.disc_all,
                            q.disc_type,
                            q.from,
                            q.quo_num,
                            u.alias
                        FROM quo_detail qd
                        JOIN quo q ON qd.quo_id = q.id
                        JOIN customer c ON q.customer_id = c.id
                        JOIN users u ON q.users_id = u.id
                        WHERE q.deleted_at IS NULL 
                          AND qd.deleted_at IS NULL
                          AND q.id IN (
                              SELECT MAX(id)
                              FROM quo
                              WHERE deleted_at IS NULL
                              GROUP BY quo_code
                          )
                          $condition
                        GROUP BY q.quo_num, c.name, q.quo_code, q.status, q.updated_at, q.disc_all, q.disc_type, q.from, q.id
                        ORDER BY q.updated_at DESC
                    ";
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
                                                    <th>Quotation Code</th>
                                                    <th>Karyawan</th>
                                                    <th>Dari</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Action</th>
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
                                                    $userId = $row['users_id'];
                                                    $alias = $row['alias'];
                                                    $from = isset($row['from']) && $row['from'] !== null ? $row['from'] : '-';

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
                                                    $formatted_date = $date->format('M, d Y');
                                                    $total_discount = $row['total_discount'] ?? 0;
                                                    $total_sub_amount = $row['total_sub_amount'] ?? 0;
                                                    $total_amount = $row['total_amount'] ?? 0;
                                                    $from = $row['from'] ?? null;
                                                    $disc_all = $row['disc_all'] ?? 0;
                                                    $disc_type = $row['disc_type'] ?? null;
                                                    $quo_num = $row['quo_num'];

                                                    // Calculate total_non_ppn or total_ppn
                                                    if ($disc_type === 'p') {
                                                        // Percentage discount
                                                        $total_non_ppn = $total_amount - ($total_amount * ($disc_all / 100));
                                                        $disc_all = $total_amount * $disc_all / 100;
                                                    } elseif ($disc_type === 'n') {
                                                        // Nominal discount
                                                        $total_non_ppn = $total_amount - $disc_all;
                                                    } else {
                                                        // No discount
                                                        $total_non_ppn = $total_amount;
                                                    }

                                                    // Calculate PPN (only if from = 'CV')
                                                    $ppn = 0;
                                                    if ($from === 'CV') {
                                                        $ppn = $total_amount * 0.11;
                                                    }

                                                    $total_ppn = $total_non_ppn + $ppn;

                                                    echo "<tr>
                                                        <td>{$customer_name}</td>
                                                        <td class='quo-code' style='color:blue;' data-quo-code='{$quo_code}' data-bs-toggle='modal' data-bs-target='#quoModal'>{$quo_code}</td>
                                                        <td>{$alias}</td>
                                                        <td>{$from}</td>
                                                        <td>{$status}</td>
                                                        <td>{$formatted_date}</td>
                                                        <td data-total-ppn='{$total_ppn}'>Rp " . number_format($total_ppn, 0, ',', ',') . "</td>
                                                        <td>
                                                            <div class='form-button-action'>
                                                                <a href='#' 
                                                                   class='btn btn-secondary me-2 btn-sm print-btn' 
                                                                   data-bs-toggle='modal' 
                                                                   data-bs-target='#printModal' 
                                                                   data-quo-num='{$quo_num}' 
                                                                   data-quo-id='{$quoId}'
                                                                   data-bs-toggle='tooltip' 
                                                                   title='Preview Quotation'>
                                                                    <i class='fa fa-print'></i>
                                                                </a>
                                                                <a href='process_edit_quotation.php?quonum={$quo_num}&id={$quoId}' 
                                                                class='btn btn-primary btn-sm' 
                                                                data-bs-toggle='tooltip' 
                                                                title='Edit Quotation'>
                                                                    <i class='fa fa-edit'></i>
                                                                </a>
                                                                <a href='deleteQuotation.php?quonum={$quo_num}&id={$quoId}' 
                                                                class='btn btn-sm btn-danger ms-2' 
                                                                data-bs-toggle='tooltip' 
                                                                title='Remove' 
                                                                onclick='return confirm(\"Apakah Anda yakin ingin menghapus quotation ini?\");'>
                                                                    <i class='fa fa-times'></i>
                                                                </a>
                                                            </div>
                                                        </td>
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
                    
<!-- Modal -->
            <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="printModalLabel">Print Options</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="printForm">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkPicture" name="pict" value="Y">
                                    <label class="form-check-label" for="checkPicture">
                                        Include Picture
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkLink" name="link" value="Y">
                                    <label class="form-check-label" for="checkLink">
                                        Include Link
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="submitPrint">Print</button>
                        </div>
                    </div>
                </div>
            </div>
            
            
                </div>
            </div>
            
            
                            <div class="modal fade" id="quoModal" tabindex="-1" aria-labelledby="quoModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="quoModalLabel">Riwayat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <th>Quo Code</th>
                                                        <th>Alias</th>
                                                        <th>Dari</th>
                                                        <th>Status</th>
                                                        <th>Updated At</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modalQuoDetails">
                                                    <!-- Data akan dimuat di sini -->
                                                </tbody>
                                            </table>
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
    document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.quo-code').forEach(td => {
        td.addEventListener('click', function () {
            const quoCode = this.getAttribute('data-quo-code');

            // AJAX Request
            fetch(`getQuoDetails.php?quo_code=${quoCode}`)
                .then(response => response.json())
                .then(data => {
                    const modalBody = document.getElementById('modalQuoDetails');
                    modalBody.innerHTML = '';

                    data.forEach(row => {
                        const date = new Date(row.created_at);
                        const options = { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                        const formattedDate = date.toLocaleString('en-US', options).replace(',', '');

                        modalBody.innerHTML += `
                            <tr>
                                <td>${row.customer_name}</td>
                                <td>${row.quo_code}</td>
                                <td>${row.alias}</td>
                                <td>${row.from}</td>
                                <td>${row.status}</td>
                                <td>${formattedDate}</td>
                                <td>
                                    <div class='form-button-action'>
                                    <a href="previewQuotation.php?quonum=${row.quo_num}&id=${row.quoId}"
                                        target="_blank"
                                        class="btn btn-secondary me-2 btn-sm" 
                                        data-bs-toggle="tooltip" 
                                        title="Preview Quotation">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <a href="process_edit_quotation.php?quonum=${row.quo_num}&id=${row.quoId}" 
                                        class="btn btn-primary btn-sm" 
                                        data-bs-toggle="tooltip" 
                                        title="Edit Quotation">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="deleteQuotation.php?quonum=${row.quo_num}&id=${row.quoId}" 
                                        class="btn btn-sm btn-danger ms-2" 
                                        data-bs-toggle="tooltip" 
                                        title="Remove" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus quotation ini?');">
                                        <i class="fa fa-times"></i>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => console.error('Error fetching quo details:', error));
        });
    });
});

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
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    var printButtons = document.querySelectorAll('.print-btn');

    printButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var quoNum = button.getAttribute('data-quo-num');
            var quoId = button.getAttribute('data-quo-id');

            document.getElementById('submitPrint').setAttribute('data-quo-num', quoNum);
            document.getElementById('submitPrint').setAttribute('data-quo-id', quoId);
        });
    });

    document.getElementById('submitPrint').addEventListener('click', function() {
        var quoNum = this.getAttribute('data-quo-num');
        var quoId = this.getAttribute('data-quo-id');
        var pict = document.getElementById('checkPicture').checked ? 'Y' : 'N';
        var link = document.getElementById('checkLink').checked ? 'Y' : 'N';

        var url = `previewQuotation.php?quonum=${quoNum}&id=${quoId}&pict=${pict}&link=${link}`;
        window.open(url, '_blank'); // Buka URL di tab baru
    });
});
</script>
</body>

</html>