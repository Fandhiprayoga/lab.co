<?php
$proposal       = $proposal ?? [];
$items          = $items ?? [];
$actorNames     = $actorNames ?? [];
$proposalStatus = $proposal['status'] ?? '-';
$loanType       = $proposal['loan_type'] ?? 'equipment';
$isEquipment    = $loanType === 'equipment';
$proposalPublicId = (string) ($proposal['public_id'] ?? '');

$accentColor  = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg     = $isEquipment ? 'rgba(79,195,247,.1)'  : 'rgba(129,199,132,.1)';
$accentBorder = $isEquipment ? '#4fc3f7'              : '#81c784';
$typeLabel    = $isEquipment ? 'Alat'                 : 'Laboratorium';
$typeIcon     = $isEquipment ? 'fa-tools'             : 'fa-door-open';

$statusMap = [
    'draft'      => ['label' => 'Draft',            'tone' => 'neutral',  'icon' => 'fa-file-alt'],
    'waiting_l1' => ['label' => 'Menunggu Laboran', 'tone' => 'waiting',  'icon' => 'fa-clock'],
    'waiting_l2' => ['label' => 'Menunggu Ka.Lab',  'tone' => 'review',   'icon' => 'fa-user-check'],
    'approved'   => ['label' => 'Disetujui',        'tone' => 'success',  'icon' => 'fa-check-circle'],
    'borrowed'   => ['label' => 'Dipinjam',         'tone' => 'active',   'icon' => 'fa-hand-holding'],
    'late'       => ['label' => 'Terlambat',        'tone' => 'warning',  'icon' => 'fa-exclamation-triangle'],
    'returned'   => ['label' => 'Dikembalikan',     'tone' => 'success',  'icon' => 'fa-undo'],
    'problematic'=> ['label' => 'Bermasalah',       'tone' => 'danger',   'icon' => 'fa-exclamation-circle'],
    'in_use'     => ['label' => 'Sedang Digunakan', 'tone' => 'active',   'icon' => 'fa-play-circle'],
    'completed'  => ['label' => 'Selesai',          'tone' => 'success',  'icon' => 'fa-flag-checkered'],
    'rejected'   => ['label' => 'Ditolak',          'tone' => 'danger',   'icon' => 'fa-times-circle'],
    'canceled'   => ['label' => 'Dibatalkan',       'tone' => 'dark',     'icon' => 'fa-ban'],
];
$statusInfo = $statusMap[$proposalStatus] ?? ['label' => $proposalStatus, 'tone' => 'neutral', 'icon' => 'fa-question'];

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

if (! empty($proposal['checkout_at'])) {
  $checkoutActor = $actorNames[(int)($proposal['checkout_by'] ?? 0)] ?? 'Petugas';
  $checkoutCondition = isset($proposal['checkout_condition'])
    ? ucwords(str_replace('_', ' ', (string) $proposal['checkout_condition']))
    : '';
  $timeline[] = [
    'time'   => $proposal['checkout_at'],
    'label'  => 'Check-out Alat',
    'detail' => esc($checkoutActor) . (! empty($checkoutCondition) ? ': kondisi awal ' . esc($checkoutCondition) : ''),
    'remark' => '',
    'color'  => 'primary',
    'icon'   => 'fa-hand-holding',
  ];
}

if (! empty($proposal['checkin_at'])) {
  $checkinActor = $actorNames[(int)($proposal['checkin_by'] ?? 0)] ?? 'Petugas';
  $checkinCondition = isset($proposal['checkin_condition'])
    ? ucwords(str_replace('_', ' ', (string) $proposal['checkin_condition']))
    : '';
  $timeline[] = [
    'time'   => $proposal['checkin_at'],
    'label'  => (($proposal['status'] ?? '') === 'problematic') ? 'Check-in (Bermasalah)' : 'Check-in Alat',
    'detail' => esc($checkinActor) . (! empty($checkinCondition) ? ': kondisi akhir ' . esc($checkinCondition) : ''),
    'remark' => $proposal['issue_note'] ?? '',
    'color'  => (($proposal['status'] ?? '') === 'problematic') ? 'danger' : 'success',
    'icon'   => (($proposal['status'] ?? '') === 'problematic') ? 'fa-exclamation-circle' : 'fa-undo',
  ];
}

