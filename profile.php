<?php

include 'conn.php';

// Ambil data user berdasarkan ID
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found!");
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
                <!-- Navbar Header -->
                <?php include 'navbar-header.php'; ?>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Update Profile</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="largeInput">Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Enter Name" value="<?php echo $user['name']; ?>" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="largeInput">Alias</label>
                                            <input type="text" name="alias" class="form-control" placeholder="Enter Alias" value="<?php echo $user['alias']; ?>" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="largeInput">Username</label>
                                            <input type="text" name="username" class="form-control" placeholder="Enter Username" value="<?php echo $user['username']; ?>" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="email2">Email Address</label>
                                            <input type="email" name="email" class="form-control" placeholder="Enter Email" value="<?php echo $user['email']; ?>" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="email2">Phone Number</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">+62</span>
                                                <input type="number" name="phone" class="form-control" placeholder="Phone Number" value="<?php echo $user['telp']; ?>" required />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" name="password" class="form-control" placeholder="Enter Password" />
                                            <small class="form-text text-muted">Leave blank if you don't want to change the password.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="photo_profile">Photo Profile</label>
                                            <input type="file" class="form-control" name="photo_profile" />
                                        </div>
                                        <div class="form-group">
                                            <label for="signature">Signature</label>
                                            <input type="file" class="form-control" name="signature" />
                                        </div>
                                    </div>
                                    <div class="card-action">
                                        <button type="submit" name="update_profile" class="btn btn-success">Update</button>
                                        <button type="reset" class="btn btn-danger">Cancel</button>
                                    </div>
                                </div>
                            </form>

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