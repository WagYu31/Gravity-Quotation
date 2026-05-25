<?php
include "conn.php";
date_default_timezone_set('Asia/Jakarta');

$quonum = isset($_GET['quonum']) ? $_GET['quonum'] : null;
$quoId = isset($_GET['id']) ? $_GET['id'] : null;
$customerName = '';
$customerId = '';
$quoFrom = null;
$quoCode = null;
$statusQuo = null;
$linkThis = "newQuotation.php?quonum=$quonum&id=$quoId";

try {
    if ($quonum) {
        // Ambil data customer berdasarkan quonum
        $sql = "SELECT customer.id, customer.name 
                FROM quo 
                JOIN customer ON quo.customer_id = customer.id 
                WHERE quo.quo_num = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param('s', $quonum);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $customerName = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $customerId = $row['id'];
        }

        $stmt->close();

        // Ambil data tambahan dari tabel quo
        $query = "SELECT add_note, `from`, `quo_code`, `status` FROM `quo` WHERE quo_num = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param('s', $quonum);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $addNote = $row['add_note'];
            $quoFrom = $row['from'];
            $quoCode = $row['quo_code'];
            $statusQuo = $row['status'];
        }

        $stmt->close();
    }
} catch (Exception $e) {
    // Log error untuk debugging (jika diperlukan)
    error_log("Error fetching quo data: " . $e->getMessage());
    // Jangan tampilkan error kepada user, cukup tangani secara default
}

