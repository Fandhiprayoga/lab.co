<?php
$proposal       = $proposal ?? [];
$items          = $items ?? [];
$actorNames     = $actorNames ?? [];
$proposalStatus = $proposal['status'] ?? '-';
$loanType       = $proposal['loan_type'] ?? 'equipment';
$isEquipment    = $loanType === 'equipment';
$proposalId     = (int) ($proposal['id'] ?? 0);

$accentColor  = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg     = $isEquipment ? 'rgba(79,195,247,.1)'  : 'rgba(129,199,132,.1)';
$accentBorder = $isEquipment ? '#4fc3f7'              : '#81c784';
$typeLabel    = $isEquipment ? 'Alat'                 : 'Laboratorium';
$typeIcon     = $isEquipment ? 'fa-tools'             : 'fa-door-open';

$statusMap = [
    'draft'      => ['label' => 'Draft',            'class' => 'secondary', 'icon' => 'fa-file-alt'],
    'waiting_l1' => ['label' => 'Menunggu Laboran',  'class' => 'warning',   'icon' => 'fa-clock'],
    'waiting_l2' => ['label' => 'Menunggu Ka.Lab',   'class' => 'info',      'icon' => 'fa-user-check'],
    'approved'   => ['label' => 'Disetujui',          'class' => 'success',   'icon' => 'fa-check-circle'],
    'rejected'   => ['label' => 'Ditolak',            'class' => 'danger',    'icon' => 'fa-times-circle'],
    'canceled'   => ['label' => 'Dibatalkan',         'class' => 'dark',      'icon' => 'fa-ban'],
];
$statusInfo = $statusMap[$proposalStatus] ?? ['label' => $proposalStatus, 'class' => 'secondary', 'icon' => 'fa-question'];

// Build timeline from proposal fields
$timeline = [];

if (! empty($proposal['created_at'])) {
    $timeline[] = [
        'time'   => $proposal['created_at'],
        'label'  => 'Proposal Dibuat',
        'detail' => 'Draft dibuat oleh ' . esc($proposal['proposer_name'] ?? '-'),
        'remark' => '',
        'color'  => 'secondary',
        'icon'   => 'fa-file-alt',
    ];
}

if (! empty($proposal['submitted_at'])) {
    $timeline[] = [
        'time'   => $proposal['submitted_at'],
        'label'  => 'Proposal Diajukan',
        'detail' => 'Diajukan untuk persetujuan oleh ' . esc($proposal['proposer_name'] ?? '-'),
        'remark' => '',
        'color'  => 'primary',
        'icon'   => 'fa-paper-plane',
    ];
}

if (! empty($proposal['approval_l1_at'])) {
    $l1Actor    = $actorNames[(int)($proposal['approval_l1_by'] ?? 0)] ?? 'Laboran';
    $isL1Reject = (! empty($proposal['rejected_reason']) && empty($proposal['approval_l2_at']));
    $timeline[] = [
        'time'   => $proposal['approval_l1_at'],
        'label'  => $isL1Reject ? 'Ditolak oleh Laboran' : 'Disetujui oleh Laboran',
        'detail' => esc($l1Actor) . (! empty($proposal['approval_l1_note']) ? ': "' . esc($proposal['approval_l1_note']) . '"' : ''),
        'remark' => $isL1Reject ? ($proposal['rejected_reason'] ?? '') : '',
        'color'  => $isL1Reject ? 'danger' : 'success',
        'icon'   => $isL1Reject ? 'fa-times-circle' : 'fa-check-circle',
    ];
}

if (! empty($proposal['approval_l2_at'])) {
    $l2Actor    = $actorNames[(int)($proposal['approval_l2_by'] ?? 0)] ?? 'Kepala Lab';
    $isL2Reject = (! empty($proposal['rejected_reason']));
    $timeline[] = [
        'time'   => $proposal['approval_l2_at'],
        'label'  => $isL2Reject ? 'Ditolak oleh Kepala Lab' : 'Disetujui oleh Kepala Lab',
        'detail' => esc($l2Actor) . (! empty($proposal['approval_l2_note']) ? ': "' . esc($proposal['approval_l2_note']) . '"' : ''),
        'remark' => $isL2Reject ? ($proposal['rejected_reason'] ?? '') : '',
        'color'  => $isL2Reject ? 'danger' : 'success',
        'icon'   => $isL2Reject ? 'fa-times-circle' : 'fa-check-circle',
    ];
}

