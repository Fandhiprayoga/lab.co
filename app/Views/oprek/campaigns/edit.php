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

        <form method="post" action="<?= base_url('oprek/' . $campaign->id . '/update') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Laboratorium <span class="text-danger">*</span></label>
            <select name="lab_id" class="form-control" required>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab['id'] ?>" <?= $campaign->lab_id == $lab['id'] ? 'selected' : '' ?>>
                  <?= esc($lab['name']) ?> (<?= esc($lab['code']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Tahun Akademik <span class="text-danger">*</span></label>
            <select name="academic_year_id" class="form-control" required>
              <?php foreach ($academicYears as $ta): ?>
                <option value="<?= $ta->id ?>" <?= $campaign->academic_year_id == $ta->id ? 'selected' : '' ?>>
                  <?= esc($ta->nama_ta) ?> (<?= esc($ta->kode_ta) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Nama Periode <span class="text-danger">*</span></label>
            <input type="text" name="period_name" class="form-control" value="<?= esc($campaign->period_name) ?>" required maxlength="100">
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="form-control" rows="3"
              placeholder="Deskripsi singkat tentang oprek ini..."><?= esc($campaign->description ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label>Syarat & Ketentuan</label>
            <textarea name="requirements" class="form-control" rows="4"
              placeholder="Persyaratan pendaftaran..."><?= esc($campaign->requirements ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label>Poster</label>
            <?php if (! empty($campaign->poster)): ?>
              <div class="mb-2">
                <img src="<?= base_url($campaign->poster) ?>" alt="Poster" class="img-thumbnail" style="max-height: 200px;">
                <br><small class="text-muted">Upload file baru untuk mengganti poster.</small>
              </div>
            <?php endif; ?>
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
                  value="<?= $campaign->registration_start_at ? date('Y-m-d\TH:i', strtotime($campaign->registration_start_at)) : '' ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal Akhir Pendaftaran</label>
                <input type="datetime-local" name="registration_end_at" class="form-control"
                  value="<?= $campaign->registration_end_at ? date('Y-m-d\TH:i', strtotime($campaign->registration_end_at)) : '' ?>">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Kuota (opsional)</label>
            <input type="number" name="quota" class="form-control" value="<?= esc($campaign->quota) ?>" min="1">
          </div>

          <hr>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Perbarui Oprek
          </button>
          <a href="<?= base_url('oprek/' . $campaign->id) ?>" class="btn btn-light">Batal</a>
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
