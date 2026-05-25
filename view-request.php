<?php
// Koneksi ke database (gantilah dengan informasi koneksi sesuai dengan database Anda)

$servername = "localhost";
$username = "u836263092_rootJadwal";
$password = "Eddie@1819";
$database = "u836263092_jadwal";

// $servername = "localhost";
// $username = "root";
// $password = "";
// $database = "teknisi";

$conn = mysqli_connect($servername, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
date_default_timezone_set('Asia/Jakarta');

$kegiatan_kode = isset($_GET['id']) ? $_GET['id'] : null;

// Fungsi untuk menampilkan tombol download jika gambar tidak null
function displayDownloadButton($image, $baseUrl) {
    if (!empty($image)) {
        $imageUrl = $baseUrl . $image;
        return '<a href="' . $imageUrl . '" download class="btn btn-xs btn-outline-primary me-2" target="_blank"><i class="fas fa-download"></i></a>';
    }
    return '';
}
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
                                <a href="#">Request</a>
                            </li>
                        </ul>
                    </div>

                    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Request</h4>
            </div>
            <?php
            $current_date = date("Y-m-d"); // Today's date
            $tomorrow_date = date("Y-m-d", strtotime("+1 day")); // Tomorrow's date
            $current_time = date("H:i:s"); // Current time

            // Query untuk mengambil data kegiatan dan pelaksanaan kegiatan
            $sql = "SELECT pk.*, k.kode, k.jadwal, c.nama AS nama_customer, c.telp AS cust_nomor, c.alamat,
                           t.nama
                    FROM pelaksanaan_kegiatan pk
                    LEFT JOIN kegiatan k ON k.kode = pk.kode
                    LEFT JOIN customer c ON k.customer_id = c.id
                    LEFT JOIN teknisi t ON pk.teknisi_id = t.id
                    WHERE pk.kode = '$kegiatan_kode'
                    AND k.deleted_at IS NULL
                    GROUP BY pk.teknisi_id, pk.waktu_mulai, pk.waktu_selesai
                    ORDER BY pk.waktu_mulai ASC";

            $result = mysqli_query($conn, $sql);
            ?>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="multi-filter-select" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Teknisi</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>Media</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $namaTeknisi = shortenTechnicianName($row['nama']);
                                    $tanggalMulai = !empty($row['waktu_mulai']) ? date("d/m/Y H:i", strtotime($row['waktu_mulai'])) : '-';
                                    $tanggalSelesai = !empty($row['waktu_selesai']) ? date("d/m/Y H:i", strtotime($row['waktu_selesai'])) : '-';
                                    $status = $row['status'];
                                    $permasalahan = $row['permasalahan'];
                                    $solusi = $row['solusi'];
                                    $keterangan = $row['keterangan'];
                                    $image1 = $row['image_1'];
                                    $image2 = $row['image_2'];
                                    $image3 = $row['image_3'];

                                    // Menentukan teks status
                                    switch ($status) {
                                        case 'selesai':
                                            $statusText = "Selesai";
                                            $statusClass = "bg-success";
                                            break;
                                        case 'dikerjakan':
                                            $statusText = "Dikerjakan";
                                            $statusClass = "bg-info";
                                            break;
                                        case 'dijadwalkan':
                                            $statusText = "Dijadwalkan";
                                            $statusClass = "bg-secondary";
                                            break;
                                        default:
                                            $statusText = "Dijadwalkan";
                                            $statusClass = "bg-secondary";
                                    }
                            ?>
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size:13px;"><?php echo $namaTeknisi; ?></h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size:13px;"><?php echo $tanggalMulai; ?></h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size:13px;"><?php echo $tanggalSelesai; ?></h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size:13px;text-transform:capitalize;"><?php echo $row['status']; ?></h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 text-dark font-weight-bold" style="font-size:13px;">
                                                Keterangan : <?php echo !empty($keterangan) ? $keterangan : '-'; ?>
                                            </h6>
                                            <h6 class="mb-0 mt-0 py-0 text-dark font-weight-bold" style="font-size:13px;">
                                                Permasalahan : <?php echo !empty($permasalahan) ? $permasalahan : '-'; ?>
                                            </h6>
                                            <h6 class="mb-0 mt-0 py-0 text-dark font-weight-bold" style="font-size:13px;">
                                                Solusi : <?php echo !empty($solusi) ? $solusi : '-'; ?>
                                            </h6>
                                        </td>
                                        </td>
                                        <!-- Di dalam perulangan -->
                                        <td>
                                            <div class="d-flex">
                                                <?php
                                                $baseUrl = "https://grav-tech.com/jadwal-3/api/storage/app/image/";
                                                
                                                // Tampilkan tombol untuk setiap gambar yang tidak null
                                                if (isset($row['image_1']) && !empty($row['image_1'])) {
                                                    echo displayDownloadButton($row['image_1'], $baseUrl);
                                                }
                                                if (isset($row['image_2']) && !empty($row['image_2'])) {
                                                    echo displayDownloadButton($row['image_2'], $baseUrl);
                                                }
                                                if (isset($row['image_3']) && !empty($row['image_3'])) {
                                                    echo displayDownloadButton($row['image_3'], $baseUrl);
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Tidak ada data pelaksanaan kegiatan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function shortenTechnicianName($fullName) {
    $muhammadVariants = [
        'Muhammad', 'Mohammed', 'Mohammad', 'Muhammed', 'Mohamed', 'Mohamad', 'Muhamad', 'Muhamed', 'Mohamud', 'Mohummad', 'Mohummed'
    ];

    $words = explode(" ", $fullName);
    if (in_array($words[0], $muhammadVariants)) {
        $words[0] = "M.";
    }

    $shortenedName = implode(" ", $words);
    if (strlen($shortenedName) > 15) {
        foreach ($words as $index => $word) {
            if ($index > 1 && $index === count($words) - 1) {
                $words[$index] = strtoupper($word[0]) . '.';
            }
        }
        $shortenedName = implode(" ", $words);
    }

    return $shortenedName;
}
?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <?php 
            
                    include "floating-menu.php";
                    // include 'footer.php';
            ?>
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
                pageLength: 15,
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