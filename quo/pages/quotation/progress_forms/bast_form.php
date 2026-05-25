<?php if (!empty($quote['bast_number'])): ?>
    <div class="view-mode">
        <dl class="row">
            <dt class="col-sm-4">No. BAST</dt><dd class="col-sm-8"><?php echo htmlspecialchars($quote['bast_number']); ?></dd>
            <dt class="col-sm-4">Tanggal BAST</dt><dd class="col-sm-8"><?php echo date('d M Y', strtotime($quote['bast_date'])); ?></dd>
            <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($quote['bast_notes'])); ?></dd>
        </dl>
        <button type="button" class="btn btn-sm btn-outline-secondary toggle-edit-btn">Ubah Data BAST</button>
    </div>
    <form class="progress-form edit-mode" style="display:none;">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="bast"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. BAST</label><input type="text" name="bast_number" class="form-control" value="<?php echo htmlspecialchars($quote['bast_number']); ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal BAST</label><input type="date" name="bast_date" class="form-control" value="<?php echo htmlspecialchars($quote['bast_date']); ?>" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="bast_notes" class="form-control"><?php echo htmlspecialchars($quote['bast_notes']); ?></textarea></div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button> <button type="button" class="btn btn-light toggle-edit-btn">Batal</button>
    </form>
<?php else: ?>
    <form class="progress-form">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="bast"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. BAST</label><input type="text" name="bast_number" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal BAST</label><input type="date" name="bast_date" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="bast_notes" class="form-control"></textarea></div>
        <button type="submit" class="btn btn-primary">Simpan BAST</button>
    </form>
<?php endif; ?>