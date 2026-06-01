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
$itemCount = count($items);

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
  .loan-show-page .selected-grid {
    margin: 0 -.35rem;
  }
  .loan-show-page .selected-item {
    padding: 0 .35rem;
    margin-bottom: .7rem;
  }
  .loan-show-page .selected-media {
    position: relative;
    height: 128px;
    overflow: hidden;
    border-radius: 4px 4px 0 0;
    background: linear-gradient(135deg, rgba(79,195,247,.15), rgba(2,136,209,.1));
  }
  .loan-show-page .selected-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .loan-show-page .selected-media .fallback {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.4rem;
  }
  .loan-show-page .selected-name {
    font-size: .86rem;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: .22rem;
  }
  .loan-show-page .catalog-card {
    transition: box-shadow .15s, transform .15s;
    border: 1px solid #e7ebf2;
  }
  .loan-show-page .catalog-card:hover {
    box-shadow: 0 8px 22px rgba(0,0,0,.1) !important;
    transform: translateY(-2px);
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
  .loan-show-page .action-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .loan-show-page .action-list form {
    margin: 0;
  }
  .loan-show-page .action-list-btn {
    align-items: center;
    background: #ffffff;
    border: 1px solid #d9e2f1;
    border-radius: 10px;
    color: #344054;
    display: flex;
    font-size: 0.8rem;
    font-weight: 700;
    gap: 0.6rem;
    justify-content: flex-start;
    padding: 0.58rem 0.72rem;
    text-decoration: none;
    transition: all 0.15s ease;
    width: 100%;
  }
  .loan-show-page .action-list-btn:hover {
    text-decoration: none;
    transform: translateY(-1px);
  }
  .loan-show-page .action-list-btn i {
    width: 16px;
    text-align: center;
  }
  .loan-show-page .action-list-btn.approve {
    border-color: #ccebd8;
    color: #1f7a3d;
    background: #f3fcf6;
  }
  .loan-show-page .action-list-btn.reject {
    border-color: #f3c9c9;
    color: #b42318;
    background: #fff5f5;
  }
  .loan-show-page .action-list-btn.cancel {
    border-color: #ffe3a5;
    color: #8a6a13;
    background: #fff9e8;
  }
  .loan-show-page .action-list-btn.back {
    border-color: #d9e2f1;
    color: #344054;
    background: #f8fafc;
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
  .loan-show-page .action-overview-grid {
    display: grid;
    gap: 0.65rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  .loan-show-page .action-overview-card {
    background: var(--surface-soft);
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 0.75rem 0.8rem;
  }
  .loan-show-page .action-overview-label {
    color: var(--ink-soft);
    font-size: 0.7rem;
    letter-spacing: 0.04em;
    margin-bottom: 0.2rem;
    text-transform: uppercase;
  }
  .loan-show-page .action-overview-value {
    color: var(--ink);
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1.35;
  }
  .loan-show-page .action-checklist {
    margin: 0;
    padding-left: 1rem;
  }
  .loan-show-page .action-checklist li {
    color: #556073;
    font-size: 0.8rem;
    line-height: 1.5;
    margin-bottom: 0.3rem;
  }
  .loan-show-page .action-checklist li:last-child {
    margin-bottom: 0;
  }
  .loan-show-page .action-note {
    background: #f8fbff;
    border: 1px solid #e4eefb;
    border-radius: 12px;
    color: #38516e;
    font-size: 0.8rem;
    margin-top: 0.85rem;
    padding: 0.7rem 0.8rem;
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
    .loan-show-page .action-overview-grid {
      grid-template-columns: 1fr;
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
              <div class="meta-label">Kode Proposal</div>
              <div class="meta-value"><?= esc($proposal['proposal_code'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Judul Proposal</div>
              <div class="meta-value"><?= esc($proposal['title'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Tipe Peminjaman</div>
              <div class="meta-value"><i class="fas <?= $typeIcon ?> mr-1"></i><?= esc($typeLabel) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Status Saat Ini</div>
              <div class="meta-value"><?= esc($statusInfo['label']) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Waktu Pengajuan</div>
              <div class="meta-value"><?= $fmtDt($proposal['submitted_at'] ?? ($proposal['created_at'] ?? null)) ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Nama Pengusul</div>
              <div class="meta-value"><?= esc($proposal['proposer_name'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">NIM</div>
              <div class="meta-value"><?= esc($proposal['proposer_nim_nik'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Email Pengusul</div>
              <div class="meta-value"><?= esc($proposal['proposer_email'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Nomor Telp</div>
              <div class="meta-value"><?= esc($proposal['proposer_phone'] ?? '-') ?></div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Program Studi</div>
              <div class="meta-value"><?= esc($proposal['proposer_prodi'] ?? '-') ?></div>
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
            <div class="meta-label mb-1" style="font-size:.68rem;">Tujuan Proposal</div>
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
            <div class="row selected-grid">
              <?php foreach ($items as $item): ?>
              <?php
                $itemName = $isEquipment ? ($item['equipment_name'] ?? '-') : ($item['lab_name'] ?? '-');
                $thumbRaw = $isEquipment ? ($item['equipment_photo'] ?? '') : ($item['lab_logo'] ?? '');
                $thumbUrl = '';
                if (! empty($thumbRaw)) {
                    $thumbUrl = preg_match('/^https?:\/\//i', (string) $thumbRaw) ? (string) $thumbRaw : base_url((string) $thumbRaw);
                }
              ?>
              <div class="col-sm-6 col-lg-4 col-xl-3 selected-item">
                <div class="card border-0 shadow-sm h-100 catalog-card">
                  <div class="selected-media">
                    <?php if ($thumbUrl !== ''): ?>
                      <img src="<?= esc($thumbUrl) ?>" alt="<?= esc($itemName) ?>">
                      <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.45),transparent)"></div>
                    <?php else: ?>
                      <div class="fallback"><i class="fas <?= $isEquipment ? 'fa-tools' : 'fa-door-open' ?>"></i></div>
                    <?php endif; ?>
                    <?php if ($isEquipment && ! empty($item['equipment_category'])): ?>
                      <span class="badge badge-light" style="position:absolute;top:8px;left:8px;font-size:.68rem">
                        <?= esc($item['equipment_category']) ?>
                      </span>
                    <?php endif; ?>
                    <?php if (! $isEquipment && ! empty($item['lab_code'])): ?>
                      <span class="badge badge-light" style="position:absolute;top:8px;left:8px;font-size:.68rem">
                        <?= esc($item['lab_code']) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($isEquipment && isset($item['qty'])): ?>
                      <span class="badge badge-light" style="position:absolute;top:8px;right:8px;font-size:.68rem;border:1px solid #e5e9f2;">
                        Qty <?= (int) $item['qty'] ?>
                      </span>
                    <?php endif; ?>
                    <?php if (! $isEquipment && $thumbUrl !== '' && ! empty($itemName)): ?>
                      <div style="position:absolute;bottom:8px;left:10px;right:10px">
                        <div class="text-white font-weight-bold" style="font-size:.85rem;text-shadow:0 1px 3px rgba(0,0,0,.6);line-height:1.2">
                          <?= esc($itemName) ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="card-body p-3 d-flex flex-column">
                    <?php if ($isEquipment || $thumbUrl === ''): ?>
                      <div class="selected-name" title="<?= esc($itemName) ?>"><?= esc($itemName) ?></div>
                    <?php endif; ?>

                    <?php if ($isEquipment): ?>
                      <div class="text-muted mb-2" style="font-size:.75rem">
                        <i class="fas fa-flask fa-xs mr-1"></i><?= esc($item['equipment_lab_name'] ?? '-') ?>
                        <?php if (! empty($item['equipment_lab_location'])): ?>
                          &bull; <i class="fas fa-map-marker-alt fa-xs mr-1"></i><?= esc($item['equipment_lab_location']) ?>
                        <?php endif; ?>
                      </div>
                    <?php else: ?>
                      <div class="mb-2">
                        <?php if (! empty($item['lab_code'])): ?>
                          <div class="text-muted" style="font-size:.73rem"><i class="fas fa-hashtag fa-xs mr-1"></i><?= esc($item['lab_code']) ?></div>
                        <?php endif; ?>
                        <?php if (! empty($item['lab_location'])): ?>
                          <div class="text-muted" style="font-size:.73rem"><i class="fas fa-map-marker-alt fa-xs mr-1"></i><?= esc($item['lab_location']) ?></div>
                        <?php endif; ?>
                        <?php if (! empty($item['lab_capacity'])): ?>
                          <div class="text-muted" style="font-size:.73rem"><i class="fas fa-users fa-xs mr-1"></i>Kapasitas <?= (int) $item['lab_capacity'] ?> orang</div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>

                    <?php if (! empty($item['note'])): ?>
                      <div class="text-muted mt-auto" style="font-size:.73rem">
                        <i class="fas fa-sticky-note fa-xs mr-1"></i><?= esc($item['note']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
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

      <section class="modern-card focus-panel" id="focus-panel-actions-main">
        <div class="card-head">
          <h6>Ringkasan Tindak Lanjut</h6>
          <span class="chip">Aksi Proposal</span>
        </div>
        <div class="card-body-modern">
          <div class="action-overview-grid">
            <div class="action-overview-card">
              <div class="action-overview-label">Status Proposal</div>
              <div class="action-overview-value"><?= esc($statusInfo['label']) ?></div>
            </div>
            <div class="action-overview-card">
              <div class="action-overview-label">Tipe Peminjaman</div>
              <div class="action-overview-value"><?= esc($typeLabel) ?></div>
            </div>
            <div class="action-overview-card">
              <div class="action-overview-label">Jumlah Item</div>
              <div class="action-overview-value"><?= (int) $itemCount ?> item</div>
            </div>
          </div>

          <div class="objective-box mt-3">
            <div class="meta-label mb-1" style="font-size:.68rem;">Checklist Sebelum Menjalankan Aksi</div>
            <ul class="action-checklist">
              <li>Pastikan data item sesuai dengan kebutuhan proposal.</li>
              <li>Periksa kembali periode peminjaman dan status proposal saat ini.</li>
              <li>Tambahkan catatan yang diperlukan untuk approval/check-out/check-in.</li>
              <li>Gunakan panel aksi di sisi kanan untuk mengeksekusi proses sesuai role.</li>
            </ul>
          </div>

          <div class="action-note">
            <i class="fas fa-info-circle mr-1"></i>
            Panel kanan menampilkan aksi yang relevan berdasarkan status proposal dan hak akses pengguna aktif.
          </div>
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

      <?php
        $canApproveL1 = activeGroupCan('lending.approval.l1') && $proposalStatus === 'waiting_l1';
        $canApproveL2 = activeGroupCan('lending.approval.l2') && $proposalStatus === 'waiting_l2';
        $canCancelProposal = activeGroupCan('lending.request.cancel')
          && in_array($proposalStatus, ['waiting_l1', 'waiting_l2'], true)
          && ((int)($proposal['proposer_id'] ?? 0) === (int)auth()->id() || activeGroupCan('lending.request.manage-all'));
      ?>

      <?php if ($canApproveL1 || $canApproveL2 || $canCancelProposal): ?>
        <section class="action-panel">
          <div class="panel-head"><i class="fas fa-list mr-1"></i>Aksi Proposal</div>
          <div class="panel-body">
            <div class="action-list">
              <?php if ($canApproveL1): ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/approve-l1') ?>" method="post" class="js-swal-approve-form" data-approve-level="L1" data-note-name="approval_l1_note">
                  <?= csrf_field() ?>
                  <input type="hidden" name="approval_l1_note" value="">
                  <button type="submit" class="action-list-btn approve"><i class="fas fa-check-circle"></i>Setujui Proposal</button>
                </form>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/reject-l1') ?>" method="post" class="js-swal-reject-form" data-reject-level="L1" data-reason-name="rejected_reason">
                  <?= csrf_field() ?>
                  <input type="hidden" name="rejected_reason" value="">
                  <button type="submit" class="action-list-btn reject"><i class="fas fa-times-circle"></i>Tolak Proposal</button>
                </form>
              <?php endif; ?>

              <?php if ($canApproveL2): ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/approve-l2') ?>" method="post" class="js-swal-approve-form" data-approve-level="L2" data-note-name="approval_l2_note">
                  <?= csrf_field() ?>
                  <input type="hidden" name="approval_l2_note" value="">
                  <button type="submit" class="action-list-btn approve"><i class="fas fa-check-circle"></i>Setujui Proposal</button>
                </form>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/reject-l2') ?>" method="post" class="js-swal-reject-form" data-reject-level="L2" data-reason-name="rejected_reason">
                  <?= csrf_field() ?>
                  <input type="hidden" name="rejected_reason" value="">
                  <button type="submit" class="action-list-btn reject"><i class="fas fa-times-circle"></i>Tolak Proposal</button>
                </form>
              <?php endif; ?>

              <?php if ($canCancelProposal): ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/cancel') ?>" method="post" class="js-swal-cancel-form" data-reason-name="cancel_reason">
                  <?= csrf_field() ?>
                  <input type="hidden" name="cancel_reason" value="">
                  <button type="submit" class="action-list-btn cancel"><i class="fas fa-ban"></i>Batalkan Proposal</button>
                </form>
              <?php endif; ?>

              <a href="<?= base_url('loans') ?>" class="action-list-btn back"><i class="fas fa-arrow-left"></i>Kembali ke Daftar</a>
            </div>
          </div>
        </section>
      <?php else: ?>
        <a href="<?= base_url('loans') ?>" class="btn btn-outline-secondary btn-sm btn-block btn-action" style="border-radius:10px;font-weight:700;">
          <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var focusTabs = document.querySelectorAll('#focus-tabs-show [data-focus-tab]');
  var summaryPanel = document.getElementById('focus-panel-summary');
  var itemsPanel = document.getElementById('focus-panel-items');
  var timelinePanel = document.getElementById('focus-panel-timeline');
  var actionsMainPanel = document.getElementById('focus-panel-actions-main');
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
    if (actionsMainPanel) actionsMainPanel.classList.toggle('is-visible', tabName === 'actions');

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

  document.querySelectorAll('.js-swal-approve-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var level = (form.getAttribute('data-approve-level') || '').trim() || 'Approval';
      var noteName = (form.getAttribute('data-note-name') || '').trim();

      Swal.fire({
        title: 'Setujui Proposal ' + level + '?',
        text: 'Tambahkan catatan approval sebelum melanjutkan.',
        icon: 'question',
        input: 'textarea',
        inputLabel: 'Catatan Approval',
        inputPlaceholder: 'Tulis catatan persetujuan...',
        inputAttributes: { 'aria-label': 'Catatan Approval' },
        inputAutoTrim: true,
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        reverseButtons: true,
        inputValidator: function (value) {
          if (!value || !value.trim()) {
            return 'Catatan approval wajib diisi.';
          }
        },
      }).then(function (result) {
        if (!result.isConfirmed) return;

        if (noteName) {
          var noteInput = form.querySelector('input[name="' + noteName + '"]');
          if (noteInput) {
            noteInput.value = (result.value || '').trim();
          }
        }

        form.submit();
      });
    });
  });

  document.querySelectorAll('.js-swal-reject-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var level = (form.getAttribute('data-reject-level') || '').trim() || 'Approval';
      var reasonName = (form.getAttribute('data-reason-name') || '').trim();

      Swal.fire({
        title: 'Tolak Proposal ' + level + '?',
        text: 'Isi alasan penolakan untuk melanjutkan.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Tulis alasan penolakan proposal...',
        inputAttributes: { 'aria-label': 'Alasan Penolakan' },
        inputAutoTrim: true,
        showCancelButton: true,
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        inputValidator: function (value) {
          if (!value || !value.trim()) {
            return 'Alasan penolakan wajib diisi.';
          }
        },
      }).then(function (result) {
        if (!result.isConfirmed) return;

        if (reasonName) {
          var reasonInput = form.querySelector('input[name="' + reasonName + '"]');
          if (reasonInput) {
            reasonInput.value = (result.value || '').trim();
          }
        }

        form.submit();
      });
    });
  });

  document.querySelectorAll('.js-swal-cancel-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var reasonName = (form.getAttribute('data-reason-name') || '').trim();

      Swal.fire({
        title: 'Batalkan Proposal?',
        text: 'Isi alasan pembatalan untuk melanjutkan.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Alasan Pembatalan',
        inputPlaceholder: 'Tulis alasan pembatalan proposal...',
        inputAttributes: { 'aria-label': 'Alasan Pembatalan' },
        inputAutoTrim: true,
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        inputValidator: function (value) {
          if (!value || !value.trim()) {
            return 'Alasan pembatalan wajib diisi.';
          }
        },
      }).then(function (result) {
        if (!result.isConfirmed) return;

        if (reasonName) {
          var reasonInput = form.querySelector('input[name="' + reasonName + '"]');
          if (reasonInput) {
            reasonInput.value = (result.value || '').trim();
          }
        }

        form.submit();
      });
    });
  });
});
</script>
