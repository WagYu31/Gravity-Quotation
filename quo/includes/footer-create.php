<footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold text-primary">Loewix Quotation</h5>
                    <p>Aplikasi untuk membantu Anda membuat, mengelola, dan melacak penawaran untuk pelanggan dengan lebih efisien.</p>
                </div>
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">Navigasi</h5>
                    <p><a href="/quotation-web/dashboard.php" class="text-white-50" style="text-decoration: none;">Dashboard</a></p>
                    <p><a href="/quotation-web/pages/barang/index.php" class="text-white-50" style="text-decoration: none;">Data Barang</a></p>
                    <p><a href="/quotation-web/pages/customer/index.php" class="text-white-50" style="text-decoration: none;">Data Customer</a></p>
                </div>
                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 fw-bold">Kontak</h5>
                    <p><i class="bi bi-geo-alt-fill me-2"></i> Jakarta, DKI Jakarta, ID</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> dev@grav-tech.com</p>
                    <p><i class="bi bi-telephone-fill me-2"></i> +62 857 1752 9244</p>
                </div>
            </div>
            <hr class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8"><p>Hak Cipta © <?php echo date("Y"); ?> <a href="#"><strong class="text-primary">Loewix Quotation</strong></a>. All Rights Reserved.</p></div>
                <div class="col-md-5 col-lg-4"><div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-facebook"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-twitter-x"></i></a></li>
                        <li class="list-inline-item"><a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="bi bi-instagram"></i></a></li>
                    </ul>
                </div></div>
            </div>
        </div>
    </footer>

    <?php
    // --- LOGIKA KONTROL MODAL (DIPERBAIKI) ---

    // Variabel $show_announcement_period diasumsikan sudah ada dari header.php
    if ($show_announcement_period):
    ?>
    <div class="modal fade" id="updateAnnouncementModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
          <div class="modal-body text-center p-4">
            <i class="bi bi-megaphone-fill text-primary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h4 class="modal-title fw-bold mb-3" id="updateModalLabel">Pembaruan Penting Sistem Quotation</h4>
            <p class="text-muted">Selamat datang di sistem penawaran versi terbaru! Untuk meningkatkan performa dan fitur, kami telah melakukan pembaruan signifikan pada struktur aplikasi.</p>
            <div class="alert alert-info text-start mt-4">
                <h6 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Harap Diperhatikan:</h6>
                <ul class="mb-0 ps-3">
                    <li>Semua penawaran <strong>Baru</strong> kini <strong>WAJIB</strong> dibuat menggunakan <a href="https://quo.grav-tech.com/quo/dashboard.php" target="_blank">Sistem Versi 2.0</a> ini.</li>
                    <li>Riwayat penawaran <strong>sebelum tanggal 14 Juli 2025</strong> masih dapat diakses pada <a href="https://quo.grav-tech.com/listQuotation.php" target="_blank">Sistem Versi 1.0</a>.</li>
                </ul>
            </div>
            <p class="small text-muted mt-3"><strong>NOTE:</strong> Perubahan struktur database yang besar membuat pemindahan data lama tidak memungkinkan untuk saat ini. Terima kasih atas pengertian Anda.</p>
            <button type="button" class="btn btn-primary mt-3" data-bs-dismiss="modal">Saya Mengerti</button>
          </div>
        </div>
      </div>
    </div>
    
    <?php
    
    $show_modal_automatically = !isset($_COOKIE['quotation_update_modal_shown']);
    
    
    if ($show_modal_automatically):
    ?>
    <script>
    
    document.addEventListener('DOMContentLoaded', function() {
        var announcementModalElement = document.getElementById('updateAnnouncementModal');
        if (announcementModalElement) {
            var announcementModal = new bootstrap.Modal(announcementModalElement);
            // Tampilkan modal secara otomatis
            announcementModal.show();
            // Saat modal ditutup, set cookie agar tidak muncul lagi hari ini
            announcementModalElement.addEventListener('hidden.bs.modal', function () {
                const expiryDate = new Date();
                expiryDate.setHours(23, 59, 59, 999);
                document.cookie = "quotation_update_modal_shown=true; expires=" + expiryDate.toUTCString() + "; path=/";
            });
        }
    });
    </script>
    <?php
    endif; 
    endif;
    ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    
    <script src="/quo/assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script src="/quo/assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
    
</body>
</html>