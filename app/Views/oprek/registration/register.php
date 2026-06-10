<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/browse') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Lab: <strong><?= esc($campaign->lab_name) ?></strong> | TA: <strong><?= esc($campaign->nama_ta) ?></strong>
          <?php if ($campaign->quota): ?>
            | Kuota: <strong><?= esc($campaign->quota) ?></strong>
          <?php endif; ?>
        </div>

        <?php if (session('errors')): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach (session('errors') as $err): ?>
                <li><?= esc($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('oprek/register/' . $campaign->public_id . '/store') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <h5 class="mb-3">Data Diri</h5>
          <div class="form-group">
            <label>Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" value="<?= old('full_name') ?>" required maxlength="100">
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>NIM <span class="text-danger">*</span></label>
                <input type="text" name="nim" class="form-control" value="<?= old('nim') ?>" required maxlength="30">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Program Studi <span class="text-danger">*</span></label>
                <input type="text" name="prodi" class="form-control" value="<?= old('prodi') ?>" required maxlength="100">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Semester <span class="text-danger">*</span></label>
                <input type="number" name="semester" class="form-control" value="<?= old('semester') ?>" required min="1" max="14">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>IPK</label>
                <input type="text" name="ipk" class="form-control" value="<?= old('ipk') ?>" placeholder="Contoh: 3.50">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>No. Telepon <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" value="<?= old('phone') ?>" required maxlength="20">
          </div>

          <div class="form-group">
            <label>Motivasi Mendaftar</label>
            <textarea name="motivation" class="form-control" rows="4" maxlength="2000"><?= old('motivation') ?></textarea>
          </div>

          <hr>
          <h5 class="mb-3">Upload Dokumen <span class="text-danger">*</span></h5>
          <p class="text-muted small">Format: PDF, JPG, PNG | Maks 5MB per file</p>

          <div class="form-group">
            <label>CV <span class="text-danger">*</span></label>
            <input type="file" name="cv" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>

          <div class="form-group">
            <label>KTM (Kartu Tanda Mahasiswa) <span class="text-danger">*</span></label>
            <input type="file" name="ktm" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>

          <div class="form-group">
            <label>KHS (Kartu Hasil Studi) <span class="text-danger">*</span></label>
            <input type="file" name="khs" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>

          <div class="form-group">
            <label>Surat Pernyataan Komitmen <span class="text-danger">*</span></label>
            <input type="file" name="commitment_letter" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>

          <hr>
          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i class="fas fa-paper-plane"></i> Kirim Pendaftaran
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