if (! empty($proposal['started_use_at'])) {
  $startUseActor = $actorNames[(int)($proposal['started_use_by'] ?? 0)] ?? 'Petugas';
  $timeline[] = [
    'time'   => $proposal['started_use_at'],
    'label'  => 'Penggunaan Ruangan Dimulai',
    'detail' => 'Diverifikasi oleh ' . esc($startUseActor),
    'remark' => '',
    'color'  => 'primary',
    'icon'   => 'fa-play-circle',
  ];
}

if (! empty($proposal['finished_use_at'])) {
  $finishUseActor = $actorNames[(int)($proposal['finished_use_by'] ?? 0)] ?? 'Petugas';
  $timeline[] = [
    'time'   => $proposal['finished_use_at'],
    'label'  => 'Penggunaan Ruangan Selesai',
    'detail' => 'Diverifikasi oleh ' . esc($finishUseActor),
    'remark' => '',
    'color'  => 'success',
    'icon'   => 'fa-flag-checkered',
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
<style>
  .loan-show-page {
    --surface: #ffffff;
    --surface-soft: #f6f8fb;
    --line: #e5e9f2;
    --ink: #0f172a;
    --ink-soft: #6b7280;
    --brand: <?= $accentColor ?>;
    --brand-soft: <?= $accentBg ?>;
    font-family: Manrope, "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--ink);
  }
  .loan-show-page .chip {
    border: 1px solid var(--line);
    background: #fff;
    border-radius: 999px;
    color: var(--ink-soft);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    padding: 0.3rem 0.65rem;
    text-transform: uppercase;
  }
  .loan-show-page .status-badge {
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    padding: 0.4rem 0.75rem;
    text-transform: uppercase;
  }
  .loan-show-page .ux-status {
    border: 1px solid transparent;
  }
  .loan-show-page .ux-status--neutral { background:#eef2f6; border-color:#dde4ec; color:#475467; }
  .loan-show-page .ux-status--waiting { background:#fff6db; border-color:#ffe3a3; color:#8a6a13; }
  .loan-show-page .ux-status--review  { background:#e8f4ff; border-color:#cce6ff; color:#0f5c8e; }
  .loan-show-page .ux-status--active  { background:#e8f0ff; border-color:#cfdcff; color:#1e4eb7; }
  .loan-show-page .ux-status--success { background:#e8f8ef; border-color:#cdeed9; color:#1f7a3d; }
  .loan-show-page .ux-status--warning { background:#fff6db; border-color:#ffe3a3; color:#8a6a13; }
  .loan-show-page .ux-status--danger  { background:#ffefef; border-color:#ffd5d5; color:#b42318; }
  .loan-show-page .ux-status--dark    { background:#edf0f5; border-color:#d6dce7; color:#344054; }
  .loan-show-page .modern-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    margin-bottom: 1rem;
    overflow: hidden;
  }
  .loan-show-page .modern-card .card-head {
    align-items: center;
    border-bottom: 1px solid #eef1f6;
    display: flex;
    justify-content: space-between;
    padding: 0.85rem 1rem;
  }
  .loan-show-page .modern-card .card-head h6 {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.07em;
    margin: 0;
    text-transform: uppercase;
  }
  .loan-show-page .card-body-modern {
    padding: 1rem;
  }
  .loan-show-page .meta-grid {
    display: grid;
    gap: 0.65rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .loan-show-page .meta-item {
    background: var(--surface-soft);
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 0.7rem 0.75rem;
  }
  .loan-show-page .meta-label {
    color: var(--ink-soft);
    font-size: 0.7rem;
    letter-spacing: 0.04em;
    margin-bottom: 0.18rem;
    text-transform: uppercase;
  }
  .loan-show-page .meta-value {
    color: var(--ink);
    font-size: 0.85rem;
    font-weight: 700;
    line-height: 1.35;
  }
  .loan-show-page .objective-box {
    background: #fcfdff;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    color: #374151;
    font-size: 0.86rem;
    line-height: 1.62;
    margin-top: 0.9rem;
    padding: 0.8rem 0.85rem;
  }
  .loan-show-page .modern-shell {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    margin-bottom: 0.72rem;
    overflow: hidden;
  }
  .loan-show-page .modern-shell .shell-body {
    padding: .95rem;
  }
  .loan-show-page .focus-tabs {
    display: grid;
    gap: .6rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  .loan-show-page .focus-tab-btn {
    align-items: flex-start;
    background: #fff;
    border: 1px solid #e1e7f0;
    border-radius: 12px;
    color: #475467;
    display: flex;
    flex-direction: column;
    font-size: .74rem;
    font-weight: 700;
    gap: .35rem;
    letter-spacing: .02em;
    min-height: 86px;
    padding: .62rem .72rem;
    text-align: left;
    text-decoration: none;
    transition: all .18s ease;
  }
  .loan-show-page .focus-tab-btn i {
    color: var(--brand);
    font-size: .92rem;
  }
  .loan-show-page .focus-tab-title {
    color: #111827;
    font-size: .76rem;
    font-weight: 800;
    line-height: 1.35;
  }
  .loan-show-page .focus-tab-sub {
    color: #667085;
    font-size: .7rem;
    line-height: 1.35;
  }
  .loan-show-page .focus-tab-count {
    align-items: center;
    background: #eef2f7;
    border: 1px solid #d9e1ee;
    border-radius: 999px;
    color: #344054;
    display: inline-flex;
    font-size: .68rem;
    font-weight: 800;
    line-height: 1;
    min-width: 1.45rem;
    padding: .22rem .45rem;
  }
  .loan-show-page .focus-tab-btn.active .focus-tab-count {
    background: rgba(255,255,255,.88);
    border-color: rgba(31, 111, 235, 0.28);
    color: var(--brand);
  }
  .loan-show-page .focus-tab-btn:hover {
    border-color: #c8d3e6;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    color: #344054;
    text-decoration: none;
    transform: translateY(-1px);
  }
  .loan-show-page .focus-tab-btn.active {
    background: linear-gradient(165deg, #ffffff, var(--brand-soft));
    border-color: rgba(31, 111, 235, 0.28);
    box-shadow: inset 0 0 0 1px rgba(31, 111, 235, 0.12);
    color: #1d4f91;
  }
  .loan-show-page .focus-panel {
    display: none;
  }
  .loan-show-page .focus-panel.is-visible {
    display: block;
  }
  .loan-show-page .item-row {
    align-items: center;
    border-bottom: 1px dashed #e7ebf2;
    display: flex;
    gap: 0.65rem;
    padding: 0.7rem 0;
  }
  .loan-show-page .item-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
  }
  .loan-show-page .item-index {
    align-items: center;
    background: var(--brand);
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    font-size: 0.72rem;
    font-weight: 800;
    height: 24px;
    justify-content: center;
    width: 24px;
  }
  .loan-show-page .item-name {
    font-size: 0.87rem;
    font-weight: 700;
    margin-bottom: 0.14rem;
  }
  .loan-show-page .item-note {
    color: var(--ink-soft);
    font-size: 0.76rem;
  }
  .loan-show-page .timeline-wrap {
    padding-left: 1.8rem;
    position: relative;
  }
  .loan-show-page .timeline-block {
    margin-bottom: 0.9rem;
    position: relative;
  }
  .loan-show-page .timeline-block:last-child {
    margin-bottom: 0;
  }
  .loan-show-page .timeline-dot {
    align-items: center;
    border-radius: 999px;
    color: #fff;
    display: inline-flex;
    font-size: 0.62rem;
    height: 24px;
    justify-content: center;
    left: -1.8rem;
    position: absolute;
    top: 0.2rem;
    width: 24px;
  }
  .loan-show-page .timeline-card {
    background: #f8faff;
    border: 1px solid #e7edf7;
    border-left-width: 3px;
    border-radius: 12px;
    padding: 0.72rem 0.78rem;
  }
  .loan-show-page .timeline-title {
    font-size: 0.82rem;
    font-weight: 800;
  }
  .loan-show-page .timeline-time {
    color: var(--ink-soft);
    font-size: 0.72rem;
  }
  .loan-show-page .timeline-detail {
    color: #556073;
    font-size: 0.76rem;
    margin-top: 0.22rem;
  }
  .loan-show-page .remark-box {
    background: #fff5f5;
    border-left: 3px solid #dc3545;
    border-radius: 8px;
    color: #b42318;
    font-size: 0.75rem;
    margin-top: 0.42rem;
    padding: 0.45rem 0.55rem;
  }
  .loan-show-page .action-col {
    position: sticky;
    top: 0.9rem;
  }
  .loan-show-page .action-panel {
    background: #ffffff;
    border: 1px solid #e6ebf3;
    border-radius: 14px;
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.07);
    margin-bottom: 0.8rem;
    overflow: hidden;
  }
  .loan-show-page .action-panel .panel-head {
    background: #f8fbff;
    border-bottom: 1px solid #edf2f9;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 0.62rem 0.78rem;
    text-transform: uppercase;
  }
  .loan-show-page .action-panel .panel-body {
    padding: 0.78rem;
  }
  .loan-show-page .action-panel .form-group label {
    color: #667085;
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.28rem;
  }
  .loan-show-page .action-panel .form-control {
    border-radius: 10px;
    font-size: 0.84rem;
  }
  .loan-show-page .action-panel .btn {
    border-radius: 10px;
    font-size: 0.79rem;
    font-weight: 700;
  }
  .loan-show-page .btn-action {
    border-radius: 10px;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.01em;
  }
  .loan-show-page .btn-outline-secondary.btn-action {
    background: #ffffff !important;
    border-color: #cfd7e6 !important;
    color: #344054 !important;
  }
  .loan-show-page .btn-outline-secondary.btn-action:hover,
  .loan-show-page .btn-outline-secondary.btn-action:focus,
  .loan-show-page .btn-outline-secondary.btn-action:active {
    background: #f2f5fb !important;
    border-color: #b9c5db !important;
    color: #1f2937 !important;
  }
  .loan-show-page .notice {
    background: #fff9e8;
    border: 1px solid #ffe3a5;
    border-radius: 12px;
    color: #8a6a13;
    font-size: 0.78rem;
    margin-bottom: 0.8rem;
    padding: 0.65rem 0.75rem;
  }
  @media (max-width: 991.98px) {
    .loan-show-page .action-col {
      position: static;
      top: auto;
    }
    .loan-show-page .meta-grid {
      grid-template-columns: 1fr;
    }
    .loan-show-page .focus-tabs {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 575.98px) {
    .loan-show-page .focus-tabs {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="loan-show-page">
  <div class="modern-shell">
    <div class="shell-body py-2">
      <div class="focus-tabs" id="focus-tabs-show">
        <button type="button" class="focus-tab-btn active" data-focus-tab="summary">
          <i class="fas fa-file-alt"></i>
          <span class="focus-tab-title">Ringkasan Proposal</span>
          <span class="focus-tab-sub">Informasi utama pengajuan.</span>
        </button>
        <button type="button" class="focus-tab-btn" data-focus-tab="items">
          <i class="fas <?= $typeIcon ?>"></i>
          <span class="d-flex align-items-center" style="gap:.35rem;">
            <span class="focus-tab-title">Item <?= esc($typeLabel) ?></span>
            <span class="focus-tab-count"><?= count($items) ?></span>
          </span>
          <span class="focus-tab-sub">Daftar item dalam proposal.</span>
        </button>
        <button type="button" class="focus-tab-btn" data-focus-tab="timeline">
          <i class="fas fa-history"></i>
          <span class="d-flex align-items-center" style="gap:.35rem;">
            <span class="focus-tab-title">Timeline Proses</span>
            <span class="focus-tab-count"><?= count($timeline) ?></span>
          </span>
          <span class="focus-tab-sub">Riwayat tahapan proposal.</span>
        </button>
        <button type="button" class="focus-tab-btn" data-focus-tab="actions">
          <i class="fas fa-bolt"></i>
          <span class="focus-tab-title">Panel Aksi</span>
          <span class="focus-tab-sub">Approval, verifikasi, dan navigasi.</span>
        </button>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8" id="focus-left-col">
      <section class="modern-card focus-panel is-visible" id="focus-panel-summary">
        <div class="card-head">
          <h6>Ringkasan Proposal</h6>
        </div>
        <div class="card-body-modern">
          <div class="meta-grid">
            <div class="meta-item">
              <div class="meta-label">Tipe Peminjaman</div>
              <div class="meta-value"><i class="fas <?= $typeIcon ?> mr-1"></i><?= esc($typeLabel) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Status Saat Ini</div>
              <div class="meta-value"><?= esc($statusInfo['label']) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Waktu Mulai</div>
              <div class="meta-value"><?= $fmtDt($proposal['start_at'] ?? null) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Waktu Selesai</div>
              <div class="meta-value"><?= $fmtDt($proposal['end_at'] ?? null) ?></div>
            </div>
          </div>
          <div class="objective-box">
            <?= nl2br(esc($proposal['objective'] ?? '-')) ?>
          </div>
        </div>
      </section>

      <section class="modern-card focus-panel" id="focus-panel-items">
        <div class="card-head">
          <h6>Item <?= esc($typeLabel) ?></h6>
          <span class="chip"><?= count($items) ?> item</span>
        </div>
        <div class="card-body-modern">
          <?php if (empty($items)): ?>
            <div class="text-center text-muted py-3">
              <i class="fas fa-box-open d-block mb-2" style="font-size:1.2rem;opacity:.45"></i>
              Tidak ada item dalam proposal ini.
            </div>
          <?php else: ?>
            <?php foreach ($items as $idx => $item): ?>
              <div class="item-row">
                <span class="item-index"><?= $idx + 1 ?></span>
                <div class="flex-grow-1">
                  <div class="item-name"><?= esc($isEquipment ? ($item['equipment_name'] ?? '-') : ($item['lab_name'] ?? '-')) ?></div>
                  <div class="item-note">
                    <?php if ($isEquipment): ?>
                      Qty: <?= (int) ($item['qty'] ?? 0) ?>
                    <?php endif; ?>
                    <?php if (! empty($item['note'])): ?>
                      <?php if ($isEquipment): ?> &middot; <?php endif; ?><?= esc($item['note']) ?>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="modern-card focus-panel" id="focus-panel-timeline">
        <div class="card-head">
          <h6>Timeline Proses</h6>
          <span class="chip"><?= count($timeline) ?> event</span>
        </div>
        <div class="card-body-modern">
          <?php if (empty($timeline)): ?>
            <div class="text-center text-muted py-3">
              <i class="fas fa-history d-block mb-2" style="font-size:1.2rem;opacity:.45"></i>
              Belum ada riwayat proses.
            </div>
          <?php else: ?>
            <div class="timeline-wrap">
              <?php foreach ($timeline as $ev): ?>
                <?php $dotClr = $dotColors[$ev['color']] ?? '#6c757d'; ?>
                <article class="timeline-block">
                  <span class="timeline-dot" style="background:<?= $dotClr ?>"><i class="fas <?= esc($ev['icon']) ?>"></i></span>
                  <div class="timeline-card" style="border-left-color:<?= $dotClr ?>">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="timeline-title"><?= esc($ev['label']) ?></div>
                      <div class="timeline-time"><?= $fmtDt($ev['time']) ?></div>
                    </div>
                    <?php if (! empty($ev['detail'])): ?>
                      <div class="timeline-detail"><?= $ev['detail'] ?></div>
                    <?php endif; ?>
                    <?php if (! empty($ev['remark'])): ?>
                      <div class="remark-box"><?= esc($ev['remark']) ?></div>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <div class="col-lg-4 action-col focus-panel" id="focus-panel-actions">
      <?php if ($proposalStatus === 'rejected' && ! empty($proposal['rejected_reason'])): ?>
        <div class="notice">
          <strong><i class="fas fa-times-circle mr-1"></i>Alasan Penolakan:</strong>
          <div class="mt-1"><?= esc($proposal['rejected_reason']) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($proposalStatus === 'canceled' && ! empty($proposal['cancel_reason'])): ?>
        <div class="notice" style="background:#f5f7fa;border-color:#dbe3ef;color:#475467;">
          <strong><i class="fas fa-ban mr-1"></i>Alasan Pembatalan:</strong>
          <div class="mt-1"><?= esc($proposal['cancel_reason']) ?></div>
        </div>
      <?php endif; ?>

      <?php if (in_array($proposalStatus, ['waiting_l1', 'waiting_l2'], true)): ?>
        <div class="notice">
          <strong><i class="fas fa-hourglass-half mr-1"></i>Menunggu Persetujuan</strong>
          <div class="mt-1">
            <?= $proposalStatus === 'waiting_l1'
              ? 'Proposal sedang menunggu persetujuan Laboran.'
              : 'Sudah disetujui Laboran dan menunggu persetujuan Kepala Lab.' ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($isEquipment && activeGroupCan('lending.checkout') && $proposalStatus === 'approved'): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-hand-holding mr-1"></i>Check-out Alat</div>
          <div class="panel-body">
            <form action="<?= base_url('loans/' . $proposalPublicId . '/checkout') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group">
                <label>Kondisi Awal</label>
                <select name="checkout_condition" class="form-control form-control-sm" required>
                  <option value="baik">Baik</option>
                  <option value="siap_pakai">Siap Pakai</option>
                  <option value="catatan">Perlu Catatan</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary btn-sm btn-block btn-action">
                <i class="fas fa-hand-holding mr-1"></i>Proses Check-out
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($isEquipment && activeGroupCan('lending.checkin') && in_array($proposalStatus, ['borrowed', 'late'], true)): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-undo mr-1"></i>Check-in Alat</div>
          <div class="panel-body">
            <form action="<?= base_url('loans/' . $proposalPublicId . '/checkin') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group">
                <label>Kondisi Akhir</label>
                <select name="checkin_condition" class="form-control form-control-sm" required>
                  <option value="baik">Baik</option>
                  <option value="rusak_ringan">Rusak Ringan</option>
                  <option value="rusak_berat">Rusak Berat</option>
                  <option value="hilang">Hilang</option>
                </select>
              </div>
              <div class="form-group">
                <label>Catatan Masalah (opsional)</label>
                <textarea name="issue_note" class="form-control form-control-sm" rows="2" placeholder="Isi jika ada kerusakan/kehilangan"></textarea>
              </div>
              <button type="submit" class="btn btn-success btn-sm btn-block btn-action">
                <i class="fas fa-undo mr-1"></i>Proses Check-in
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if (! $isEquipment && activeGroupCan('lending.checkout') && $proposalStatus === 'approved'): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-play mr-1"></i>Mulai Penggunaan</div>
          <div class="panel-body">
            <p class="text-muted small mb-2">Verifikasi bahwa ruangan mulai digunakan.</p>
            <form action="<?= base_url('loans/' . $proposalPublicId . '/usage/start') ?>" method="post">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-primary btn-sm btn-block btn-action">
                <i class="fas fa-play mr-1"></i>Mulai Penggunaan
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if (! $isEquipment && activeGroupCan('lending.checkin') && $proposalStatus === 'in_use'): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-flag-checkered mr-1"></i>Selesaikan Penggunaan</div>
          <div class="panel-body">
            <p class="text-muted small mb-2">Verifikasi penggunaan ruangan sudah selesai.</p>
            <form action="<?= base_url('loans/' . $proposalPublicId . '/usage/finish') ?>" method="post">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-success btn-sm btn-block btn-action">
                <i class="fas fa-flag-checkered mr-1"></i>Selesaikan Penggunaan
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.approval.l1') && $proposalStatus === 'waiting_l1'): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-user-check mr-1"></i>Approval Laboran</div>
          <div class="panel-body">
            <form action="<?= base_url('loans/' . $proposalPublicId . '/approve-l1') ?>" method="post" class="mb-3">
              <?= csrf_field() ?>
              <div class="form-group mb-2">
                <label>Catatan (opsional)</label>
                <input type="text" name="approval_l1_note" class="form-control form-control-sm" placeholder="Tambahkan catatan...">
              </div>
              <button type="submit" class="btn btn-success btn-sm btn-block btn-action"><i class="fas fa-check mr-1"></i>Setujui Proposal</button>
            </form>

            <form action="<?= base_url('loans/' . $proposalPublicId . '/reject-l1') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group mb-2">
                <label>Alasan Penolakan <span class="text-danger">*</span></label>
                <textarea name="rejected_reason" class="form-control form-control-sm" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
              </div>
              <button type="submit" class="btn btn-danger btn-sm btn-block btn-action"><i class="fas fa-times mr-1"></i>Tolak Proposal</button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.approval.l2') && $proposalStatus === 'waiting_l2'): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-user-shield mr-1"></i>Approval Kepala Lab</div>
          <div class="panel-body">
            <form action="<?= base_url('loans/' . $proposalPublicId . '/approve-l2') ?>" method="post" class="mb-3">
              <?= csrf_field() ?>
              <div class="form-group mb-2">
                <label>Catatan (opsional)</label>
                <input type="text" name="approval_l2_note" class="form-control form-control-sm" placeholder="Tambahkan catatan...">
              </div>
              <button type="submit" class="btn btn-success btn-sm btn-block btn-action"><i class="fas fa-check mr-1"></i>Setujui Proposal</button>
            </form>

            <form action="<?= base_url('loans/' . $proposalPublicId . '/reject-l2') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group mb-2">
                <label>Alasan Penolakan <span class="text-danger">*</span></label>
                <textarea name="rejected_reason" class="form-control form-control-sm" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
              </div>
              <button type="submit" class="btn btn-danger btn-sm btn-block btn-action"><i class="fas fa-times mr-1"></i>Tolak Proposal</button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <?php if (
        activeGroupCan('lending.request.cancel')
        && in_array($proposalStatus, ['waiting_l1', 'waiting_l2'], true)
        && ((int)($proposal['proposer_id'] ?? 0) === (int)auth()->id() || activeGroupCan('lending.request.manage-all'))
      ): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-ban mr-1"></i>Batalkan Proposal</div>
          <div class="panel-body">
            <form action="<?= base_url('loans/' . $proposalPublicId . '/cancel') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group mb-2">
                <label>Alasan Pembatalan <span class="text-danger">*</span></label>
                <textarea name="cancel_reason" class="form-control form-control-sm" rows="3" placeholder="Jelaskan alasan pembatalan..." required></textarea>
              </div>
              <button type="submit" class="btn btn-danger btn-sm btn-block btn-action">
                <i class="fas fa-ban mr-1"></i>Konfirmasi Batalkan
              </button>
            </form>
          </div>
        </section>
      <?php endif; ?>

      <a href="<?= base_url('loans') ?>" class="btn btn-outline-secondary btn-sm btn-block btn-action" style="border-radius:10px;font-weight:700;">
        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
      </a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var focusTabs = document.querySelectorAll('#focus-tabs-show [data-focus-tab]');
  var summaryPanel = document.getElementById('focus-panel-summary');
  var itemsPanel = document.getElementById('focus-panel-items');
  var timelinePanel = document.getElementById('focus-panel-timeline');
  var actionsPanel = document.getElementById('focus-panel-actions');
  var leftCol = document.getElementById('focus-left-col');
  var tabStorageKey = 'loanShowActiveTab';

  function normalizeTabName(tabName) {
    var allowed = ['summary', 'items', 'timeline', 'actions'];
    return allowed.indexOf(tabName) !== -1 ? tabName : 'summary';
  }

  function updateTabInUrl(tabName) {
    try {
      var url = new URL(window.location.href);
      url.searchParams.set('tab', tabName);
      window.history.replaceState({}, '', url.toString());
    } catch (e) {
      // Ignore URL parsing issues.
    }
  }

  function setFocusTab(tabName) {
    tabName = normalizeTabName(tabName);

    focusTabs.forEach(function (btn) {
      btn.classList.toggle('active', btn.getAttribute('data-focus-tab') === tabName);
    });

    if (summaryPanel) summaryPanel.classList.toggle('is-visible', tabName === 'summary');
    if (itemsPanel) itemsPanel.classList.toggle('is-visible', tabName === 'items');
    if (timelinePanel) timelinePanel.classList.toggle('is-visible', tabName === 'timeline');

    if (actionsPanel) actionsPanel.classList.toggle('is-visible', tabName === 'actions');

    if (leftCol) {
      leftCol.classList.toggle('col-lg-8', tabName === 'actions');
      leftCol.classList.toggle('col-lg-12', tabName !== 'actions');
    }

    try {
      localStorage.setItem(tabStorageKey, tabName);
    } catch (e) {
      // Ignore storage issues.
    }
    updateTabInUrl(tabName);
  }

  focusTabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setFocusTab(this.getAttribute('data-focus-tab'));
    });
  });

  var urlTab = '';
  try {
    urlTab = new URL(window.location.href).searchParams.get('tab') || '';
  } catch (e) {
    urlTab = '';
  }

  var savedTab = '';
  try {
    savedTab = localStorage.getItem(tabStorageKey) || '';
  } catch (e) {
    savedTab = '';
  }

  setFocusTab(urlTab || savedTab || 'summary');
});
</script>
