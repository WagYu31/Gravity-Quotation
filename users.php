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
                                <a href="#">Users</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Users</h4>
                                </div>
                                <div class="card-body">
                                    <form class="mb-4" action="process_user_input.php" method="POST">
                                        <div class="row">
                                            <!-- Column 1 -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required />
                                                </div>
                                                <div class="form-group">
                                                    <label for="alias">Alias</label>
                                                    <input type="text" class="form-control" id="alias" name="alias" placeholder="Enter Alias" required />
                                                </div>
                                            </div>

                                            <!-- Column 2 -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="phone_number">Phone Number</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">+62</span>
                                                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email Address</label>
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" />
                                                    <small class="form-text text-muted">We'll never share your email with anyone else.</small>
                                                </div>
                                            </div>

                                            <!-- Column 3 -->
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label for="role">Role</label>
                                                    <select class="form-control" id="role" name="role" required>
                                                        <option value="" disabled selected>Select Role</option>
                                                        <option value="superadmin">Superadmin</option>
                                                        <option value="admin">Admin</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-success">Save</button>
                                                    <button type="reset" class="btn btn-danger">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="card-body">

                                    <div class="table-responsive">
                                        <table
                                            id="multi-filter-select"
                                            class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Alias</th>
                                                    <th>Role</th>
                                                    <th>Phone Number</th>
                                                    <th>Email</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Alias</th>
                                                    <th>Role</th>
                                                    <th>Phone Number</th>
                                                    <th>Email</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php
                                                // Query to fetch user data
                                                $query = "
                                                        SELECT 
                                                            id, 
                                                            name, 
                                                            alias, 
                                                            role, 
                                                            telp AS phone_number, 
                                                            email 
                                                        FROM users
                                                        WHERE deleted_at IS NULL
                                                        ORDER BY name DESC
                                                    ";

                                                // Execute the query
                                                $result = $conn->query($query);

                                                // Check if there are any results
                                                if ($result->num_rows > 0) {
                                                    // Output the data into the table
                                                    while ($row = $result->fetch_assoc()) {
                                                        $id = $row['id'];
                                                        $name = $row['name'];
                                                        $alias = $row['alias'];
                                                        $role = $row['role'];
                                                        $phone_number = $row['phone_number'];
                                                        $email = $row['email'];

                                                        echo "
                                                        <tr>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$name</td>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$alias</td>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$role</td>
                                                            <td style='font-size:13px;'>$phone_number</td>
                                                            <td style='font-size:13px;'>$email</td>
                                                            <td style='font-size:13px;' class='d-flex align-items-center'>
                                                                <button class='btn btn-primary btn-sm me-2 edit-btn' data-id='$id'>
                                                                    <i class='fas fa-pencil-alt'></i>
                                                                </button>
                                                                <button class='btn btn-danger btn-sm delete-btn' data-id='$id'>
                                                                    <i class='fas fa-trash-alt'></i>
                                                                </button>
                                                            </td>
                                                        </tr>";

                                                ?>

                                                        <!-- Modal Edit User -->
                                                        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <form id="editUserForm" action="process_edit_user.php" method="POST">
                                                                            <div class="modal-body">
                                                                                <div class="form-group">
                                                                                    <label for="editName">Name</label>
                                                                                    <input type="text" class="form-control" id="editName" name="name" required>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label for="editAlias">Alias</label>
                                                                                    <input type="text" class="form-control" id="editAlias" name="alias" required>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label for="editRole">Role</label>
                                                                                    <select class="form-control" id="editRole" name="role" required>
                                                                                        <option value="" disabled selected>Select Role</option>
                                                                                        <option value="superadmin">Superadmin</option>
                                                                                        <option value="admin">Admin</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label for="editPhone">Phone Number</label>
                                                                                    <input type="text" class="form-control" id="editPhone" name="phone_number">
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label for="editEmail">Email</label>
                                                                                    <input type="email" class="form-control" id="editEmail" name="email">
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="submit" class="btn btn-success">Save</button>
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            </div>
                                                                        </form>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                <?php
                                                    }
                                                } else {
                                                    // If no users found
                                                    echo "<tr><td colspan='6'>No users found.</td></tr>";
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
        $('.edit-btn').on('click', function() {
            var userId = $(this).data('id');

            // Mengambil data pengguna berdasarkan ID
            $.ajax({
                url: 'get_user_data.php',
                method: 'GET',
                data: {
                    id: userId
                },
                success: function(response) {
                    var user = JSON.parse(response);
                    if (user.error) {
                        alert(user.error);
                    } else {
                        // Isi modal dengan data pengguna
                        $('#editName').val(user.name);
                        $('#editAlias').val(user.alias); // 'nickname' atau 'alias' jika ada
                        $('#editRole').val(user.role); // Menggunakan 'jabatan' sebagai role
                        $('#editPhone').val(user.telp);
                        $('#editEmail').val(user.email);

                        // Menambahkan ID ke URL form action
                        $('#editUserForm').attr('action', 'process_edit_user.php?id=' + userId);

                        // Menampilkan modal
                        $('#editUserModal').modal('show');
                    }
                }
            });
        });


        // Handle delete button click
        $('.delete-btn').on('click', function() {
            var userId = $(this).data('id');

            // Send AJAX request to delete user
            $.ajax({
                url: 'delete_user.php',
                method: 'POST',
                data: {
                    id: userId
                },
                success: function(response) {
                    if (response === 'success') {
                        alert('User deleted successfully!');
                        location.reload(); // Reload the page to reflect the change
                    } else {
                        alert('Error deleting user');
                    }
                }
            });
        });
    </script>

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