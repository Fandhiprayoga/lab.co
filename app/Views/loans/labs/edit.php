<?php $lab = $lab ?? []; ?>
<?php $logoUrl = ! empty($lab['logo']) ? base_url($lab['logo']) : base_url('assets/img/stisla-fill.svg'); ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Edit Lab</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/labs/update/' . (int) ($lab['id'] ?? 0)) ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group text-center">
            <img src="<?= $logoUrl ?>" alt="logo lab" class="img-thumbnail mb-2" style="width: 120px; height: 120px; object-fit: contain;">
            <small class="d-block text-muted">Jika belum ada logo, sistem menampilkan placeholder default.</small>
          </div>

          <div class="form-group">
            <label for="name">Nama Lab/Ruangan</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name', $lab['name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="code">Kode Lab</label>
            <input type="text" id="code" name="code" class="form-control" value="<?= old('code', $lab['code'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="4" maxlength="1000"><?= old('description', $lab['description'] ?? '') ?></textarea>
            <small class="form-text text-muted">Opsional. Maksimal 1000 karakter.</small>
          </div>

          <div class="form-group">
            <label for="location">Lokasi</label>
            <input type="text" id="location" name="location" class="form-control" value="<?= old('location', $lab['location'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="capacity">Kapasitas</label>
            <input type="number" id="capacity" name="capacity" min="0" class="form-control" value="<?= old('capacity', $lab['capacity'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="condition_status">Status Kondisi Lab</label>
            <?php $originalCondition = (string) ($lab['condition_status'] ?? 'baik'); ?>
            <?php $selectedCondition = old('condition_status', $originalCondition); ?>
            <select id="condition_status" name="condition_status" class="form-control" data-original="<?= esc($originalCondition) ?>" required>
              <option value="baik" <?= $selectedCondition === 'baik' ? 'selected' : '' ?>>Baik</option>
              <option value="perlu_perbaikan" <?= $selectedCondition === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
              <option value="rusak" <?= $selectedCondition === 'rusak' ? 'selected' : '' ?>>Rusak</option>
            </select>
          </div>

          <div class="form-group" id="condition-reason-wrap" style="display: none;">
            <label for="condition_reason">Alasan Perubahan Kondisi <span class="text-danger">*</span></label>
            <textarea id="condition_reason" name="condition_reason" class="form-control" rows="3" maxlength="500" placeholder="Wajib diisi saat status kondisi diubah, minimal 5 karakter."><?= old('condition_reason') ?></textarea>
            <small class="form-text text-muted">Riwayat perubahan dan alasannya akan dicatat ke menu Riwayat Kondisi.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_loanable" name="is_loanable" value="1" <?= old('is_loanable', (string) ($lab['is_loanable'] ?? '1')) === '1' ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_loanable">Status Boleh Dipinjam</label>
            </div>
            <small class="form-text text-muted">Jika kondisi lab rusak, sistem otomatis menonaktifkan status boleh dipinjam.</small>
          </div>

          <div class="form-group">
            <label for="lab_logo">Ganti Foto/Logo Lab</label>
            <input type="file" id="lab_logo" name="lab_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="form-text text-muted">Opsional. Format: PNG/JPG/WEBP/SVG, maksimal 2 MB. Untuk PNG/JPG/WEBP sistem akan crop/resize otomatis ke rasio 1:1.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', (string) ($lab['is_active'] ?? '1')) === '1' ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Lab Aktif</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/labs') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var sel  = document.getElementById('condition_status');
    var wrap = document.getElementById('condition-reason-wrap');
    var ta   = document.getElementById('condition_reason');
    if (!sel || !wrap) return;
    var original = sel.getAttribute('data-original') || '';

    function sync() {
      var changed = sel.value !== original;
      wrap.style.display = changed ? '' : 'none';
      if (ta) ta.required = changed;
    }
    sel.addEventListener('change', sync);
    sync();
  })();
</script>
