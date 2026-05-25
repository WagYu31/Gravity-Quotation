<?php if (!empty($quote['so_number'])): ?>
    <div class="view-mode">
        <dl class="row">
            <dt class="col-sm-4">No. SO</dt><dd class="col-sm-8"><?php echo htmlspecialchars($quote['so_number']); ?></dd>
            <dt class="col-sm-4">Tanggal Pengerjaan</dt><dd class="col-sm-8"><?php echo date('d M Y', strtotime($quote['so_start_date'])); ?></dd>
            <dt class="col-sm-4">Alamat Instalasi</dt><dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($quote['so_instalasi_address'])); ?></dd>
            <dt class="col-sm-4">Contact Person</dt><dd class="col-sm-8"><?php echo htmlspecialchars($quote['so_contact_person']); ?></dd>
        </dl>
        <button type="button" class="btn btn-sm btn-outline-secondary toggle-edit-btn">Ubah Data SO</button>
    </div>
    <form class="progress-form edit-mode" style="display:none;">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="so"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. SO</label><input type="text" name="so_number" class="form-control" value="<?php echo htmlspecialchars($quote['so_number']); ?>" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal Pengerjaan</label><input type="date" name="so_start_date" class="form-control" value="<?php echo htmlspecialchars($quote['so_start_date']); ?>" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Alamat Instalasi</label><textarea name="so_instalasi_address" class="form-control" required><?php echo htmlspecialchars($quote['so_instalasi_address']); ?></textarea></div>
        <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" name="so_contact_person" class="form-control" value="<?php echo htmlspecialchars($quote['so_contact_person']); ?>" required></div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button> <button type="button" class="btn btn-light toggle-edit-btn">Batal</button>
    </form>
<?php else: ?>
    <form class="progress-form">
        <input type="hidden" name="action" value="update_progress"><input type="hidden" name="step" value="so"><input type="hidden" name="id" value="<?php echo $quote['id']; ?>">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">No. SO</label><input type="text" name="so_number" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Tanggal Pengerjaan</label><input type="date" name="so_start_date" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Alamat Instalasi</label><textarea name="so_instalasi_address" class="form-control" required></textarea></div>
        <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" name="so_contact_person" class="form-control" required></div>
        <button type="submit" class="btn btn-primary">Simpan SO</button>
    </form>
<?php endif; ?>