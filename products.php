<?php

include 'conn.php';
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
                <!-- Navbar Header -->
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
                                <a href="#">Data Product</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Produk</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            id="basic-datatables"
                                            class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Gambar</th>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Harga</th>
                                                    <th>Spesifikasi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Gambar</th>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Harga</th>
                                                    <th>Spesifikasi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </tfoot>
                                            <?php
                                            // Koneksi database

                                            // Query untuk mendapatkan data dari tabel barang
                                            $sql = "SELECT * FROM barang WHERE deleted_at IS NULL";
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if (!empty($row['image'])): ?>
                                                                    <img src="uploads/products/<?php echo $row['image']; ?>" alt="<?php echo $row['code']; ?>" width="90">
                                                                <?php else: ?>
                                                                    No Image
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($row['code']); ?></td>
                                                            <td style="text-transform:capitalize;"><?php echo htmlspecialchars($row['kategori']); ?></td>
                                                            <td><?php echo "Rp" . number_format($row['price'], 0); ?></td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?php
                                                                    $desc = htmlspecialchars($row['desc']); // Escape untuk menghindari XSS
                                                                    $desc = nl2br($desc); // Konversi baris baru ke <br>

                                                                    // Potong teks jika lebih dari 300 karakter
                                                                    if (strlen($desc) > 200) {
                                                                        $desc = substr($desc, 0, 200) . '...';
                                                                    }

                                                                    echo $desc;
                                                                    ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <div class="form-button-action">
                                                                <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="delete-product.php?id=<?php echo $row['id']; ?>"
                                                                    class="btn btn-danger btn-sm ms-1"
                                                                    title="Delete"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6">Tidak ada data ditemukan.</td>
                                                    </tr>
                                                <?php endif; ?>
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