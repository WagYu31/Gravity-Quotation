<?php if ($quote['progress_status'] === 'INVOICE'): ?>
    <p>Semua tahapan telah selesai. Tentukan status akhir untuk penawaran ini.</p>
    <form class="progress-form d-inline-block me-2">
        <input type="hidden" name="action" value="update_progress">
        <input type="hidden" name="step" value="selesai">
        <input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <input type="hidden" name="final_status" value="COMPLETED">
        <button type="submit" class="btn btn-success">Tandai Selesai (Diterima)</button>
    </form>
    <form class="progress-form d-inline-block">
        <input type="hidden" name="action" value="update_progress">
        <input type="hidden" name="step" value="selesai">
        <input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <input type="hidden" name="final_status" value="CANCELED">
        <button type="submit" class="btn btn-danger">Tandai Gagal (Ditolak)</button>
    </form>
<?php else: ?>
    <p>Status pengerjaan saat ini: <strong><?php echo $quote['progress_status']; ?></strong></p>
<?php endif; ?>