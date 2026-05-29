<?php $faculty = $faculty ?? []; ?>
<?php $logoUrl = ! empty($faculty['logo']) ? base_url($faculty['logo']) : base_url('assets/img/stisla-fill.svg'); ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Edit Fakultas</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/faculties/update/' . (int) ($faculty['id'] ?? 0)) ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group text-center">
            <img src="<?= $logoUrl ?>" alt="logo fakultas" class="img-thumbnail mb-2" style="width: 120px; height: 120px; object-fit: contain;">
            <small class="d-block text-muted">Jika belum ada logo, sistem menampilkan placeholder default.</small>
            <?php if (! empty($faculty['logo'])): ?>
              <form action="<?= base_url('admin/loans/faculties/delete-logo/' . (int) ($faculty['id'] ?? 0)) ?>" method="post" class="mt-2 js-swal-delete-form"
                    data-swal-title="Hapus logo fakultas?"
                    data-swal-text="Logo fakultas akan dihapus dan diganti placeholder default."
                    data-swal-confirm="Ya, hapus"
                    data-swal-cancel="Batal">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus Logo</button>
              </form>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="name">Nama Fakultas</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name', $faculty['name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="code">Kode Fakultas</label>
            <input type="text" id="code" name="code" class="form-control" value="<?= old('code', $faculty['code'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= old('description', $faculty['description'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="faculty_logo">Ganti Foto/Logo Fakultas</label>
            <input type="file" id="faculty_logo" name="faculty_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="form-text text-muted">Opsional. Format: PNG/JPG/WEBP/SVG, maksimal 2 MB. Untuk PNG/JPG/WEBP sistem akan crop/resize otomatis ke rasio 1:1.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', (string) ($faculty['is_active'] ?? '1')) === '1' ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Fakultas Aktif</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/faculties') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
