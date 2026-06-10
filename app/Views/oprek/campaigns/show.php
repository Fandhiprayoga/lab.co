<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  .status-badge { font-size: 0.85rem; }
  .campaign-shell { background: #f6f8fc; border: 1px solid #e6ebf3; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
  .campaign-header { background: linear-gradient(120deg, #f8fafc, #eef4ff); border-bottom: 1px solid #e3e9f3; }
  .campaign-header .subtitle { color: #6b7280; font-size: 0.85rem; }
  .campaign-stat { border: 1px solid #e8edf6; border-radius: 12px; padding: 14px 16px; background: #ffffff; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06); height: 100%; }
  .campaign-stat .label { color: #6b7280; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; }
  .campaign-stat .value { font-weight: 700; font-size: 1.05rem; }
  .campaign-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #ffffff; border: 1px solid #e2e8f0; font-size: 0.82rem; }
  .tab-shell { border: 1px solid #e6ebf3; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
  .tab-shell .card-header { background: #f9fbff; border-bottom: 1px solid #e8edf6; }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <!-- Campaign Info -->
    <div class="card campaign-shell">
      <div class="card-header campaign-header d-flex align-items-center justify-content-between">
        <div>
          <h4 class="mb-1"><?= esc($campaign->period_name) ?></h4>
          <div class="subtitle">Ringkasan periode oprek dan progres pendaftar.</div>
        </div>
        <div class="card-header-action">
          <?php if ($campaign->status === 'draft'): ?>
            <a href="<?= base_url('oprek/' . $campaign->id . '/edit') ?>" class="btn btn-info btn-sm">
              <i class="fas fa-edit"></i> Edit
            </a>
            <button class="btn btn-success btn-sm js-publish-btn" data-id="<?= $campaign->id ?>">
              <i class="fas fa-paper-plane"></i> Publikasikan
            </button>
          <?php endif; ?>
          <?php if ($campaign->status === 'published'): ?>
            <a href="<?= base_url('oprek/' . $campaign->id . '/components') ?>" class="btn btn-warning btn-sm">
              <i class="fas fa-cogs"></i> Komponen Seleksi
            </a>
            <a href="<?= base_url('oprek/' . $campaign->id . '/scoring') ?>" class="btn btn-info btn-sm">
              <i class="fas fa-star"></i> Penilaian
            </a>
            <button class="btn btn-secondary btn-sm js-close-btn" data-id="<?= $campaign->id ?>">
              <i class="fas fa-lock"></i> Tutup
            </button>
          <?php endif; ?>
          <?php if ($campaign->status === 'closed'): ?>
            <a href="<?= base_url('oprek/' . $campaign->id . '/finalize') ?>" class="btn btn-success btn-sm">
              <i class="fas fa-check-double"></i> Verifikasi Akhir
            </a>
          <?php endif; ?>
          <a href="<?= base_url('oprek') ?>" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <div class="campaign-stat">
              <div class="label">Lab</div>
              <div class="value"><?= esc($campaign->lab_name) ?></div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="campaign-stat">
              <div class="label">Tahun Akademik</div>
              <div class="value"><?= esc($campaign->nama_ta) ?></div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="campaign-stat">
              <div class="label">Kuota</div>
              <div class="value"><?= $campaign->quota ? esc($campaign->quota) : 'Tidak dibatasi' ?></div>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="campaign-stat">
              <div class="label">Status</div>
              <?php $badge = match($campaign->status) { 'draft' => 'badge-secondary', 'published' => 'badge-success', 'closed' => 'badge-warning', 'archived' => 'badge-dark', default => 'badge-light' }; ?>
              <div class="value"><span class="badge <?= $badge ?> status-badge"><?= esc($campaign->status) ?></span></div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap align-items-center">
          <span class="campaign-chip mr-2 mb-2"><i class="fas fa-calendar-alt"></i>
            <?= $campaign->registration_start_at ? date('d M Y H:i', strtotime($campaign->registration_start_at)) : '-' ?>
            &mdash;
            <?= $campaign->registration_end_at ? date('d M Y H:i', strtotime($campaign->registration_end_at)) : '-' ?>
          </span>
          <span class="campaign-chip mb-2"><i class="fas fa-users"></i> Total Pendaftar: <?= count($applications) ?></span>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card tab-shell">
      <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tab-applications">Pendaftar (<?= count($applications) ?>)</a>
          </li>
          <?php if (activeGroupCan('oprek.manage')): ?>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-components">Komponen Seleksi (<?= count($components) ?>)</a>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-logs">Log Aktivitas</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <!-- Tab: Applications -->
          <div class="tab-pane fade show active" id="tab-applications">
            <?php if (empty($applications)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>Belum ada pendaftar.</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-striped table-sm" id="table-applications">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>NIM/NIK</th>
                      <th>Nama</th>
                      <th>Daftar</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($applications as $i => $app): ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td><?= esc($app->nim_nik ?? '-') ?></td>
                      <td><?= esc($app->username) ?></td>
                      <td><?= date('d/m/Y', strtotime($app->submitted_at)) ?></td>
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
                          'activated' => 'badge-info',
                          default => 'badge-light',
                        };
                        ?>
                        <span class="badge <?= $sBadge ?>"><?= esc($app->application_status) ?></span>
                      </td>
                      <td>
                        <?php if (activeGroupCan('oprek.manage') && in_array($app->application_status, ['submitted', 'doc_revision'])): ?>
                          <a href="<?= base_url('oprek/verify/' . $app->public_id . '/documents') ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-check"></i> Verifikasi
                          </a>
                        <?php endif; ?>
                        <?php if (activeGroupCan('oprek.scoring') && in_array($app->application_status, ['doc_verified', 'in_selection'])): ?>
                          <a href="<?= base_url('oprek/scoring/' . $app->public_id) ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-star"></i> Nilai
                          </a>
                        <?php endif; ?>
                        <?php if (activeGroupCan('oprek.manage') && $app->application_status === 'onboarding_pending'): ?>
                          <a href="<?= base_url('oprek/onboarding/' . $app->public_id . '/verify') ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-check-circle"></i> Verifikasi Onboarding
                          </a>
                        <?php endif; ?>
                        <?php if (activeGroupCan('oprek.manage') && $app->application_status === 'onboarding_complete'): ?>
                          <a href="<?= base_url('oprek/activate/' . $app->public_id) ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-user-graduate"></i> Aktivasi
                          </a>
                        <?php endif; ?>
                        <?php if ($app->application_status === 'activated'): ?>
                          <span class="badge badge-info"><i class="fas fa-check"></i> Aktif</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- Tab: Components -->
          <?php if (activeGroupCan('oprek.manage')): ?>
          <div class="tab-pane fade" id="tab-components">
            <?php if (empty($components)): ?>
              <div class="text-center py-4 text-muted">
                <i class="fas fa-cogs fa-2x mb-2"></i>
                <p>Belum ada komponen seleksi.</p>
                <a href="<?= base_url('oprek/' . $campaign->id . '/components') ?>" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus"></i> Atur Komponen
                </a>
              </div>
            <?php else: ?>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>#</th><th>Komponen</th><th>Bobot</th><th>Nilai Maks</th><th>Wajib</th><th>Aktif</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($components as $i => $comp): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($comp->component_name) ?></td>
                    <td><?= $comp->weight_percentage ?>%</td>
                    <td><?= $comp->max_score ?></td>
                    <td><?= $comp->is_required ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>' ?></td>
                    <td><?= $comp->is_active ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>' ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <a href="<?= base_url('oprek/' . $campaign->id . '/components') ?>" class="btn btn-sm btn-primary mt-2">Kelola Komponen</a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Tab: Activity Logs -->
          <div class="tab-pane fade" id="tab-logs">
            <?php if (empty($activityLogs)): ?>
              <p class="text-muted">Belum ada aktivitas.</p>
            <?php else: ?>
              <table class="table table-sm">
                <thead>
                  <tr><th>Waktu</th><th>User</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($activityLogs as $log): ?>
                  <tr>
                    <td><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></td>
                    <td><?= esc($log->username) ?></td>
                    <td><code><?= esc($log->action_type) ?></code></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
  $('#table-applications').DataTable({ pageLength: 25, language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' } });

  $('.js-publish-btn').click(function() {
    if (!confirm('Publikasikan oprek ini?')) return;
    var id = $(this).data('id');
    $.post('<?= base_url('oprek') ?>/' + id + '/publish', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'})
      .done(function() { location.reload(); })
      .fail(function() { alert('Gagal publikasi.'); });
  });

  $('.js-close-btn').click(function() {
    if (!confirm('Tutup oprek ini?')) return;
    var id = $(this).data('id');
    $.post('<?= base_url('oprek') ?>/' + id + '/close', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'})
      .done(function() { location.reload(); })
      .fail(function() { alert('Gagal menutup oprek.'); });
  });
});
</script>
<?= $this->endSection() ?>
