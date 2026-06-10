<?= $this->section('css') ?>
<style>
.application-shell { background: #f6f8fc; border: 1px solid #e6ebf3; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
.application-header { background: linear-gradient(120deg, #f8fafc, #eef4ff); border-bottom: 1px solid #e3e9f3; }
.application-header .subtitle { color: #6b7280; font-size: 0.85rem; }
.status-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; border-radius: 12px; border: 1px solid #e6ebf3; background: #ffffff; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06); }
.status-left { display: flex; align-items: center; gap: 14px; }
.status-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-weight: 600; font-size: 0.85rem; }
.status-success .status-icon, .status-success .status-pill { background: #e8f7ee; color: #157347; border: 1px solid #cfe9da; }
.status-danger .status-icon, .status-danger .status-pill { background: #fde8ea; color: #b02a37; border: 1px solid #f5c6cb; }
.status-warning .status-icon, .status-warning .status-pill { background: #fff4da; color: #b26a00; border: 1px solid #ffe0a3; }
.status-info .status-icon, .status-info .status-pill { background: #e9f2ff; color: #1f4b99; border: 1px solid #cfe0ff; }
.status-label { color: #6b7280; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; }
.status-value { font-weight: 700; font-size: 1.1rem; }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card application-shell">
      <div class="card-header application-header d-flex align-items-center justify-content-between">
        <div>
          <h4 class="mb-1"><?= esc($page_title) ?></h4>
          <div class="subtitle">Pantau status pendaftaran dan detail berkas Anda.</div>
        </div>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/my-applications') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php
        $sLabel = match ($application->application_status) {
          'submitted' => 'Menunggu Verifikasi',
          'doc_revision' => 'Revisi Berkas',
          'doc_rejected' => 'Berkas Ditolak',
          'doc_verified' => 'Berkas Terverifikasi',
          'in_selection' => 'Tahap Seleksi',
          'failed' => 'Tidak Lolos',
          'accepted' => 'Diterima',
          'onboarding_pending' => 'Menunggu Onboarding',
          'onboarding_complete' => 'Onboarding Selesai',
          default => $application->application_status,
        };
        $statusTone = in_array($application->application_status, ['accepted', 'onboarding_complete']) ? 'success' :
          (in_array($application->application_status, ['failed', 'doc_rejected']) ? 'danger' :
          (in_array($application->application_status, ['doc_revision', 'onboarding_pending']) ? 'warning' : 'info'));
        $statusIcon = match ($statusTone) {
          'success' => 'fa-check-circle',
          'danger' => 'fa-times-circle',
          'warning' => 'fa-exclamation-triangle',
          default => 'fa-info-circle',
        };
        ?>
        <div class="status-card status-<?= $statusTone ?>">
          <div class="status-left">
            <span class="status-icon"><i class="fas <?= $statusIcon ?>"></i></span>
            <div>
              <div class="status-label">Status Pendaftaran</div>
              <div class="status-value"><?= esc($sLabel) ?></div>
            </div>
          </div>
          <span class="status-pill"><i class="fas <?= $statusIcon ?>"></i> <?= esc($sLabel) ?></span>
        </div>

        <!-- Form Data -->
        <?php $form = json_decode($application->form_payload ?? '{}'); ?>
        <h5>Data Pendaftaran</h5>
        <table class="table table-sm table-bordered">
          <tr><th width="150">Nama</th><td><?= esc($form->full_name ?? '-') ?></td></tr>
          <tr><th>NIM</th><td><?= esc($form->nim ?? '-') ?></td></tr>
          <tr><th>Prodi</th><td><?= esc($form->prodi ?? '-') ?></td></tr>
          <tr><th>Semester</th><td><?= esc($form->semester ?? '-') ?></td></tr>
          <tr><th>IPK</th><td><?= esc($form->ipk ?? '-') ?></td></tr>
          <tr><th>Telepon</th><td><?= esc($form->phone ?? '-') ?></td></tr>
          <?php if (! empty($form->motivation)): ?>
          <tr><th>Motivasi</th><td><?= nl2br(esc($form->motivation)) ?></td></tr>
          <?php endif; ?>
        </table>

        <!-- Documents -->
        <h5 class="mt-4">Dokumen</h5>
        <?php if (empty($documents)): ?>
          <p class="text-muted">Tidak ada dokumen.</p>
        <?php else: ?>
          <table class="table table-sm">
            <thead>
              <tr><th>Jenis</th><th>File</th><th>Verifikasi</th><th>Catatan</th></tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $doc): ?>
              <tr>
                <td><?= esc(ucfirst(str_replace('_', ' ', $doc->document_type))) ?></td>
                <td>
                  <a href="<?= base_url($doc->file_path) ?>" target="_blank"><?= esc($doc->file_name) ?></a>
                  <small class="text-muted">(<?= round($doc->file_size / 1024, 1) ?> KB)</small>
                </td>
                <td>
                  <?php if ($doc->is_verified): ?>
                    <span class="badge badge-success">Terverifikasi</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Belum</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?= esc($doc->verification_note) ?>
                  <?php if ($application->application_status === 'doc_revision' && in_array($doc->document_type, ['cv', 'ktm', 'khs', 'commitment_letter'])): ?>
                    <br><a href="<?= base_url('oprek/my-applications/' . $application->public_id . '/revise/' . $doc->document_type) ?>" class="btn btn-xs btn-warning mt-1">Upload Ulang</a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <?php if ($application->application_status === 'accepted' || $application->application_status === 'onboarding_pending'): ?>
          <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle"></i> Selamat! Anda dinyatakan <strong>diterima</strong>.
            Silakan lengkapi data onboarding.
            <a href="<?= base_url('oprek/onboarding/' . $application->public_id) ?>" class="btn btn-sm btn-success ml-2">
              Lengkapi Data <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        <?php endif; ?>

        <!-- Activity Logs -->
        <?php if (! empty($logs)): ?>
        <h5 class="mt-4">Riwayat Aktivitas</h5>
        <ul class="list-group list-group-flush">
          <?php foreach ($logs as $log): ?>
          <li class="list-group-item small py-1">
            <span class="text-muted"><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></span> -
            <code><?= esc($log->action_type) ?></code>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
