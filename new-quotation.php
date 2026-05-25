<?php
include "conn.php";
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
                                                            onkeyup="searchCustomer(this.value)" />
                                                        <input
                                                            type="hidden"
                                                            id="customer-id"
                                                            name="customer_id" />
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

                                                    // Kirim permintaan AJAX ke server
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
                                                                    };
                                                                    suggestions.appendChild(item);
                                                                });
                                                            } else {
                                                                suggestions.style.display = 'none';
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
                                                            onkeyup="searchProduct(this.value)" />
                                                        <input
                                                            type="hidden"
                                                            id="product-id"
                                                            name="product_id" />
                                                        <div id="product-suggestions" class="dropdown-menu" style="display: none; position: absolute;"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                function searchProduct(query) {
                                                    if (query.trim() === '') {
                                                        document.getElementById('product-suggestions').style.display = 'none';
                                                        return;
                                                    }

                                                    // Kirim permintaan AJAX ke server
                                                    fetch('search_product.php?q=' + encodeURIComponent(query))
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            const suggestions = document.getElementById('product-suggestions');
                                                            suggestions.innerHTML = ''; // Reset suggestions

                                                            if (data.length > 0) {
                                                                suggestions.style.display = 'block';
                                                                data.forEach(product => {
                                                                    const item = document.createElement('a');
                                                                    item.className = 'dropdown-item';
                                                                    item.href = '#';
                                                                    item.textContent = `${product.code} - ${product.tipe}`;
                                                                    item.onclick = function() {
                                                                        selectProduct(product.id, product.code, product.tipe, product.price);
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
                                                    // Tampilkan modal dengan data produk
                                                    showProductModal(productId, productCode, productName, productPrice);
                                                }

                                                function showProductModal(productId, productCode, productName, productPrice) {
                                                    // Isi form modal dengan data produk
                                                    document.getElementById('product-id').value = productId;
                                                    document.getElementById('product-name').value = productCode;
                                                    document.getElementById('product-price').value = productPrice;
                                                    document.getElementById('product-qty').value = ''; // Reset qty
                                                    document.getElementById('discount-value').value = ''; // Reset discount
                                                    document.getElementById('discount-type').value = 'percent'; // Default discount type

                                                    // Tampilkan modal
                                                    $('#productModal').modal('show');
                                                }
                                            </script>

                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <!-- <label>Dibuat</label><br /> -->
                                                    <div class="d-flex">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="radio"
                                                                name="flexRadioDefault"
                                                                id="flexRadioDefault1" />
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
                                                                checked />
                                                            <label
                                                                class="form-check-label"
                                                                for="flexRadioDefault2">
                                                                Loewix
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            

                                            <div class="col-12">
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
                                                            <tr>
                                                                <th>Kode Produk</th>
                                                                <th>Qty</th>
                                                                <th>Price</th>
                                                                <th>Discount</th>
                                                                <th>Amount</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                        </tfoot>
                                                        <tbody>
                                                            <!-- Data akan diisi secara dinamis -->
                                                        </tbody>
                                                    </table>
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
                                                                <input type="hidden" class="form-control" id="product-id" name="product_id" readonly />
                                                                <div class="form-group">
                                                                    <label for="product-name">Product Name</label>
                                                                    <input type="text" class="form-control" id="product-name" name="product_name" readonly />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="product-price">Price</label>
                                                                    <input type="number" class="form-control" id="product-price" name="price" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="product-qty">Quantity</label>
                                                                    <input type="number" class="form-control" id="product-qty" name="qty" />
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
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary" onclick="saveProductDetails()">Save</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <script>
                                                // Fungsi untuk menyimpan detail produk
                                                function saveProductDetails() {
                                                    const productId = document.getElementById('product-id').value;
                                                    const price = parseFloat(document.getElementById('product-price').value || 0);
                                                    const qty = parseInt(document.getElementById('product-qty').value || 0);
                                                    const discountValue = parseFloat(document.getElementById('discount-value').value || 0);
                                                    const discountType = document.getElementById('discount-type').value;

                                                    // Hitung diskon
                                                    let discItem = discountType === "percent" ? (price * discountValue / 100) : discountValue;
                                                    let discType = discountType === "percent" ? "p" : "n";

                                                    // Ambil atau buat quo_num
                                                    let quonum = localStorage.getItem('quo_num');
                                                    if (!quonum) {
                                                        quonum = generateQuoNum();
                                                        localStorage.setItem('quo_num', quonum);
                                                    }

                                                    // Kirim data ke server menggunakan AJAX
                                                    fetch('save_quo.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                product_id: productId,
                                                                price: price,
                                                                qty: qty,
                                                                discount_value: discItem,
                                                                discount_type: discType,
                                                                quo_num: quonum
                                                            })
                                                        })
                                                        .then(response => response.json())
                                                        .then(result => {
                                                            if (result.success) {
                                                                // Tutup modal dan refresh tabel
                                                                $('#productModal').modal('hide');
                                                                alert('Data berhasil disimpan');
                                                                loadTableData(quonum); // Refresh tabel
                                                            } else {
                                                                alert('Gagal menyimpan data: ' + result.error);
                                                            }
                                                        })
                                                        .catch(error => console.error('Error:', error));
                                                }

                                                // Fungsi untuk meng-generate quo_num
                                                function generateQuoNum() {
                                                    const generateRandomCode = () => Math.random().toString(36).substring(2, 9).toUpperCase(); // 7 karakter acak
                                                    return `${generateRandomCode()}-${generateRandomCode()}-${generateRandomCode()}-${generateRandomCode()}`;
                                                }

                                                // Fungsi untuk memuat data tabel berdasarkan quo_num
                                                function loadTableData(quonum) {
                                                    fetch(`fetch_table_data.php?quonum=${quonum}`)
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            const tableBody = document.querySelector('#basic-datatables tbody');
                                                            tableBody.innerHTML = ''; // Kosongkan tabel

                                                            // Tambahkan data baru ke tabel
                                                            data.forEach(row => {
                                                                const tr = document.createElement('tr');
                                                                tr.innerHTML = `
                                                                    <td>${row.product_code}</td>
                                                                    <td>${row.qty}</td>
                                                                    <td>Rp ${parseInt(row.price).toLocaleString()}</td>
                                                                    <td>${row.disc_item}</td>
                                                                    <td>Rp ${parseInt(row.amount).toLocaleString()}</td>
                                                                    <td>
                                                                        <div class="form-button-action">
                                                                            <button class="btn btn-primary btn-sm" title="Edit">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-danger btn-sm ms-1" title="Delete">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>`;
                                                                tableBody.appendChild(tr);
                                                            });
                                                        })
                                                        .catch(error => console.error('Error fetching table data:', error));
                                                }

                                                // Event untuk memuat data saat halaman selesai dimuat
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    const quonum = localStorage.getItem('quo_num');
                                                    if (quonum) {
                                                        loadTableData(quonum); // Muat data tabel berdasarkan quo_num
                                                    }
                                                });

                                                // Event untuk menangani penyegaran halaman
                                                window.onbeforeunload = function() {
                                                    const quonum = generateQuoNum();
                                                    localStorage.setItem('quo_num', quonum);

                                                    fetch('save_quo.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                quo_num: quonum
                                                            })
                                                        })
                                                        .then(response => response.json())
                                                        .then(result => {
                                                            if (!result.success) {
                                                                console.log('Gagal menyimpan quo pada refresh: ' + result.error);
                                                            }
                                                        })
                                                        .catch(error => console.error('Error:', error));
                                                };
                                            </script>


                                        </div>
                                    </div>
                                    <div class="card-action">
                                        <button type="submit" class="btn btn-success">Save</button>
                                        <button type="reset" class="btn btn-danger">Discard</button>
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

</body>

</html>