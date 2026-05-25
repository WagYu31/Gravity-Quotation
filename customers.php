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
                                <a href="#">Customers</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Customers</h4>
                                </div>
                                <div class="card-body">
                                    <form class="mb-4" action="process_insert_customer.php" method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">Nama</label>
                                                    <input type="text" class="form-control" name="name" placeholder="Masukan Nama" required />
                                                </div>
                                                <div class="form-group">
                                                    <label for="address">Alamat</label>
                                                    <textarea class="form-control" name="address" rows="5" placeholder="Masukan Alamat" required></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name">PIC / Keterangan Lain</label>
                                                    <input type="text" class="form-control" name="ket" placeholder="Nama PIC atau Keterangan Lain " required />
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone_number">Nomor Telepon</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">+62</span>
                                                        <input type="text" class="form-control" name="phone_number" placeholder="Nomor Telepon" required />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" name="email" placeholder="Email" />
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
                                                    <th>Nama</th>
                                                    <th>Alamat</th>
                                                    <th>Keterangan</th>
                                                    <th>Nomor Telepon</th>
                                                    <th>Email</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Alamat</th>
                                                    <th>Keterangan</th>
                                                    <th>Nomor Telepon</th>
                                                    <th>Email</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php
                                                // Assuming you have already established a connection to the database
                                                $query = "
                                                    SELECT 
                                                        c.id, 
                                                        c.name, 
                                                        c.ket,
                                                        c.store_name, 
                                                        c.address, 
                                                        c.phone_number, 
                                                        c.email, 
                                                        c.type
                                                    FROM customer c
                                                    WHERE c.deleted_at IS NULL
                                                    ORDER BY c.name DESC
                                                ";

                                                // Execute the query
                                                $result = $conn->query($query);

                                                // Check if there are any results
                                                if ($result->num_rows > 0) {
                                                    // Output the data into the table
                                                    while ($row = $result->fetch_assoc()) {
                                                        // Fetch customer data
                                                        $id = $row['id'];
                                                        $name = $row['name'];
                                                        $ket = $row['ket'];
                                                        $store_name = $row['store_name'];
                                                        $address = $row['address'];
                                                        $phone_number = $row['phone_number'];
                                                        $email = $row['email'];
                                                        $type = $row['type'];
                                                        
                                                        if (substr($phone_number, 0, 1) == '8') {
                                                            // Jika iya, tambahkan 0 di depan
                                                            $phone_number = '0' . $phone_number;
                                                        }

                                                        echo "
                                                        <tr>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$name</td>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$address</td>
                                                            <td style='text-transform:capitalize; font-size:13px;'>$ket</td>
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
                                                        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <form id="editCustomerForm">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="editModalLabel">Edit Customer</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <input type="hidden" id="editCustomerId" name="id">
                                                                            <div class="mb-3">
                                                                                <label for="editName" class="form-label">Nama</label>
                                                                                <input type="text" class="form-control" id="editName" name="name" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="editAddress" class="form-label">Alamat</label>
                                                                                <textarea class="form-control" id="editAddress" name="address" rows="3" required></textarea>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="editKet" class="form-label">PIC / Keterangan Lain</label>
                                                                                <input type="text" class="form-control" id="editKet" name="ket" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="editPhoneNumber" class="form-label">Nomor Telepon</label>
                                                                                <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                                                                            </div>
                                                                            <div class="mb-3">
                                                                                <label for="editEmail" class="form-label">Email</label>
                                                                                <input type="email" class="form-control" id="editEmail" name="email" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                <?php
                                                    }
                                                } else {
                                                    // If no customers found
                                                    echo "<tr><td colspan='7'>No customers found.</td></tr>";
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
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Edit Button Click
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-id');

                    // Fetch customer details
                    fetch(`get_customer.php?id=${customerId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const customer = data.customer;

                                // Populate modal with data
                                document.getElementById('editCustomerId').value = customer.id;
                                document.getElementById('editName').value = customer.name;
                                document.getElementById('editKet').value = customer.ket;
                                document.getElementById('editAddress').value = customer.address;
                                document.getElementById('editPhoneNumber').value = customer.phone_number;
                                document.getElementById('editEmail').value = customer.email;

                                // Show the modal
                                const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                                editModal.show();
                            } else {
                                alert('Failed to fetch customer details.');
                            }
                        });
                });
            });

            // Handle Form Submission
            document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('edit_customer.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Customer updated successfully.');
                            window.location.reload();
                        } else {
                            alert('Failed to update customer.');
                        }
                    });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for Delete button
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this customer?')) {
                        // Send DELETE request via AJAX
                        fetch('delete_customer.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `id=${customerId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Customer deleted successfully.');
                                    // Optionally, remove the row from the table
                                    this.closest('tr').remove();
                                } else {
                                    alert('Failed to delete customer.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred. Please try again.');
                            });
                    }
                });
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