<?php
$bhpRequest   = $bhpRequest ?? [];
$requestItems = $requestItems ?? [];
$status       = $bhpRequest['status'] ?? '';
$requestId    = (int)($bhpRequest['id'] ?? 0);

$statusMap = [
    'draft'            => ['label' => 'Draft',               'class' => 'secondary', 'icon' => 'fa-file-alt'],
    'waiting_approval' => ['label' => 'Menunggu Persetujuan', 'class' => 'warning',   'icon' => 'fa-clock'],
    'approved'         => ['label' => 'Disetujui',            'class' => 'success',   'icon' => 'fa-check-circle'],
    'rejected'         => ['label' => 'Ditolak',              'class' => 'danger',    'icon' => 'fa-times-circle'],
    'disbursed'        => ['label' => 'Dikeluarkan',          'class' => 'info',      'icon' => 'fa-box-open'],
    'completed'        => ['label' => 'Selesai',              'class' => 'primary',   'icon' => 'fa-check-double'],
    'canceled'         => ['label' => 'Dibatalkan',           'class' => 'dark',      'icon' => 'fa-ban'],
    'problematic'      => ['label' => 'Bermasalah',           'class' => 'danger',    'icon' => 'fa-exclamation-triangle'],
];
$si = $statusMap[$status] ?? ['label' => $status, 'class' => 'secondary', 'icon' => 'fa-question'];

$canCancel  = in_array($status, ['draft', 'waiting_approval']);
$canSubmit  = $status === 'draft';
$canApprove = $status === 'waiting_approval' && activeGroupCan('bhp.approval');
$canDisburse= $status === 'approved' && activeGroupCan('bhp.disburse');
$canRealize = $status === 'disbursed' && activeGroupCan('bhp.realize');
?>

