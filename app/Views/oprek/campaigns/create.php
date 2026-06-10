<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (session('errors')): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach (session('errors') as $err): ?>
                <li><?= esc($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('oprek/store') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Laboratorium <span class="text-danger">*</span></label>
            <select name="lab_id" class="form-control" required>
              <option value="">-- Pilih Lab --</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab['id'] ?>" <?= old('lab_id') == $lab['id'] ? 'selected' : '' ?>>
                  <?= esc($lab['name']) ?> (<?= esc($lab['code']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Tahun Akademik</label>
            <input type="text" class="form-control" value="<?= esc($activeYear->nama_ta) ?> (<?= esc($activeYear->kode_ta) ?>)" readonly disabled>
            <small class="text-muted">Menggunakan tahun akademik yang sedang aktif.</small>
          </div>

          <div class="form-group">
            <label>Nama Periode <span class="text-danger">*</span></label>
            <input type="text" name="period_name" class="form-control" value="<?= old('period_name') ?>"
              placeholder="Contoh: Gelombang 1 Semester Ganjil 2025/2026" required maxlength="100">
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"
              placeholder="Deskripsi singkat tentang oprek ini..."><?= old('description') ?></textarea>
          </div>

          <div class="form-group">
            <label>Syarat & Ketentuan</label>
            <textarea name="requirements" class="form-control" rows="4"
              placeholder="Persyaratan pendaftaran (contoh: IPK minimal 3.0, semester 3+, dsb)..."><?= old('requirements') ?></textarea>
          </div>

          <div class="form-group">
            <label>Poster (opsional)</label>
            <div class="custom-file">
              <input type="file" name="poster" class="custom-file-input" id="posterInput" accept="image/*">
              <label class="custom-file-label" for="posterInput">Pilih gambar poster...</label>
            </div>
            <small class="text-muted">Format: JPG, PNG, WEBP. Maks 2MB.</small>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal Mulai Pendaftaran</label>
                <input type="datetime-local" name="registration_start_at" class="form-control"
                  value="<?= old('registration_start_at') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal Akhir Pendaftaran</label>
                <input type="datetime-local" name="registration_end_at" class="form-control"
                  value="<?= old('registration_end_at') ?>">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Kuota (opsional)</label>
            <input type="number" name="quota" class="form-control" value="<?= old('quota') ?>" min="1"
              placeholder="Kosongkan jika tidak ada batasan">
          </div>

          <hr>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Oprek
          </button>
          <a href="<?= base_url('oprek') ?>" class="btn btn-light">Batal</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script>
$('.custom-file-input').on('change', function() {
  var fileName = $(this).val().split('\\').pop();
  $(this).next('.custom-file-label').html(fileName || 'Pilih gambar poster...');
});
</script>
<?= $this->endSection() ?>
