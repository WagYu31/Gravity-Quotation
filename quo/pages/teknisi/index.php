<?php
// Memanggil file-file penting
include '../../includes/db.php';
include '../../includes/header.php';

// Keamanan: Pastikan hanya user yang sudah login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Ambil role dan ID user yang sedang login dari session
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'admin'; // Default ke 'admin' jika tidak ada
$user_name = $_SESSION['user_name'];
// BAGIAN 1: LOGIKA PHP ANDA (TIDAK DIUBAH SAMA SEKALI)
// ========================================================
$jadwal_host = 'localhost';
$jadwal_user = 'u836263092_rootJadwal';
$jadwal_password = 'Eddie@1819';
$jadwal_database = 'u836263092_jadwal';

$jadwal_conn = mysqli_connect($jadwal_host, $jadwal_user, $jadwal_password, $jadwal_database);
if (!$jadwal_conn) {
    die("Koneksi ke database jadwal gagal: " . mysqli_connect_error());
}

if ($user_role == 'superadmin') {
    $query = "SELECT k.id, k.kegiatan, k.jadwal, k.request, k.kode, c.name as customer_name, c.phone_number as customer_phone FROM kegiatan k JOIN customer c ON k.customer_id = c.id WHERE k.deleted_at IS NULL AND c.deleted_at IS NULL AND k.kegiatan != 'service' AND k.kode LIKE 'qqq%' ORDER BY k.jadwal DESC";
} elseif ($user_role == 'admin') {
    // PENTING: Menggunakan prepared statement untuk keamanan
    $query = "SELECT k.id, k.kegiatan, k.jadwal, k.request, k.kode, c.name as customer_name, c.phone_number as customer_phone FROM kegiatan k JOIN customer c ON k.customer_id = c.id WHERE k.deleted_at IS NULL AND c.deleted_at IS NULL AND k.kegiatan != 'service' AND k.kode LIKE 'qqq%' AND k.request = ? ORDER BY k.jadwal DESC";
}

if ($user_role == 'admin') {
    $stmt = mysqli_prepare($jadwal_conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($jadwal_conn, $query);
}

// Catatan: Saya sedikit memperbaiki query Anda agar lebih aman dengan prepared statement.
// Hasilnya akan tetap sama persis.

?>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Data Request (Survey atau Pasang Baru)</h4>
                <a href="req-survey.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Request Baru</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Customer</th>
                                <th>Nomor Telepon</th>
                                <th>Kegiatan</th>
                                <th>Jadwal</th>
                                <th>Request Oleh</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Logika format nomor telepon (tetap sama)
                                    $phone = $row['customer_phone'];
                                    $display_phone = '-';
                                    if (!empty($phone)) {
                                        $sanitized_for_link = preg_replace('/[^0-9]/', '', $phone);
                                        $whatsapp_number = $sanitized_for_link;
                                        if (substr($sanitized_for_link, 0, 1) === '0') {
                                            $whatsapp_number = '62' . substr($sanitized_for_link, 1);
                                        }
                                        $display_phone = htmlspecialchars($phone);
                                        if (substr($sanitized_for_link, 0, 2) === '62') {
                                            $display_phone = '0' . substr($sanitized_for_link, 2);
                                        }
                                        $display_phone = "<a href='https://wa.me/{$whatsapp_number}' target='_blank' class='text-decoration-none text-dark'><i class='bi bi-whatsapp text-success me-1'></i>{$display_phone}</a>";
                                    }

                                    // Logika format tanggal (tetap sama)
                                    $jadwal = $row['jadwal'];
                                    $formatted_jadwal = $jadwal ? date('d M Y, H:i', strtotime($jadwal)) : '-';

                                    // Tampilan baris tabel dengan style baru
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                                    echo '<td>' . $display_phone . '</td>';
                                    echo '<td class="text-capitalize">' . htmlspecialchars($row['kegiatan']) . '</td>';
                                    echo '<td>' . $formatted_jadwal . '</td>';
                                    echo '<td>' . htmlspecialchars($row['request']) . '</td>';
                                    echo '<td class="text-center">';
                                        echo '<div class="btn-group" role="group">';
                                            echo "<a class='btn btn-sm btn-outline-info' href='view-request.php?id={$row['kode']}' title='Lihat Detail'><i class='bi bi-eye-fill'></i></a>";
                                            echo "<a class='btn btn-sm btn-outline-primary' href='proses_pasang.php?" . http_build_query(['id' => $row['id']]) . "' title='Proses Pemasangan'><i class='bi bi-tools'></i></a>";
                                            echo "<button class='btn btn-sm btn-outline-danger delete-btn' data-id='{$row['id']}' title='Hapus'><i class='bi bi-trash-fill'></i></button>";
                                        echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center text-muted py-4'>Tidak ada data request ditemukan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle delete button (logika JS tetap sama)
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                window.location.href = 'delete_kegiatan.php?id=' + id;
            }
        });
    </script>

<?php
mysqli_close($jadwal_conn);
?>


<?php include '../../includes/footer.php'; ?>