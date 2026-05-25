<?php if (!empty($quote['sj_number'])): ?>
    <div class="view-mode">
        <dl class="row">
            <dt class="col-sm-4">No. Surat Jalan</dt><dd class="col-sm-8"><?php echo htmlspecialchars($quote['sj_number']); ?></dd>
            <dt class="col-sm-4">Tanggal Surat Jalan</dt><dd class="col-sm-8"><?php echo date('d M Y', strtotime($quote['sj_date'])); ?></dd>
        </dl>
        <button type="button" class="btn btn-sm btn-outline-secondary toggle-edit-btn">Ubah Data SJ</button>
    </div>
    <form class="progress-form edit-mode" style="display:none;">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="sj"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. Surat Jalan</label><input type="text" name="sj_number" class="form-control" value="<?php echo htmlspecialchars($quote['sj_number']); ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal Surat Jalan</label><input type="date" name="sj_date" class="form-control" value="<?php echo htmlspecialchars($quote['sj_date']); ?>" required></div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button> <button type="button" class="btn btn-light toggle-edit-btn">Batal</button>
    </form>
<?php else: ?>
    <form class="progress-form">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="sj"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. Surat Jalan</label><input type="text" name="sj_number" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal Surat Jalan</label><input type="date" name="sj_date" class="form-control" required></div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan SJ</button>
    </form>
<?php endif; ?>