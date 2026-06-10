<?= $this->section('css') ?>
<style>
.onboarding-shell { background: #f6f8fc; border: 1px solid #e6ebf3; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
.onboarding-header { background: linear-gradient(120deg, #f8fafc, #eef4ff); border-bottom: 1px solid #e3e9f3; }
.onboarding-upload-card { border: 1px solid #e8edf6; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06); }
.onboarding-upload-card .card-header { background: #f9fbff; border-bottom: 1px solid #e8edf6; }
.upload-meta { color: #6b7280; font-size: 0.85rem; }
.upload-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #ffffff; border: 1px solid #e2e8f0; font-size: 0.82rem; }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card onboarding-shell">
      <div class="card-header onboarding-header d-flex align-items-center justify-content-between">
        <div>
          <h4 class="mb-1"><?= esc($page_title) ?></h4>
          <div class="upload-meta">Lengkapi data onboarding dan unggah dokumen pendukung.</div>
        </div>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/my-applications') ?>" class="btn btn-secondary btn-sm">
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

        <?php if ($profile && $profile->onboarding_status === 'verified'): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Data onboarding Anda telah diverifikasi. Status: <strong>Selesai</strong>
          </div>
        <?php elseif ($profile && $profile->onboarding_status === 'revision'): ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Mohon perbaiki data onboarding Anda.
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Silakan lengkapi data berikut untuk onboarding sebagai asisten lab.
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('oprek/onboarding/' . $application->public_id . '/store') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <h5>Data Rekening</h5>
          <div class="form-group">
            <label>Nama Bank <span class="text-danger">*</span></label>
            <input type="text" name="bank_name" class="form-control" value="<?= esc($profile->bank_name ?? '') ?>" required maxlength="100" placeholder="Contoh: BNI, BRI, Mandiri">
          </div>

          <div class="form-group">
            <label>Nomor Rekening <span class="text-danger">*</span></label>
            <input type="text" name="bank_account_number" class="form-control" value="<?= esc($profile->bank_account_number ?? '') ?>" required maxlength="30">
          </div>

          <div class="form-group">
            <label>Nama Pemilik Rekening <span class="text-danger">*</span></label>
            <input type="text" name="bank_account_name" class="form-control" value="<?= esc($profile->bank_account_name ?? '') ?>" required maxlength="100">
          </div>

          <div class="card onboarding-upload-card mt-4">
            <div class="card-header">
              <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                  <h5 class="mb-1">Dokumen</h5>
                  <div class="upload-meta">Format: PDF, JPG, PNG | Maks 5MB per file</div>
                </div>
                <span class="upload-chip ml-md-auto"><i class="fas fa-cloud-upload-alt"></i> Upload Kelengkapan</span>
              </div>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label>Tanda Tangan Digital</label>
                <?php
                $sigDoc = null;
                foreach ($documents as $d) { if ($d->document_type === 'signature') { $sigDoc = $d; break; } }
                ?>
                <?php if ($sigDoc): ?>
                  <div class="mb-2">
                    <a href="<?= base_url($sigDoc->file_path) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                      <i class="fas fa-file-image"></i> <?= esc($sigDoc->file_name) ?>
                    </a>
                  </div>
                <?php endif; ?>
                <input type="file" name="signature_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
              </div>

              <div class="form-group">
                <label>Halaman Depan Buku Tabungan</label>
                <?php
                $passbookDoc = null;
                foreach ($documents as $d) { if ($d->document_type === 'passbook_front') { $passbookDoc = $d; break; } }
                ?>
                <?php if ($passbookDoc): ?>
                  <div class="mb-2">
                    <a href="<?= base_url($passbookDoc->file_path) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                      <i class="fas fa-file"></i> <?= esc($passbookDoc->file_name) ?>
                    </a>
                  </div>
                <?php endif; ?>
                <input type="file" name="passbook_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
              </div>
            </div>
          </div>

          <hr>
          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i class="fas fa-save"></i> Simpan Data Onboarding
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