if (! empty($proposal['canceled_at'])) {
    $cancelActor = isset($actorNames[(int)($proposal['canceled_by'] ?? 0)])
        ? $actorNames[(int)$proposal['canceled_by']]
        : esc($proposal['proposer_name'] ?? '-');
    $timeline[] = [
        'time'   => $proposal['canceled_at'],
        'label'  => 'Proposal Dibatalkan',
        'detail' => 'Dibatalkan oleh ' . $cancelActor,
        'remark' => $proposal['cancel_reason'] ?? '',
        'color'  => 'dark',
        'icon'   => 'fa-ban',
    ];
}

usort($timeline, fn($a, $b) => strcmp($a['time'], $b['time']));

$dotColors = [
    'secondary' => '#6c757d', 'primary' => '#6777ef', 'success' => '#28a745',
    'danger'    => '#dc3545', 'warning' => '#ffc107', 'info'    => '#17a2b8',
    'dark'      => '#343a40',
];
$fmtDt = fn(?string $dt) => $dt ? date('d M Y, H:i', strtotime($dt)) : '-';
$fmtD  = fn(?string $dt) => $dt ? date('d M Y', strtotime($dt)) : '-';
?>

<?php /* ── STEP WIZARD ── */ ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-center">
      <div class="d-flex align-items-center flex-shrink-0">
        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success text-white"
             style="width:32px;height:32px;font-size:.8rem;flex-shrink:0">
          <i class="fas fa-check"></i>
        </div>
        <div class="ml-2">
          <div class="font-weight-bold small text-success">Step 1</div>
          <div class="text-muted" style="font-size:.72rem">Info Proposal</div>
        </div>
      </div>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#28a745"></div>
      <div class="d-flex align-items-center flex-shrink-0">
        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success text-white"
             style="width:32px;height:32px;font-size:.8rem;flex-shrink:0">
          <i class="fas fa-check"></i>
        </div>
        <div class="ml-2">
          <div class="font-weight-bold small text-success">Step 2</div>
          <div class="text-muted" style="font-size:.72rem">Pilih <?= $typeLabel ?></div>
        </div>
      </div>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#28a745"></div>
      <div class="d-flex align-items-center flex-shrink-0">
        <div class="d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold"
             style="width:32px;height:32px;font-size:.82rem;background:#6777ef;flex-shrink:0">3</div>
        <div class="ml-2">
          <div class="font-weight-bold small" style="color:#6777ef">Step 3</div>
          <div class="text-muted" style="font-size:.72rem">Kirim &amp; Approval</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php /* ── PROPOSAL HEADER CARD ── */ ?>
<div class="card border-0 shadow-sm mb-4" style="border-left:4px solid <?= $accentColor ?>!important">
  <div class="card-body py-3 px-4">
    <div class="row align-items-center">
      <div class="col-md-8">
        <div class="d-flex align-items-center">
          <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
               style="width:44px;height:44px;background:<?= $accentBg ?>">
            <i class="fas <?= $typeIcon ?> fa-lg" style="color:<?= $accentColor ?>"></i>
          </div>
          <div>
            <h5 class="mb-0 font-weight-bold"><?= esc($proposal['title'] ?? '-') ?></h5>
            <span class="text-muted small mr-2"><?= esc($proposal['proposal_code'] ?? '-') ?></span>
            <span class="badge badge-<?= $isEquipment ? 'primary' : 'success' ?>">
              <i class="fas <?= $typeIcon ?> mr-1"></i><?= $typeLabel ?>
            </span>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-right mt-2 mt-md-0">
        <span class="badge badge-<?= $statusInfo['class'] ?> px-3 py-2" style="font-size:.85rem">
          <i class="fas <?= $statusInfo['icon'] ?> mr-1"></i><?= $statusInfo['label'] ?>
        </span>
        <div class="text-muted small mt-1">
          <i class="fas fa-user mr-1"></i><?= esc($proposal['proposer_name'] ?? '-') ?>
          &nbsp;&bull;&nbsp;
          <i class="fas fa-calendar-alt mr-1"></i><?= $fmtD($proposal['created_at'] ?? null) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php /* ── MAIN ROW ── */ ?>