<div class="row">
  <!-- Informasi permintaan -->
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-flask mr-2 text-primary"></i><?= esc($bhpRequest['request_code'] ?? '') ?></h5>
        <span class="badge badge-<?= $si['class'] ?> px-3 py-2">
          <i class="fas <?= $si['icon'] ?> mr-1"></i><?= $si['label'] ?>
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Pemohon</dt>
          <dd class="col-sm-8"><?= esc($bhpRequest['requester_name'] ?? '—') ?></dd>
          <dt class="col-sm-4">Laboratorium</dt>
          <dd class="col-sm-8"><?= esc($bhpRequest['lab_name'] ?? '—') ?></dd>
          <dt class="col-sm-4">Tujuan</dt>
          <dd class="col-sm-8"><?= nl2br(esc($bhpRequest['purpose'] ?? '')) ?></dd>
          <dt class="col-sm-4">Jadwal Penggunaan</dt>
          <dd class="col-sm-8"><?= $bhpRequest['scheduled_date'] ? esc($bhpRequest['scheduled_date']) : '<span class="text-muted">—</span>' ?></dd>
          <?php if ($bhpRequest['approval_note']): ?>
          <dt class="col-sm-4">Catatan Approval</dt>
          <dd class="col-sm-8"><?= esc($bhpRequest['approval_note']) ?> <small class="text-muted">(<?= esc($bhpRequest['approver_name'] ?? '') ?>)</small></dd>
          <?php endif; ?>
          <?php if ($bhpRequest['canceled_reason']): ?>
          <dt class="col-sm-4">Alasan Pembatalan</dt>
          <dd class="col-sm-8 text-danger"><?= esc($bhpRequest['canceled_reason']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <!-- Daftar item -->
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Daftar Bahan</h6></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th>Bahan</th>
              <th class="text-right">Diminta</th>
              <th class="text-right">Disetujui</th>
              <th class="text-right">Aktual</th>
              <th>Satuan</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($requestItems)): ?>
              <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada item.</td></tr>
            <?php else: ?>
              <?php foreach ($requestItems as $ri): ?>
              <tr>
                <td><?= esc($ri['item_name'] ?? '—') ?></td>
                <td class="text-right"><?= number_format((float)$ri['qty_requested'], 2) ?></td>
                <td class="text-right"><?= $ri['qty_approved'] !== null ? number_format((float)$ri['qty_approved'], 2) : '<span class="text-muted">—</span>' ?></td>
                <td class="text-right"><?= $ri['qty_actual'] !== null ? number_format((float)$ri['qty_actual'], 2) : '<span class="text-muted">—</span>' ?></td>
                <td><?= esc($ri['unit_symbol'] ?? '') ?></td>
                <td><?= esc($ri['notes'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Panel aksi -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h6 class="mb-0">Aksi</h6></div>
      <div class="card-body">

        <?php if ($canSubmit): ?>
        <form method="post" action="<?= site_url('consumables/requests/' . $requestId . '/submit') ?>" class="mb-2">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-success btn-block">
            <i class="fas fa-paper-plane mr-1"></i> Kirim untuk Persetujuan
          </button>
        </form>
        <?php endif; ?>

        <?php if ($canApprove): ?>
        <form method="post" action="<?= site_url('consumables/requests/' . $requestId . '/approve') ?>" class="mb-2">
          <?= csrf_field() ?>
          <div class="mb-2">
            <?php foreach ($requestItems as $ri): ?>
            <div class="form-group mb-1">
              <label class="small"><?= esc($ri['item_name'] ?? '') ?> — Qty Disetujui (<?= esc($ri['unit_symbol'] ?? '') ?>)</label>
              <input type="number" step="0.01" min="0" name="qty_approved[<?= $ri['id'] ?>]"
                     class="form-control form-control-sm"
                     value="<?= number_format((float)$ri['qty_requested'], 2) ?>">
            </div>
            <?php endforeach; ?>
          </div>
          <div class="form-group">
            <textarea name="approval_note" class="form-control form-control-sm" rows="2" placeholder="Catatan (opsional)"></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-block mb-1">
            <i class="fas fa-check mr-1"></i> Setujui
          </button>
        </form>
        <form method="post" action="<?= site_url('consumables/requests/' . $requestId . '/reject') ?>" class="mb-2">
          <?= csrf_field() ?>
          <div class="form-group">
            <textarea name="approval_note" class="form-control form-control-sm" rows="2" placeholder="Alasan penolakan" required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-block">
            <i class="fas fa-times mr-1"></i> Tolak
          </button>
        </form>
        <?php endif; ?>

        <?php if ($canDisburse): ?>
        <form method="post" action="<?= site_url('consumables/requests/' . $requestId . '/disburse') ?>" class="mb-2"
              onsubmit="return confirm('Keluarkan bahan dari stok sekarang?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-box-open mr-1"></i> Keluarkan Bahan
          </button>
        </form>
        <?php endif; ?>

        <?php if ($canRealize): ?>
        <a href="<?= site_url('consumables/requests/' . $requestId . '/realize') ?>" class="btn btn-info btn-block mb-2">
          <i class="fas fa-clipboard-check mr-1"></i> Catat Realisasi
        </a>
        <?php endif; ?>

        <?php if ($canCancel): ?>
        <button class="btn btn-outline-danger btn-block btn-sm" data-toggle="modal" data-target="#cancelModal">
          <i class="fas fa-ban mr-1"></i> Batalkan Permintaan
        </button>
        <?php endif; ?>

        <hr>
        <a href="<?= site_url('consumables/requests') ?>" class="btn btn-light btn-block btn-sm">
          <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal cancel -->
<?php if ($canCancel): ?>
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= site_url('consumables/requests/' . $requestId . '/cancel') ?>">
        <?= csrf_field() ?>
        <div class="modal-header"><h5 class="modal-title">Batalkan Permintaan</h5></div>
        <div class="modal-body">
          <div class="form-group mb-0">
            <label>Alasan Pembatalan</label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Opsional"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-danger">Batalkan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
