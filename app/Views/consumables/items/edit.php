<?php
$item       = $item ?? [];
$labs       = $labs ?? [];
$categories = $categories ?? [];
$units      = $units ?? [];
?>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/consumables/items/' . (int)($item['id'] ?? 0) . '/update') ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Nama Bahan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= old('name', esc($item['name'] ?? '')) ?>" required maxlength="200">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Laboratorium <span class="text-danger">*</span></label>
            <select name="lab_id" class="form-control" required>
              <option value="">— Pilih Lab —</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab['id'] ?>" <?= old('lab_id', $item['lab_id']) == $lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" class="form-control">
              <option value="">— Tanpa Kategori —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= old('category_id', $item['category_id']) == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Satuan</label>
            <select name="unit_id" class="form-control">
              <option value="">— Pilih Satuan —</option>
              <?php foreach ($units as $unit): ?>
                <option value="<?= $unit['id'] ?>" <?= old('unit_id', $item['unit_id']) == $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?> (<?= esc($unit['symbol']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Lokasi Penyimpanan</label>
            <input type="text" name="location" class="form-control" value="<?= old('location', esc($item['location'] ?? '')) ?>" placeholder="Contoh: Rak A-3">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Batas Min. Stok</label>
            <input type="number" step="0.01" min="0" name="min_stock" class="form-control" value="<?= old('min_stock', number_format((float)($item['min_stock'] ?? 0), 2)) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Tanggal Kedaluwarsa</label>
            <input type="date" name="expiry_date" class="form-control" value="<?= old('expiry_date', esc($item['expiry_date'] ?? '')) ?>">
          </div>
        </div>
      </div>
      <div class="alert alert-info py-2 px-3">
        <i class="fas fa-info-circle mr-1"></i>
        Perubahan jumlah stok dilakukan melalui <a href="<?= site_url('consumables/adjustments/' . (int)($item['id'] ?? 0) . '/create') ?>">Penyesuaian Stok</a>, bukan di form ini.
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <textarea name="notes" class="form-control" rows="2"><?= old('notes', esc($item['notes'] ?? '')) ?></textarea>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="requiresApproval" name="requires_approval" value="1"
                   <?= old('requires_approval', $item['requires_approval'] ?? 0) ? 'checked' : '' ?>>
            <label class="custom-control-label" for="requiresApproval">Memerlukan persetujuan Kepala Lab</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1"
                   <?= old('is_active', $item['is_active'] ?? 1) ? 'checked' : '' ?>>
            <label class="custom-control-label" for="isActive">Aktif</label>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between">
        <a href="<?= site_url('admin/consumables/items') ?>" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Perbarui</button>
      </div>
    </form>
  </div>
</div>
