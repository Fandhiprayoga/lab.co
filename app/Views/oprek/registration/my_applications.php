<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/browse') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Cari Oprek
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($applications)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>Anda belum mendaftar di oprek manapun.</p>
            <a href="<?= base_url('oprek/browse') ?>" class="btn btn-primary">Lihat Oprek Aktif</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Oprek</th>
                  <th>Lab</th>
                  <th>Daftar Pada</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                  <td><?= esc($app->period_name) ?></td>
                  <td><?= esc($app->lab_name) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($app->submitted_at)) ?></td>
                  <td>
                    <?php
                    $sBadge = match ($app->application_status) {
                      'submitted' => 'badge-info',
                      'doc_revision' => 'badge-warning',
                      'doc_rejected' => 'badge-danger',
                      'doc_verified' => 'badge-primary',
                      'in_selection' => 'badge-info',
                      'failed' => 'badge-danger',
                      'accepted' => 'badge-success',
                      'onboarding_pending' => 'badge-warning',
                      'onboarding_complete' => 'badge-success',
                      default => 'badge-light',
                    };
                    $sLabel = match ($app->application_status) {
                      'submitted' => 'Menunggu Verifikasi',
                      'doc_revision' => 'Revisi Berkas',
                      'doc_rejected' => 'Berkas Ditolak',
                      'doc_verified' => 'Berkas Terverifikasi',
                      'in_selection' => 'Tahap Seleksi',
                      'failed' => 'Tidak Lolos',
                      'accepted' => 'Diterima',
                      'onboarding_pending' => 'Menunggu Onboarding',
                      'onboarding_complete' => 'Onboarding Selesai',
                      default => $app->application_status,
                    };
                    ?>
                    <span class="badge <?= $sBadge ?>"><?= esc($sLabel) ?></span>
                  </td>
                  <td>
                    <a href="<?= base_url('oprek/my-applications/' . $app->public_id) ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> Detail
                    </a>
                    <?php if ($app->application_status === 'accepted' || $app->application_status === 'onboarding_pending'): ?>
                      <a href="<?= base_url('oprek/onboarding/' . $app->public_id) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-upload"></i> Upload Kelengkapan
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
