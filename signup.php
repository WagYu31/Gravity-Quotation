<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "quo";

// $host = "localhost";
// $user = "u836263092_rootQuo";
// $password = "Eddie@18";
// $database = "u836263092_quo";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection to the database failed. " . $conn->connect_error);
}


// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

// Waktu sekarang
$current_time = date('Y-m-d H:i:s');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <div class="wrapper">
        <div class="container col-6">
            <div class="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <form action="proses_signup.php" method="POST" enctype="multipart/form-data">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Register Account</div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="defaultSelect">Name</label>
                                        <select class="form-select form-control" id="defaultSelect" name="name" required>
                                            <?php
                                            $result = $conn->query("SELECT name FROM users WHERE deleted_at IS NULL AND password IS NULL");
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="largeInput">Username</label>
                                        <input type="text" name="username" class="form-control" placeholder="Enter Username" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Enter Password" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="photo_profile">Photo Profile</label>
                                        <input type="file" class="form-control" name="photo_profile" onchange="previewImage(event)" />
                                        <img id="preview" style="display: none; margin-top: 10px; height: 80px; width: auto;" />
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
                                    </div>
                                    <div class="form-group">
                                        <label for="signature">Signature</label>
                                        <input type="file" class="form-control" name="signature" onchange="previewImages(event)" />
                                        <img id="previews" style="display: none; margin-top: 10px; height: 80px; width: auto;" />
                                        <small id="signature" class="form-text text-muted">See the signature example here : <a href="assets/img/ttd-aulia.png" target="_blank">Example</a></small>
                                        <script>
                                            function previewImages(event) {
                                                const fileInput = event.target;
                                                const preview = document.getElementById('previews');
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
                                    </div>
                                </div>
                                <div class="card-action">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                    <button type="reset" class="btn btn-danger">Cancel</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>


        <?php include 'footer.php'; ?>

        <!-- Custom template | don't include it in your project! -->

        <?php
            // include 'custom-temp.php'
        ; ?>
    </div>

    <?php include 'core-scripts.php'; ?>
</body>

</html>