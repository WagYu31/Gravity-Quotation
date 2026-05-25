<?php
// 1. Selalu mulai sesi di awal untuk bisa mengaksesnya
session_start();

// 2. Hapus semua variabel yang tersimpan di dalam sesi
session_unset();

// 3. Hancurkan sesi itu sendiri secara permanen
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login dengan pesan sukses (opsional)
header('Location: login.php?status=logout_success');

// 5. Hentikan eksekusi skrip lebih lanjut untuk memastikan tidak ada kode lain yang berjalan
exit();
?>