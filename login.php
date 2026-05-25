<?php
session_start();
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']); // Hapus pesan setelah ditampilkan
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <div class="wrapper d-flex justify-content-center align-items-center">
        <div class="container col-6">
            <div class="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <form action="proses_login.php" method="POST">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Login Account</div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Input untuk Username atau Email -->
                                        <div class="form-group col-12">
                                            <label for="username">Username / Email</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="username"
                                                name="username"
                                                placeholder="Enter your username or email"
                                                required />
                                        </div>

                                        <!-- Input untuk Password -->
                                        <div class="form-group col-12">
                                            <label for="password">Password</label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                name="password"
                                                placeholder="Enter your password"
                                                required />
                                        </div>
                                    </div>
                                </div>

                                <div class="card-action">
                                    <!-- Tombol Submit -->
                                    <button type="submit" class="btn btn-success">Login</button>
                                    <!-- Tombol Reset -->
                                    <button type="reset" class="btn btn-danger">Cancel</button>
                                    <p class="w-100 text-center mt-5">
                                        <small>Belum punya akun?</small>
                                        <br>
                                        <a href="signup.php" class="btn btn-primary btn-sm">Buat Akun</a>
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- Custom template | don't include it in your project! -->

        <?php
            // include 'custom-temp.php'
        ; ?>
    </div>

    <?php include 'core-scripts.php'; ?>
</body>

</html>