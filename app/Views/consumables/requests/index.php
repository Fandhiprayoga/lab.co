<?php
$requests  = $requests ?? [];

$statusMap = [
    'draft'            => ['label' => 'Draft',              'badge' => 'badge-secondary', 'icon' => 'fa-file-alt'],
    'waiting_approval' => ['label' => 'Menunggu Persetujuan','badge' => 'badge-warning',   'icon' => 'fa-clock'],
    'approved'         => ['label' => 'Disetujui',           'badge' => 'badge-success',   'icon' => 'fa-check-circle'],
    'rejected'         => ['label' => 'Ditolak',             'badge' => 'badge-danger',    'icon' => 'fa-times-circle'],
    'disbursed'        => ['label' => 'Dikeluarkan',         'badge' => 'badge-info',      'icon' => 'fa-box-open'],
    'completed'        => ['label' => 'Selesai',             'badge' => 'badge-primary',   'icon' => 'fa-check-double'],
    'canceled'         => ['label' => 'Dibatalkan',          'badge' => 'badge-dark',      'icon' => 'fa-ban'],
    'problematic'      => ['label' => 'Bermasalah',          'badge' => 'badge-danger',    'icon' => 'fa-exclamation-triangle'],
];

$counts = ['all' => count($requests), 'active' => 0, 'completed' => 0, 'closed' => 0];
foreach ($requests as $r) {
    if (in_array($r['status'], ['draft', 'waiting_approval', 'approved', 'disbursed'])) $counts['active']++;
    elseif ($r['status'] === 'completed') $counts['completed']++;
    elseif (in_array($r['status'], ['rejected', 'canceled'])) $counts['closed']++;
}
?>

<!-- Summary cards -->
<div class="row mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-clipboard-list"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total</h4></div>
        <div class="card-body"><?= $counts['all'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Aktif</h4></div>
        <div class="card-body"><?= $counts['active'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check-double"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Selesai</h4></div>
        <div class="card-body"><?= $counts['completed'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-ban"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Ditolak/Batal</h4></div>
        <div class="card-body"><?= $counts['closed'] ?></div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <span></span>
  <?php if (activeGroupCan('bhp.request.create')): ?>
    <a href="<?= site_url('consumables/requests/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Buat Permintaan
    </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Kode</th>
            <th>Lab</th>
            <th>Tujuan</th>
            <th>Jadwal</th>
            <th class="text-center">Status</th>
            <th>Dibuat</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($requests)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada permintaan.</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $r):
              $si = $statusMap[$r['status']] ?? ['label' => $r['status'], 'badge' => 'badge-secondary', 'icon' => 'fa-question'];
            ?>
            <tr>
              <td><code><?= esc($r['request_code']) ?></code></td>
              <td><?= esc($r['lab_name'] ?? '—') ?></td>
              <td><?= esc(mb_strimwidth($r['purpose'] ?? '', 0, 60, '…')) ?></td>
              <td><?= $r['scheduled_date'] ? esc($r['scheduled_date']) : '<span class="text-muted">—</span>' ?></td>
              <td class="text-center">
                <span class="badge <?= $si['badge'] ?>">
                  <i class="fas <?= $si['icon'] ?> mr-1"></i><?= $si['label'] ?>
                </span>
              </td>
              <td><?= esc($r['created_at'] ?? '') ?></td>
              <td class="text-right">
                <a href="<?= site_url('consumables/requests/' . $r['id']) ?>" class="btn btn-xs btn-primary">Detail</a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
