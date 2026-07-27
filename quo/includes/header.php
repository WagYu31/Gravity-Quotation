<?php
// Pastikan sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Dapatkan path halaman saat ini untuk menandai menu aktif
$current_page = $_SERVER['PHP_SELF'];

// LOGIKA UNTUK MENENTUKAN APAKAH TOMBOL INFORMASI PERLU MUNCUL
$show_announcement_period = (new DateTime('now', new DateTimeZone('Asia/Jakarta')) < new DateTime('2025-09-06'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Quotation Loewix</title>
    <link rel="icon" type="image/png" href="/quo/assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="/quo/assets/css/style.css">
    <link rel="stylesheet" href="/quo/assets/css/custom-ui.css?v=<?php echo time(); ?>">
    <script>!function(){var t=localStorage.getItem('gv-theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');}();</script>

</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php $user_initials = mb_substr($_SESSION['user_name'] ?? 'U', 0, 1); ?>
    <nav class="navbar navbar-expand-lg sticky-top gv-navbar">
      <div class="container-fluid gv-navbar-inner">
        <a class="navbar-brand fw-bold" href="/quo/dashboard.php">
            <img src="/quo/assets/img/logo.png" alt="Logo" style="height: 30px; margin-right: 5px;">
            <!--Loewix Quotation-->
        </a>
        <button class="navbar-toggler gv-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav">
          <i class="bi bi-list"></i>
        </button>
        <div class="collapse navbar-collapse" id="main-nav">
          <ul class="navbar-nav gv-nav-menu me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link gv-nav-link <?php if (str_contains($current_page, 'dashboard.php')) echo 'active'; ?>" href="/quo/dashboard.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link gv-nav-link <?php if (str_contains($current_page, 'barang')) echo 'active'; ?>" href="/quo/pages/barang/index.php"><i class="bi bi-box-seam-fill"></i> Data Barang</a></li>
            <li class="nav-item"><a class="nav-link gv-nav-link <?php if (str_contains($current_page, 'customer')) echo 'active'; ?>" href="/quo/pages/customer/index.php"><i class="bi bi-people-fill"></i> Data Customer</a></li>
          </ul>
          
          <?php if ($show_announcement_period): ?>
            <button type="button" class="btn gv-btn-info me-3" data-bs-toggle="modal" data-bs-target="#updateAnnouncementModal">
                <i class="bi bi-megaphone-fill"></i> Info
            </button>
          <?php endif; ?>

          <button type="button" class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
              <span class="toggle-thumb">
                  <i class="bi bi-sun-fill icon-sun"></i>
                  <i class="bi bi-moon-fill icon-moon" style="display:none;"></i>
              </span>
          </button>

          <div class="dropdown">
            <a href="#" class="gv-user-trigger dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="gv-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <span class="gv-avatar"><?php echo strtoupper($user_initials); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end gv-dropdown" aria-labelledby="dropdownUser">
                <li class="gv-dropdown-header">
                    <span class="gv-avatar-lg"><?php echo strtoupper($user_initials); ?></span>
                    <div class="gv-dropdown-user-info">
                        <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                        <small><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'User'); ?></small>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item gv-dropdown-item" href="#"><i class="bi bi-person-fill"></i> Profil Saya</a></li>
                <li><a class="dropdown-item gv-dropdown-item" href="#"><i class="bi bi-gear-fill"></i> Pengaturan</a></li>
                <li class="gv-version-row">
                    <a href="https://quo.grav-tech.com/quo/dashboard.php" class="gv-version-btn active">V 2.0</a>
                    <a href="https://quo.grav-tech.com/listQuotation.php" class="gv-version-btn">V 1.0</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item gv-dropdown-item gv-logout" href="/quo/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
<?php endif; ?>