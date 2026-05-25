<?php
include '../../includes/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../login.php'); exit();
}
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT q.*, c.name as customer_name FROM quotations q JOIN customer c ON q.customer_id = c.id WHERE q.id = ? AND q.deleted_at IS NULL");
$stmt->bind_param("i", $id);
$stmt->execute();
$quote = $stmt->get_result()->fetch_assoc();
if (!$quote) die("Penawaran tidak ditemukan.");

$steps = ['APPROVED', 'SO', 'SJ', 'BAST', 'INVOICE'];
$step_labels = ['Disetujui', 'Sales Order', 'Surat Jalan', 'BAST', 'Invoice', 'Selesai'];
$current_step_index = array_search($quote['progress_status'], $steps);
if($current_step_index === false) {
    if(in_array($quote['progress_status'], ['COMPLETED', 'CANCELED'])) {
        $current_step_index = count($steps);
    } else {
        $current_step_index = -1;
    }
}
?>

<style>
    .progress-stepper { display: flex; justify-content: space-between; width: 100%; position: relative; margin: 20px 0 40px 0; }
    .progress-stepper::before { content: ''; position: absolute; top: 50%; left: 0; transform: translateY(-50%); height: 2px; width: 100%; background-color: #e0e0e0; z-index: 1; }
    .progress-stepper .step { display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; z-index: 2; width: 120px; }
    .step-icon { width: 30px; height: 30px; border-radius: 50%; background-color: white; color: #9e9e9e; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #e0e0e0; transition: all 0.3s ease; }
    .step-label { margin-top: 8px; font-size: 12px; color: #9e9e9e; }
    .step.completed .step-icon { background-color: #198754; border-color: #198754; color: white; }
    .step.completed .step-label { color: #198754; font-weight: bold; }
    .step.current .step-icon { border-color: #0d6efd; color: #0d6efd; }
    .step.current .step-label { color: #0d6efd; font-weight: bold; }
    .step.canceled .step-icon { background-color: #dc3545; border-color: #dc3545; color: white; }
    .step.canceled .step-label { color: #dc3545; }
    .card-body dl { margin-bottom: 0; }
    .card-body dt { font-weight: bold; color: #6c757d; }
    .card-body dd { margin-left: 0; }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Progress Penawaran #<?php echo htmlspecialchars($quote['quotation_code']); ?></h2>
            <p class="text-muted mb-0">Untuk: <?php echo htmlspecialchars($quote['customer_name']); ?> | <a href="print.php?id=<?php echo $id; ?>" target="_blank">Lihat Detail Penawaran</a></p>
        </div>
        <a href="../../dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
    </div>

    <div class="progress-stepper">
        <?php foreach ($step_labels as $i => $label): 
            $status_class = '';
            if ($i < $current_step_index) $status_class = 'completed';
            if ($i == $current_step_index) $status_class = 'current';
            if ($quote['progress_status'] == 'COMPLETED') $status_class = 'completed';
            if ($quote['progress_status'] == 'CANCELED') $status_class = 'canceled';
        ?>
            <div class="step <?php echo $status_class; ?>">
                <div class="step-icon"><?php echo ($status_class == 'completed' && $quote['progress_status'] != 'CANCELED') ? '⭐' : $i + 1; ?></div>
                <div class="step-label"><?php echo $label; ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
        <?php 
        $tahapan = [ 'so' => 'Sales Order (SO)', 'sj' => 'Surat Jalan (SJ)', 'bast' => 'BAST', 'invoice' => 'Invoice', 'selesai' => 'Penyelesaian' ];
        $is_previous_step_done = true; 
        foreach ($tahapan as $key => $title):
            $is_current_step_done = !empty($quote[$key.'_number']) || ($key == 'selesai' && in_array($quote['progress_status'], ['COMPLETED', 'CANCELED']));
            $is_active = ($is_previous_step_done && !$is_current_step_done);
        ?>
            <div class="card mb-3">
                <div class="card-header fw-bold <?php echo ($is_current_step_done ? 'text-success' : ($is_active ? 'text-primary' : '')); ?>">
                    <?php 
                        if ($is_current_step_done) echo '✅ ';
                        elseif ($is_active) echo '✏️ ';
                        else echo '🔒 ';
                        echo $title; 
                    ?>
                </div>
                <div class="card-body">
                    <?php if ($is_previous_step_done || $is_current_step_done): ?>
                        <?php include "progress_forms/{$key}_form.php"; ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Selesaikan tahap sebelumnya terlebih dahulu.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php $is_previous_step_done = $is_current_step_done; endforeach; ?>
        </div>
    </div>
</div>

<script>
// Listener untuk form submit (menyimpan data)
document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('progress-form')) {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('button[type="submit"]');
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
        
        const formData = new FormData(form);
        fetch('actions.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Data berhasil disimpan!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                button.disabled = false;
                button.innerHTML = originalButtonText;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Terjadi kesalahan. Cek console untuk detail.');
            button.disabled = false;
            button.innerHTML = originalButtonText;
        });
    }
});

// Listener untuk tombol "Ubah" dan "Batal"
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('toggle-edit-btn')) {
        // Cari parent terdekat yaitu .card-body
        const cardBody = e.target.closest('.card-body');
        if (cardBody) {
            const viewMode = cardBody.querySelector('.view-mode');
            const editMode = cardBody.querySelector('.edit-mode');
            
            // Toggle tampilan antara mode view dan mode edit
            viewMode.style.display = viewMode.style.display === 'none' ? '' : 'none';
            editMode.style.display = editMode.style.display === 'none' ? '' : 'none';
        }
    }
});
</script>
<?php include '../../includes/footer.php'; ?>