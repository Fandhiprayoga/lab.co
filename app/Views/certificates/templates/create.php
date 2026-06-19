<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('certificates/templates/store') ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nama Template <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required maxlength="200"
                   value="<?= old('name') ?>" placeholder="Contoh: Sertifikat Peserta PKL">
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Deskripsi singkat template ini"><?= old('description') ?></textarea>
          </div>
          <div class="form-group">
            <label>Orientasi Halaman</label>
            <select name="page_orientation" class="form-control">
              <option value="landscape" <?= old('page_orientation') === 'landscape' ? 'selected' : '' ?>>Landscape (Horizontal)</option>
              <option value="portrait" <?= old('page_orientation') === 'portrait' ? 'selected' : '' ?>>Portrait (Vertikal)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Background Sertifikat (JPG/PNG/WebP, max 5MB)</label>
            <input type="file" name="background" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
            <small class="text-muted">Gambar background akan ditampilkan sebagai dasar sertifikat. Kosongkan jika tanpa background.</small>
          </div>
          <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Template</button>
            <a href="<?= base_url('certificates/templates') ?>" class="btn btn-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