// Tentukan readonly jika $quonum null
$readonly = is_null($quonum) ? 'readonly' : '';
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
                        <h3 class="fw-bold mb-3">Forms</h3>
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
                                <a href="#">Forms</a>
                            </li>
                            <li class="separator">
                                <i class="icon-arrow-right"></i>
                            </li>
                            <li class="nav-item">
                                <a href="#">Quotation</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <form action="proses_insert.php" method="POST" enctype="multipart/form-data">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Form Quotation</div>
                                        <?php
                                        if ($statusQuo == "temp") {
                                            echo "<small class='text-muted mt-1'><i>Save this quotation data after completion to obtain the reference code.</i></small>";
                                        } else {
                                        ?>
                                            <div class='card-subtitle text-muted mt-1' style='text-transform:uppercase;'><?php echo $quoCode; ?></div>
                                        <?php
                                        }
                                        ?>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <i class="fa fa-user"></i>
                                                        </span>
                                                        <input
                                                            type="text"
                                                            id="customer-search"
                                                            class="form-control"
                                                            placeholder="Customer"
                                                            value="<?php echo $customerName; ?>"
                                                            onkeyup="searchCustomer(this.value)" />
                                                        <input
                                                            type="hidden"
                                                            id="customer-id"
                                                            name="customer_id"
                                                            value="<?php echo $customerId; ?>" />
                                                        <div id="customer-suggestions" class="dropdown-menu" style="display: none;"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                function searchCustomer(query) {
                                                    if (query.length === 0) {
                                                        document.getElementById('customer-suggestions').style.display = 'none';
                                                        return;
                                                    }

                                                    fetch('search_customer.php?q=' + query)
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            let suggestions = document.getElementById('customer-suggestions');
                                                            suggestions.innerHTML = '';

                                                            if (data.length > 0) {
                                                                suggestions.style.display = 'block';
                                                                data.forEach(customer => {
                                                                    let item = document.createElement('a');
                                                                    item.className = 'dropdown-item';
                                                                    item.href = '#';
                                                                    item.textContent = customer.name + ' - ' + customer.phone_number;
                                                                    item.onclick = function() {
                                                                        document.getElementById('customer-search').value = customer.name;
                                                                        document.getElementById('customer-id').value = customer.id;
                                                                        suggestions.style.display = 'none';

                                                                        // Trigger AJAX to insert into `quo`
                                                                        createQuotation(customer.id);
                                                                    };
                                                                    suggestions.appendChild(item);
                                                                });
                                                            } else {
                                                                suggestions.style.display = 'none';
                                                            }
                                                        });
                                                }

                                                function createQuotation(customerId) {
                                                    const quonum = new URLSearchParams(window.location.search).get('quonum');

                                                    fetch('process_quotation.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                customer_id: customerId,
                                                                quonum: quonum // Kirim quonum jika ada
                                                            })
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                // Redirect ke halaman dengan quonum yang sudah diperbarui
                                                                window.location.href = 'newQuotation.php?quonum=' + data.quonum + '&id=' + data.idQuo;
                                                            } else {
                                                                alert('Failed to update/create quotation: ' + data.message);
                                                            }
                                                        });
                                                }
                                            </script>

                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <div class="input-icon">
                                                        <input
                                                            type="text"
                                                            id="product-search"
                                                            class="form-control"
                                                            placeholder="Search product..."
                                                            onkeyup="searchProduct(this.value)"
                                                            <?php echo $readonly; ?> />
                                                        <input
                                                            type="hidden"
                                                            id="product-id"
                                                            name="product_id" />
                                                        <div id="product-suggestions" class="dropdown-menu" style="display: none; position: absolute;"></div>
                                                    </div>
                                                </div>
                                                <?php if ($user_id != 6 && $user_id != 9) : ?>
                                                <a href="add-product.php" target="_blank" class="btn btn-sm btn-primary p-1 px-2 ms-3">+ Product Baru</a>
                                                <?php endif; ?>
                                                <!--<button type="button" class="btn btn-primary btn-sm p-1 px-2 ms-3" data-bs-toggle="modal" data-bs-target="#produkBaruModal">-->
                                                <!--    + Produk Baru-->
                                                <!--</button>-->
                                            </div>

                                            <script>
                                                function searchProduct(query) {
                                                    if (query.trim() === '') {
                                                        document.getElementById('product-suggestions').style.display = 'none';
                                                        return;
                                                    }

                                                    fetch('search_product.php?q=' + encodeURIComponent(query))
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            const suggestions = document.getElementById('product-suggestions');
                                                            suggestions.innerHTML = '';

                                                            if (data.length > 0) {
                                                                suggestions.style.display = 'block';
                                                                data.forEach(product => {
                                                                    const item = document.createElement('a');
                                                                    item.className = 'dropdown-item';
                                                                    item.href = '#';
                                                                    item.textContent = `${product.kategori} - ${product.code}`;
                                                                    item.onclick = function() {
                                                                        selectProduct(product.id, product.code, product.kategori, product.price);
                                                                        suggestions.style.display = 'none';
                                                                    };
                                                                    suggestions.appendChild(item);
                                                                });
                                                            } else {
                                                                suggestions.style.display = 'none';
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error fetching product data:', error);
                                                        });
                                                }

                                                function selectProduct(productId, productCode, productName, productPrice) {
                                                    showProductModal(productId, productCode, productName, productPrice);
                                                }

                                                function showProductModal(productId, productCode, productName, productPrice) {
                                                    document.getElementById('product-id').value = productId;
                                                    document.getElementById('product-name').value = productName;
                                                    document.getElementById('product-price').value = productPrice;
                                                    document.getElementById('product-qty').value = ''; // Reset qty
                                                    document.getElementById('discount-value').value = ''; // Reset discount
                                                    document.getElementById('discount-type').value = 'percent'; // Default discount type

                                                    // Tampilkan modal
                                                    $('#productModal').modal('show');
                                                }
                                            </script>

                                            <!-- Modal -->
                                            <div class="modal fade" id="produkBaruModal" tabindex="-1" aria-labelledby="produkBaruModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="produkBaruModalLabel">Tambah Produk Baru</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Form Input -->
                                                            <form action="insert_barang_baru.php" method="POST" enctype="multipart/form-data">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="form-group">
                                                                                    <label for="kode">Kode Barang</label>
                                                                                    <input
                                                                                        type="text"
                                                                                        class="form-control"
                                                                                        id="code"
                                                                                        name="code"
                                                                                        placeholder="Kode Barang"
                                                                                        required />
                                                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Masukan kode barang sesuai dengan Accurate</i></small>
                                                                                </div>
                                                                                
                                                                                <input type="hidden" name="linkThis" value="<?php echo $linkThis;?>">

                                                                                <div class="form-group">
                                                                                    <label for="kode">Nama Barang</label>
                                                                                    <input
                                                                                        type="text"
                                                                                        class="form-control"
                                                                                        id="kategori"
                                                                                        name="kategori"
                                                                                        placeholder="Nama Barang"
                                                                                        required />
                                                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Masukan Nama Barang contoh : IP Camera Indoor 4MP<br>LX-2230IP</i></small>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label for="kode">Satuan</label>
                                                                                    <input
                                                                                        type="text"
                                                                                        class="form-control"
                                                                                        id="satuan"
                                                                                        name="satuan"
                                                                                        placeholder="Satuan Barang"
                                                                                        required />
                                                                                    <small id="Kode" class="form-text text-muted" style="font-size:12px;"><i>Unit / Pcs / Node / Lainnya</i></small>
                                                                                </div>

                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="form-group">
                                                                                    <label for="comment">Harga</label>
                                                                                    <div class="input-group mb-3">
                                                                                        <span class="input-group-text">Rp</span>
                                                                                        <input
                                                                                            type="number"
                                                                                            class="form-control"
                                                                                            name="price"
                                                                                            aria-label="Amount (to the nearest dollar)" />
                                                                                        <span class="input-group-text">,-</span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label for="exampleFormControlFile1">Gambar Produk</label>
                                                                                    <input
                                                                                        type="file"
                                                                                        class="form-control form-control-file"
                                                                                        id="exampleFormControlFile1"
                                                                                        name="exampleFormControlFile1"
                                                                                        onchange="previewImage(event)" />
                                                                                    <img
                                                                                        id="preview"
                                                                                        src=""
                                                                                        alt="Preview Gambar"
                                                                                        style="display: none; margin-top: 10px; height: 80px; width: auto;" />
                                                                                </div>

                                                                                <script>
                                                                                    function previewImage(event) {
                                                                                        const fileInput = event.target;
                                                                                        const preview = document.getElementById('preview');
                                                                                        const file = fileInput.files[0];

                                                                                        if (file) {
                                                                                            const reader = new FileReader();
                                                                                            reader.onload = function(e) {
                                                                                                preview.src = e.target.result;
                                                                                                preview.style.display = 'block';
                                                                                            };
                                                                                            reader.readAsDataURL(file);
                                                                                        } else {
                                                                                            preview.style.display = 'none';
                                                                                            preview.src = '';
                                                                                        }
                                                                                    }
                                                                                </script>

                                                                                <div class="form-group">
                                                                                    <label for="comment">Spesifikasi</label>
                                                                                    <textarea class="form-control" id="comment" name="comment" rows="5"></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6 col-lg-4 mt-md-0 mt-4">
                                                                                <label class="mb-2"><b>Form Link Product</b></label>
                                                                                <div class="form-group">
                                                                                    <label for="basic-url">Link 1</label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-text" id="basic-addon3">https://</span>
                                                                                        <input
                                                                                            type="text"
                                                                                            class="form-control"
                                                                                            id="basic-url"
                                                                                            name="link_1"
                                                                                            aria-describedby="basic-addon3" />
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <input
                                                                                        type="text"
                                                                                        class="form-control"
                                                                                        id="code"
                                                                                        name="name_link_1"
                                                                                        placeholder="Nama Link" />
                                                                                    <small id="Kode" class="form-text text-muted">Contoh : Video Hasil Rekaman</small>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label for="basic-url">Link 2</label>
                                                                                    <div class="input-group mb-0">
                                                                                        <span class="input-group-text" id="basic-addon3">https://</span>
                                                                                        <input
                                                                                            type="text"
                                                                                            class="form-control"
                                                                                            id="basic-url"
                                                                                            name="link_2"
                                                                                            aria-describedby="basic-addon3" />
                                                                                    </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <input
                                                                                        type="text"
                                                                                        class="form-control"
                                                                                        id="code"
                                                                                        name="name_link_2"
                                                                                        placeholder="Nama Link" />
                                                                                    <small id="Kode" class="form-text text-muted">Contoh : Video Live Streaming</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-action">
                                                                        <button type="submit" class="btn btn-success">Submit</button>
                                                                        <button data-bs-dismiss="modal" class="btn btn-danger">Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Modal -->
                                            <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Form content -->
                                                            <form id="productForm">
                                                                <?php echo $quoId; ?>
                                                                <input type="hidden" class="form-control" id="product-id" name="product_id" readonly />
                                                                <div class="form-group">
                                                                    <label for="product-name">Product Name</label>
                                                                    <input type="text" class="form-control" id="product-name" name="product_name" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="product-price">Price</label>
                                                                    <input type="number" class="form-control" id="product-price" name="price" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="product-qty">Quantity</label>
                                                                    <input type="number" class="form-control" id="product-qty" name="qty" />
                                                                </div>
                                                                <input type="hidden" value="<?php echo $quoId; ?>" id="idquo" name="quoid" />
                                                                <div class="form-group">
                                                                    <label>Discount</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" id="discount-value" name="discount_value" placeholder="Discount" />
                                                                        <select class="custom-select" id="discount-type" name="discount_type">
                                                                            <option value="percent">%</option>
                                                                            <option value="nominal">Nominal</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" onclick="saveProductDetails()">Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                function saveProductDetails() {
                                                    const formData = {
                                                        quo_num: "<?php echo $quonum; ?>", // Pass quo_num dynamically from PHP
                                                        product_id: document.getElementById('product-id').value,
                                                        product_name: document.getElementById('product-name').value,
                                                        qty: document.getElementById('product-qty').value,
                                                        price: document.getElementById('product-price').value,
                                                        idquo: document.getElementById('idquo').value,
                                                        discount_type: document.getElementById('discount-type').value,
                                                        discount_value: document.getElementById('discount-value').value
                                                    };

                                                    fetch('insert_quo_detail.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                            },
                                                            body: JSON.stringify(formData),
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                // Tampilkan pesan berhasil
                                                                alert('Product added successfully!');

                                                                // Sembunyikan modal
                                                                const modal = document.getElementById('productModal'); // Ganti dengan ID modal Anda
                                                                const modalInstance = bootstrap.Modal.getInstance(modal); // Gunakan Bootstrap Modal API
                                                                modalInstance.hide();

                                                                // Refresh halaman
                                                                window.location.reload();
                                                            } else {
                                                                alert('Error: ' + data.message);
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error:', error);
                                                        });
                                                }
                                            </script>

                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <div class="d-flex">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="radio"
                                                                name="flexRadioDefault"
                                                                id="flexRadioDefault1"
                                                                value="CV"
                                                                <?php
                                                                echo $readonly;
                                                                // Jika quo.from == 'CV', tambahkan atribut checked
                                                                echo ($quoFrom === 'CV') ? ' checked' : '';
                                                                ?>
                                                                onchange="updateQuotationFrom('<?php echo $quonum; ?>', this.value)" />
                                                            <label
                                                                class="form-check-label"
                                                                for="flexRadioDefault1">
                                                                CV
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="radio"
                                                                name="flexRadioDefault"
                                                                id="flexRadioDefault2"
                                                                value="Loewix"
                                                                <?php
                                                                echo $readonly;
                                                                echo ($quoFrom === 'Loewix') ? ' checked' : '';
                                                                ?>
                                                                onchange="updateQuotationFrom('<?php echo $quonum; ?>', this.value)" />
                                                            <label
                                                                class="form-check-label"
                                                                for="flexRadioDefault2">
                                                                Loewix
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                function updateQuotationFrom(quonum, fromValue) {
                                                    if (!quonum) {
                                                        console.error("Quonum is null, cannot update.");
                                                        return;
                                                    }

                                                    fetch('update_quotation_from.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                            },
                                                            body: JSON.stringify({
                                                                quonum: quonum,
                                                                from: fromValue,
                                                            }),
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                console.log('Quotation updated successfully:', data.message);
                                                                window.location.reload();
                                                            } else {
                                                                console.error('Failed to update quotation:', data.message);
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error updating quotation:', error);
                                                        });
                                                }
                                            </script>

                                            <?php
                                            include "quo_data.php";
                                            ?>

                                            <div class="col-12 mt-4">
                                                <div class="table-responsive">
                                                    <table id="basic-datatables" class="display table table-striped table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Kode Produk</th>
                                                                <th>Qty</th>
                                                                <th>Price</th>
                                                                <th>Discount</th>
                                                                <th>Amount</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tfoot>
                                                            <tr style="border-top:1px solid grey;">
                                                                <th colspan="2" rowspan="4" class="note">
                                                                    Note :<br>
                                                                    <?php echo !empty($addNote) ? nl2br($addNote) : '- Price subject to change without prior notice'; ?>
                                                                    <br>
                                                                    <br>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-warning px-3 py-1"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#noteModal"
                                                                        onclick="EditNote('<?php echo $quonum; ?>')">
                                                                        <small>Edit Note</small>
                                                                    </button>
                                                                </th>
                                                                <th rowspan="4"></th>
                                                                <th>Sub Total</th>
                                                                <th><?= number_format($total_sub_amount, 0); ?></th>
                                                                <th></th>
                                                                <div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="noteModalLabel">Edit Note</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form id="noteForm">
                                                                                    <div class="mb-3">
                                                                                        <textarea class="form-control" id="editNoteTextarea" rows="4" placeholder="Masukkan catatan baru"></textarea>
                                                                                    </div>
                                                                                    <input type="hidden" id="editQuonum" value="">
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                                <button type="button" class="btn btn-primary" onclick="updateNote()">Simpan</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <script>
                                                                    function EditNote(quonum) {
                                                                        // Isi hidden input dengan quonum
                                                                        document.getElementById('editQuonum').value = quonum;

                                                                        // Ambil data add_note dari server untuk ditampilkan (opsional)
                                                                        fetch('fetch_note.php?quonum=' + quonum)
                                                                            .then(response => response.json())
                                                                            .then(data => {
                                                                                document.getElementById('editNoteTextarea').value = data.add_note || '';
                                                                            })
                                                                            .catch(error => console.error('Error fetching note:', error));
                                                                    }

                                                                    function updateNote() {
                                                                        const quonum = document.getElementById('editQuonum').value;
                                                                        const newNote = document.getElementById('editNoteTextarea').value;

                                                                        // Kirim data ke server untuk update
                                                                        fetch('update_note.php', {
                                                                                method: 'POST',
                                                                                headers: {
                                                                                    'Content-Type': 'application/json'
                                                                                },
                                                                                body: JSON.stringify({
                                                                                    quonum: quonum,
                                                                                    add_note: newNote
                                                                                })
                                                                            })
                                                                            .then(response => response.json())
                                                                            .then(result => {
                                                                                if (result.success) {
                                                                                    // Tutup modal
                                                                                    $('#noteModal').modal('hide');
                                                                                    alert('Catatan berhasil diperbarui.');

                                                                                    // Refresh halaman untuk memperbarui tampilan
                                                                                    location.reload();
                                                                                } else {
                                                                                    alert('Gagal memperbarui catatan: ' + result.error);
                                                                                }
                                                                            })
                                                                            .catch(error => console.error('Error updating note:', error));
                                                                    }
                                                                </script>

                                                            </tr>
                                                            <tr>
                                                                <th>Discount Product</th>
                                                                <th><?= number_format($total_discount, 0); ?></th>
                                                                <th></th>
                                                            </tr>
                                                            <tr style="border-top:1px solid grey;">
                                                                <th>After Discount</th>
                                                                <th><?= number_format($total_amount, 0); ?></th>
                                                                <th></th>
                                                            </tr>
                                                            <tr>
                                                                <th>Special Discount</th>
                                                                <th><?= number_format($disc_all, 0); ?></th>
                                                                <th>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-primary px-3 py-1"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#discountModal"
                                                                        onclick="AddDisc('<?php echo $quonum; ?>')">
                                                                        + <small>Discount</small>
                                                                    </button>

                                                                    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title" id="discountModalLabel">Add Discount</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <form id="discount-form">
                                                                                        <div class="form-group">
                                                                                            <label>Discount</label>
                                                                                            <div class="input-group">
                                                                                                <!-- Hidden input untuk quonum -->
                                                                                                <input type="hidden" class="form-control" name="quonum" id="quonum" value="">
                                                                                                <input type="number" class="form-control" id="dscv" name="discount_value" placeholder="Discount" required />
                                                                                                <select class="custom-select" id="dsct" name="discount_type">
                                                                                                    <option value="percent">%</option>
                                                                                                    <option value="nominal">Nominal</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                    <button type="button" class="btn btn-primary" onclick="submitDiscount()">Apply Discount</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <script>
                                                                        function AddDisc(quonum) {
                                                                            // Isi hidden field dengan quonum
                                                                            document.getElementById('quonum').value = quonum;

                                                                            // Tampilkan modal
                                                                            const modal = new bootstrap.Modal(document.getElementById("discountModal"));
                                                                            modal.show();
                                                                        }

                                                                        function submitDiscount() {
                                                                            // Ambil data dari form
                                                                            const quonum = document.getElementById('quonum').value;
                                                                            const discountValue = document.getElementById('dscv').value;
                                                                            const discountType = document.getElementById('dsct').value === 'percent' ? 'p' : 'n';

                                                                            // Validasi input
                                                                            if (!discountValue) {
                                                                                alert('Please enter a discount value.');
                                                                                return;
                                                                            }

                                                                            // Siapkan data untuk dikirim
                                                                            const formData = new FormData();
                                                                            formData.append('quonum', quonum);
                                                                            formData.append('discount_value', discountValue);
                                                                            formData.append('discount_type', discountType);

                                                                            // Kirim data menggunakan fetch
                                                                            fetch('update_quo_discount.php', {
                                                                                    method: 'POST',
                                                                                    body: formData,
                                                                                })
                                                                                .then(response => response.json())
                                                                                .then(data => {
                                                                                    if (data.success) {
                                                                                        alert('Discount updated successfully!');
                                                                                        location.reload(); // Reload halaman setelah berhasil
                                                                                    } else {
                                                                                        alert('Failed to update discount: ' + data.message);
                                                                                    }
                                                                                })
                                                                                .catch(error => {
                                                                                    console.error('Error:', error);
                                                                                    alert('An error occurred while updating the discount.');
                                                                                });
                                                                        }
                                                                    </script>

                                                                </th>
                                                            </tr>
                                                            <?php if ($from === 'CV'): ?>
                                                                <tr>
                                                                    <th colspan="3"></th>
                                                                    <th>PPN 11%</th>
                                                                    <th><?= number_format($ppn, 0); ?></th>
                                                                    <th></th>
                                                                </tr>
                                                                <tr style="border-top:1px solid grey;border-bottom:1px solid grey;">
                                                                    <th colspan="3"></th>
                                                                    <th>Grand Total</th>
                                                                    <th><?= number_format($total_ppn, 0); ?></th>
                                                                    <th></th>
                                                                </tr>
                                                            <?php else: ?>
                                                                <tr style="border-top:1px solid grey;border-bottom:1px solid grey;">
                                                                    <th colspan="3"></th>
                                                                    <th>Grand Total</th>
                                                                    <th><?= number_format($total_non_ppn, 0); ?></th>
                                                                    <th></th>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tfoot>

                                                        <tbody>
                                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($row['barang_name'] ?? $row['kategori']); ?></td>
                                                                    <td><?= htmlspecialchars($row['qty']); ?></td>
                                                                    <td><?= number_format($row['price'], 0); ?></td>
                                                                    <td><?= number_format($row['disc_item'], 0); ?></td>
                                                                    <td><?= number_format($row['amount'], 0); ?></td>
                                                                    <td>
                                                                        <button
                                                                            type="button"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#emptyModal"
                                                                            class="btn btn-warning btn-sm"
                                                                            onclick="editDetail(<?= $row['id']; ?>)">
                                                                            Edit
                                                                        </button>
                                                                        <a href="delete_quo_detail.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="emptyModal" tabindex="-1" aria-labelledby="emptyModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="emptyModalLabel">Edit Details</h5>
                                                            <a href="newQuotation.php?quonum=<?php echo $quonum; ?>&id=<?php echo $quoId; ?>" class="btn-close" aria-label="Close"></a>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form id="editForm">
                                                                <input type="hidden" class="form-control" id="edit-id" name="edit_id" readonly />
                                                                <div class="form-group">
                                                                    <label for="edit-name">Product Name</label>
                                                                    <input type="text" class="form-control" id="edit-name" name="edit_name" readonly />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="edit-price">Price</label>
                                                                    <input type="number" class="form-control" id="edit-price" name="price" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="edit-qty">Quantity</label>
                                                                    <input type="number" class="form-control" id="edit-qty" name="qty" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Discount</label>
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control" id="discount-value" name="discount_value" placeholder="Discount" />
                                                                        <select class="custom-select" id="discount-type" name="discount_type">
                                                                            <option value="percent">%</option>
                                                                            <option value="nominal">Nominal</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="newQuotation.php?quonum=<?php echo $quonum; ?>&id=<?php echo $quoId; ?>" class="btn btn-secondary">Close</a>
                                                            <button type="button" class="btn btn-primary" onclick="updateDetail()">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                function editDetail(id) {
                                                    // Kirim permintaan ke server untuk mendapatkan data berdasarkan id
                                                    fetch(`getQuoDetail.php?id=${id}`)
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.error) {
                                                                alert(data.error); // Jika ada error, tampilkan pesan
                                                            } else {
                                                                // Isi nilai ke dalam modal form
                                                                document.getElementById("edit-id").value = data.id;
                                                                document.getElementById("edit-name").value = data.product_name;
                                                                document.getElementById("edit-price").value = data.price;
                                                                document.getElementById("edit-qty").value = data.qty;

                                                                // Mendapatkan elemen discount
                                                                const discountValueField = document.getElementById("discount-value");
                                                                const discountTypeField = document.getElementById("discount-type");

                                                                // Pastikan data.discount_value ada dan nilai data.discount_type valid
                                                                if (data.discount_value !== undefined && data.discount_type !== undefined) {
                                                                    // Mengisi field discount-value dan discount-type
                                                                    if (data.discount_type === 'nominal') {
                                                                        discountValueField.value = data.discount_value; // Set nilai nominal di discount-value
                                                                        discountTypeField.value = 'nominal'; // Set select option menjadi nominal
                                                                    } else if (data.discount_type === 'percent') {
                                                                        discountValueField.value = data.discount_value; // Set nilai persen di discount-value
                                                                        discountTypeField.value = 'percent'; // Set select option menjadi percent
                                                                    }
                                                                } else {
                                                                    console.log("Data discount tidak ditemukan.");
                                                                }

                                                                // Tampilkan modal
                                                                const modal = new bootstrap.Modal(document.getElementById("emptyModal"));
                                                                modal.show();
                                                            }
                                                        })
                                                        .catch(error => console.error("Error:", error));
                                                }

                                                function updateDetail() {
                                                    // Ambil data dari form
                                                    const formData = new FormData(document.getElementById('editForm'));

                                                    // Kirim data menggunakan fetch
                                                    fetch('updateQuoDetail.php', {
                                                            method: 'POST',
                                                            body: formData
                                                        })
                                                        .then(response => response.json())
                                                        .then(result => {
                                                            if (result.success) {
                                                                alert('Data updated successfully!');
                                                                // Tutup modal
                                                                const modal = bootstrap.Modal.getInstance(document.getElementById('emptyModal'));
                                                                modal.hide();

                                                                // Refresh data table atau halaman
                                                                location.reload();
                                                            } else {
                                                                alert('Failed to update data: ' + result.message);
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error:', error);
                                                            alert('An error occurred while updating data.');
                                                        });
                                                }


                                                // function deleteDetail(id) {
                                                //     if (!confirm("Are you sure you want to delete this detail?")) {
                                                //         return;
                                                //     }

                                                //     fetch('delete_quo_detail.php', {
                                                //             method: 'POST',
                                                //             headers: {
                                                //                 'Content-Type': 'application/json',
                                                //             },
                                                //             body: JSON.stringify({
                                                //                 id: id
                                                //             })
                                                //         })
                                                //         .then(response => response.json())
                                                //         .then(data => {
                                                //             if (data.success) {
                                                //                 alert("Item deleted successfully!");
                                                //                 window.location.href = `newQuotation.php?quonum=${quonum}&id=${quoId}`;
                                                //             } else {
                                                //                 alert("Error: " + data.message);
                                                //             }
                                                //         })
                                                //         .catch(error => {
                                                //             console.error("Error:", error);
                                                //         });
                                                // }
                                            </script>


                                            <?php
                                            $stmt->close();
                                            $totalStmt->close();
                                            ?>



                                        </div>
                                    </div>
                                    <div class="card-action">
                                        <button type="button" class="btn btn-primary" onclick="updateQuoSave()"><i class="fa fa-save me-2"></i> Save</button>

                                        <a href="#"
                                            class="btn btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#printModal"
                                            data-quo-num="<?php echo $quonum; ?>"
                                            data-quo-id="<?php echo $quoId; ?>"
                                            data-bs-toggle="tooltip"
                                            title="Preview Quotation">
                                            <i class="fa fa-print me-2"></i> Print
                                        </a>

                                        <!--<a href="previewQuotation.php?quonum=<?php echo $quonum; ?>&id=<?php echo $quoId; ?>" class="btn btn-success" target="_blank"><i class="fa fa-print me-2"></i> Print</a>-->
                                        <!--<button type="reset" class="btn btn-danger"><i class="fa fa-times me-2"></i> Discard</button>-->
                                        <button type="button" class="btn btn-danger" onclick="discardQuotation('<?php echo $quoId; ?>', '<?php echo $quonum; ?>')">
                                            <i class="fa fa-times me-2"></i> Discard
                                        </button>
                                    </div>
                                </div>
                            </form>


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

                </div>
            </div>

            <script>
                function updateQuoSave() {
                    // Ambil quo_num yang terkait (pastikan nilai quo_num ada di halaman ini)
                    const quoNum = '<?php echo $quonum; ?>'; // Ambil nilai quo_num di PHP dan masukkan ke JS

                    // Panggil server untuk mendapatkan quo_code terakhir
                    fetch('getLastQuoCode.php')
                        .then(response => response.json())
                        .then(responseData => {
                            if (responseData.success) {
                                const lastQuoCode = responseData.quo_code;

                                // Format quo_code baru
                                const quoCode = generateNewQuoCode(lastQuoCode);
                                const status = "saved";

                                // Buat objek data untuk dikirimkan ke server
                                const data = {
                                    quo_num: quoNum,
                                    quo_code: quoCode,
                                    status: status
                                };

                                // Kirim data ke server menggunakan fetch
                                return fetch('updateQuo.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(data),
                                });
                            } else {
                                throw new Error('Failed to fetch last quo_code');
                            }
                        })
                        .then(response => response.json())
                        .then(responseData => {
                            if (responseData.success) {
                                alert('Quotation updated successfully');
                                location.reload(); // Refresh halaman setelah update berhasil
                            } else {
                                alert('Error: ' + responseData.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                // Fungsi untuk membuat quo_code baru
                function generateNewQuoCode(lastQuoCode) {
                    const today = new Date();
                    const day = String(today.getDate()).padStart(2, '0');
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const year = String(today.getFullYear()).slice(-2); // Ambil 2 digit terakhir tahun
                    const datePart = year + day + month;

                    if (lastQuoCode && lastQuoCode.startsWith('QLX')) {
                        const lastNumber = parseInt(lastQuoCode.slice(3, 7), 10); // Ambil 4 digit setelah QLX
                        const newNumber = String(lastNumber + 1).padStart(4, '0'); // Tambahkan 1 dan format menjadi 4 digit
                        return `QLX${newNumber}${datePart}`;
                    }

                    // Jika tidak ada quo_code terakhir, mulai dari 0001
                    return `QLX0001${datePart}`;
                }

                function discardQuotation(quoId, quoNum) {
                    if (confirm("Apakah Anda yakin ingin menghapus quotation ini?")) {
                        fetch('process_discard_quotation.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    quoId: quoId,
                                    quoNum: quoNum
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = 'listQuotation.php'; // Redirect ke halaman listQuotation.php
                                } else {
                                    alert('Gagal menghapus quotation: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat menghapus quotation.');
                            });
                    }
                }
            </script>

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
        document.getElementById('submitPrint').addEventListener('click', function() {
            var quoNum = document.querySelector('a[data-bs-target="#printModal"]').getAttribute('data-quo-num');
            var quoId = document.querySelector('a[data-bs-target="#printModal"]').getAttribute('data-quo-id');
            var pict = document.getElementById('checkPicture').checked ? 'Y' : 'N';
            var link = document.getElementById('checkLink').checked ? 'Y' : 'N';

            var url = `previewQuotation.php?quonum=${quoNum}&id=${quoId}&pict=${pict}&link=${link}`;
            window.open(url, '_blank');
        });
    </script>

</body>

</html>