<div class="row">

  <?php /* ── LEFT: Tabs ── */ ?>
  <div class="col-lg-8 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white px-4 pb-0 pt-3">
        <ul class="nav nav-tabs card-header-tabs" id="proposalTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#pane-detail" role="tab">
              <i class="fas fa-info-circle mr-1"></i>Detail
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-history" role="tab">
              <i class="fas fa-history mr-1"></i>Riwayat
              <span class="badge badge-light ml-1"><?= count($timeline) ?></span>
            </a>
          </li>
        </ul>
      </div>

      <div class="tab-content">

        <?php /* ── TAB: Detail ── */ ?>
        <div class="tab-pane fade show active p-4" id="pane-detail" role="tabpanel">

          <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:.7rem;letter-spacing:.08em">
            Informasi Proposal
          </h6>
          <div class="row small mb-4">
            <div class="col-sm-6 mb-3">
              <div class="text-muted mb-1">Pengusul</div>
              <div class="font-weight-semibold"><?= esc($proposal['proposer_name'] ?? '-') ?></div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="text-muted mb-1">Tipe Peminjaman</div>
              <span class="badge badge-<?= $isEquipment ? 'primary' : 'success' ?>">
                <i class="fas <?= $typeIcon ?> mr-1"></i><?= $typeLabel ?>
              </span>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="text-muted mb-1"><i class="fas fa-calendar-check mr-1 text-success"></i>Mulai</div>
              <div><?= $fmtDt($proposal['start_at'] ?? null) ?></div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="text-muted mb-1"><i class="fas fa-calendar-times mr-1 text-danger"></i>Selesai</div>
              <div><?= $fmtDt($proposal['end_at'] ?? null) ?></div>
            </div>
            <div class="col-12">
              <div class="text-muted mb-1">Tujuan / Deskripsi</div>
              <div class="p-3 rounded" style="background:#f8f9fa;font-size:.88rem;line-height:1.7">
                <?= nl2br(esc($proposal['objective'] ?? '-')) ?>
              </div>
            </div>
          </div>

          <hr class="my-0 mb-4">

          <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:.7rem;letter-spacing:.08em">
            Item <?= $typeLabel ?> dalam Proposal
            <span class="badge badge-light border ml-1"><?= count($items) ?></span>
          </h6>

          <?php if (empty($items)): ?>
          <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity:.3"></i>
            Tidak ada item dalam proposal ini.
          </div>
          <?php else: ?>
          <div class="list-group list-group-flush rounded" style="border:1px solid #e9ecef">
            <?php foreach ($items as $idx => $item): ?>
            <div class="list-group-item py-3">
              <div class="d-flex align-items-center">
                <div class="d-flex align-items-center justify-content-center rounded-circle text-white flex-shrink-0 mr-3"
                     style="width:28px;height:28px;font-size:.75rem;font-weight:700;background:<?= $accentColor ?>">
                  <?= $idx + 1 ?>
                </div>
                <div class="flex-grow-1">
                  <div class="small font-weight-semibold">
                    <?= esc($isEquipment ? ($item['equipment_name'] ?? '-') : ($item['lab_name'] ?? '-')) ?>
                  </div>
                  <div class="text-muted" style="font-size:.75rem">
                    <?php if ($isEquipment): ?>
                      <span class="badge badge-light border">Qty: <?= (int)$item['qty'] ?></span>
                    <?php endif; ?>
                    <?php if (! empty($item['note'])): ?>
                      <span class="ml-1"><?= esc($item['note']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </div><!-- /pane-detail -->

        <?php /* ── TAB: Riwayat ── */ ?>
        <div class="tab-pane fade p-4" id="pane-history" role="tabpanel">

          <h6 class="text-muted text-uppercase font-weight-bold mb-4" style="font-size:.7rem;letter-spacing:.08em">
            Riwayat Status &amp; Approval
          </h6>

          <?php if (empty($timeline)): ?>
          <div class="text-center py-4 text-muted">
            <i class="fas fa-history fa-2x mb-2 d-block" style="opacity:.3"></i>Belum ada riwayat.
          </div>
          <?php else: ?>
          <div style="position:relative;padding-left:2.5rem">
            <?php foreach ($timeline as $i => $ev):
              $isLast  = ($i === count($timeline) - 1);
              $dotClr  = $dotColors[$ev['color']] ?? '#6c757d';
            ?>
            <div style="position:relative;margin-bottom:<?= $isLast ? '0' : '1.75rem' ?>">

              <?php /* vertical connector */ ?>
              <?php if (! $isLast): ?>
              <div style="position:absolute;left:-1.75rem;top:1.75rem;bottom:-1.75rem;width:2px;background:#e9ecef;z-index:0"></div>
              <?php endif; ?>

              <?php /* icon dot */ ?>
              <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                   style="position:absolute;left:-2.5rem;top:.2rem;width:1.5rem;height:1.5rem;font-size:.6rem;
                          background:<?= $dotClr ?>;z-index:1;box-shadow:0 0 0 3px #fff,0 0 0 4px <?= $dotClr ?>3a">
                <i class="fas <?= $ev['icon'] ?>"></i>
              </div>

              <div class="rounded p-3" style="background:#f8f9fa;border-left:3px solid <?= $dotClr ?>">
                <div class="d-flex align-items-start justify-content-between">
                  <span class="font-weight-bold small"><?= $ev['label'] ?></span>
                  <span class="text-muted flex-shrink-0 ml-2" style="font-size:.72rem;white-space:nowrap">
                    <?= $fmtDt($ev['time']) ?>
                  </span>
                </div>
                <?php if (! empty($ev['detail'])): ?>
                <div class="text-muted small mt-1"><?= $ev['detail'] ?></div>
                <?php endif; ?>
                <?php if (! empty($ev['remark'])): ?>
                <div class="mt-2 p-2 rounded small" style="background:#fff5f5;border-left:3px solid #dc3545">
                  <div class="font-weight-bold text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">
                    Alasan / Catatan
                  </div>
                  <div class="text-danger"><?= esc($ev['remark']) ?></div>
                </div>
                <?php endif; ?>
              </div>

            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </div><!-- /pane-history -->

      </div><!-- /tab-content -->
    </div>
  </div>

  <?php /* ── RIGHT: Action Panel ── */ ?>
  <div class="col-lg-4">

    <?php /* Rejection alert */ ?>
    <?php if ($proposalStatus === 'rejected' && ! empty($proposal['rejected_reason'])): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
      <div class="font-weight-bold mb-1 small">
        <i class="fas fa-times-circle mr-1"></i>Alasan Penolakan
      </div>
      <div style="font-size:.88rem"><?= esc($proposal['rejected_reason']) ?></div>
    </div>
    <?php endif; ?>

    <?php /* Cancellation alert */ ?>
    <?php if ($proposalStatus === 'canceled' && ! empty($proposal['cancel_reason'])): ?>
    <div class="alert alert-secondary border-0 shadow-sm mb-3" role="alert">
      <div class="font-weight-bold mb-1 small">
        <i class="fas fa-ban mr-1"></i>Alasan Pembatalan
      </div>
      <div style="font-size:.88rem"><?= esc($proposal['cancel_reason']) ?></div>
    </div>
    <?php endif; ?>

    <?php /* Approved summary card */ ?>
    <?php if ($proposalStatus === 'approved'): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-top:3px solid #28a745!important">
      <div class="card-body p-3 text-center">
        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
        <div class="font-weight-bold text-success">Proposal Disetujui</div>
        <?php if (! empty($proposal['approval_l1_at'])): ?>
        <div class="text-muted small mt-1">
          L1: <?= $fmtD($proposal['approval_l1_at']) ?>
          <?php if (! empty($proposal['approval_l2_at'])): ?>
            &bull; L2: <?= $fmtD($proposal['approval_l2_at']) ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (! empty($proposal['approval_l1_note']) || ! empty($proposal['approval_l2_note'])): ?>
        <div class="mt-2 text-left p-2 rounded" style="background:#f1f8f1;font-size:.78rem">
          <?php if (! empty($proposal['approval_l1_note'])): ?>
          <div class="text-muted"><strong>Laboran:</strong> <?= esc($proposal['approval_l1_note']) ?></div>
          <?php endif; ?>
          <?php if (! empty($proposal['approval_l2_note'])): ?>
          <div class="text-muted mt-1"><strong>Ka.Lab:</strong> <?= esc($proposal['approval_l2_note']) ?></div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Waiting notice */ ?>
    <?php if (in_array($proposalStatus, ['waiting_l1', 'waiting_l2'])): ?>
    <div class="card border-0 shadow-sm mb-3" style="background:#fffde7;border-left:3px solid #ffc107!important">
      <div class="card-body p-3">
        <div class="font-weight-bold small mb-1" style="color:#856404">
          <i class="fas fa-hourglass-half mr-1"></i>Menunggu Persetujuan
        </div>
        <div class="text-muted small">
          <?= $proposalStatus === 'waiting_l1'
              ? 'Proposal sedang menunggu persetujuan Laboran.'
              : 'Sudah disetujui Laboran. Menunggu persetujuan Kepala Lab.' ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php /* L1 Approval panel */ ?>
    <?php if (activeGroupCan('lending.approval.l1') && $proposalStatus === 'waiting_l1'): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-top:3px solid #28a745!important">
      <div class="card-header bg-white py-2 px-3">
        <h6 class="mb-0 font-weight-bold small">
          <i class="fas fa-user-check mr-2 text-success"></i>Approval Laboran
        </h6>
      </div>
      <div class="card-body p-3">
        <form action="<?= base_url('loans/' . $proposalId . '/approve-l1') ?>" method="post" class="mb-3">
          <?= csrf_field() ?>
          <div class="form-group mb-2">
            <label class="small text-muted">Catatan (opsional)</label>
            <input type="text" name="approval_l1_note" class="form-control form-control-sm"
                   placeholder="Tambahkan catatan...">
          </div>
          <button type="submit" class="btn btn-success btn-sm btn-block">
            <i class="fas fa-check mr-1"></i>Setujui Proposal
          </button>
        </form>
        <div class="divider text-center mb-3"><span class="bg-white px-2 text-muted small">atau</span><hr class="mt-n3"></div>
        <form action="<?= base_url('loans/' . $proposalId . '/reject-l1') ?>" method="post">
          <?= csrf_field() ?>
          <div class="form-group mb-2">
            <label class="small text-muted">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="rejected_reason" class="form-control form-control-sm" rows="3"
                      placeholder="Jelaskan alasan penolakan..." required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-sm btn-block">
            <i class="fas fa-times mr-1"></i>Tolak Proposal
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php /* L2 Approval panel */ ?>
    <?php if (activeGroupCan('lending.approval.l2') && $proposalStatus === 'waiting_l2'): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-top:3px solid #17a2b8!important">
      <div class="card-header bg-white py-2 px-3">
        <h6 class="mb-0 font-weight-bold small">
          <i class="fas fa-user-shield mr-2 text-info"></i>Approval Kepala Lab
        </h6>
      </div>
      <div class="card-body p-3">
        <form action="<?= base_url('loans/' . $proposalId . '/approve-l2') ?>" method="post" class="mb-3">
          <?= csrf_field() ?>
          <div class="form-group mb-2">
            <label class="small text-muted">Catatan (opsional)</label>
            <input type="text" name="approval_l2_note" class="form-control form-control-sm"
                   placeholder="Tambahkan catatan...">
          </div>
          <button type="submit" class="btn btn-success btn-sm btn-block">
            <i class="fas fa-check mr-1"></i>Setujui Proposal
          </button>
        </form>
        <div class="divider text-center mb-3"><span class="bg-white px-2 text-muted small">atau</span><hr class="mt-n3"></div>
        <form action="<?= base_url('loans/' . $proposalId . '/reject-l2') ?>" method="post">
          <?= csrf_field() ?>
          <div class="form-group mb-2">
            <label class="small text-muted">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="rejected_reason" class="form-control form-control-sm" rows="3"
                      placeholder="Jelaskan alasan penolakan..." required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-sm btn-block">
            <i class="fas fa-times mr-1"></i>Tolak Proposal
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Cancel button */ ?>
    <?php if (
        activeGroupCan('lending.request.cancel')
        && in_array($proposalStatus, ['waiting_l1', 'waiting_l2'], true)
        && ((int)($proposal['proposer_id'] ?? 0) === (int)auth()->id() || activeGroupCan('lending.request.manage-all'))
    ): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body p-3">
        <button class="btn btn-outline-danger btn-sm btn-block" data-toggle="collapse" data-target="#cancel-form">
          <i class="fas fa-ban mr-1"></i>Batalkan Proposal
        </button>
        <div class="collapse mt-3" id="cancel-form">
          <form action="<?= base_url('loans/' . $proposalId . '/cancel') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group mb-2">
              <label class="small font-weight-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
              <textarea name="cancel_reason" class="form-control form-control-sm" rows="3"
                        placeholder="Jelaskan alasan pembatalan..." required></textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-sm btn-block">
              <i class="fas fa-ban mr-1"></i>Konfirmasi Batalkan
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Back to list */ ?>
    <a href="<?= base_url('loans') ?>" class="btn btn-outline-secondary btn-sm btn-block">
      <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
    </a>

  </div>

</div>
