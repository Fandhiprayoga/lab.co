<?php
$proposals = $proposals ?? [];
$activeTab = $activeTab ?? 'active';
$tabCounts = $tabCounts ?? ['active' => 0, 'archive' => 0];
$activeLab = $activeLab ?? null;
?>

<?php if ($activeLab): ?>
<!-- <div class="alert alert-info alert-dismissible show fade">
  <div class="alert-body d-flex align-items-center justify-content-between">
    <span>
      <i class="fas fa-flask mr-1"></i>
      Data difilter untuk laboratorium: <strong><?= esc($activeLab['name']) ?></strong> (<?= esc($activeLab['code']) ?>)
    </span>
    <a href="<?= base_url('loans') ?>" class="btn btn-sm btn-light">
      <i class="fas fa-times mr-1"></i>Reset Filter
    </a>
  </div>
</div> -->
<?php endif; ?>

<?php
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

// counts for summary cards
$counts = [
    'all'        => count($proposals),
  'active'     => 0,
  'operational'=> 0,
  'closed'     => 0,
];
foreach ($proposals as $p) {
  if (in_array($p['status'], ['draft', 'waiting_l1', 'waiting_l2', 'approved'], true)) {
    $counts['active']++;
  } elseif (in_array($p['status'], ['borrowed', 'late', 'in_use'], true)) {
    $counts['operational']++;
  } elseif (in_array($p['status'], ['returned', 'completed', 'problematic', 'rejected', 'canceled'], true)) {
    $counts['closed']++;
  }
}
?>
<style>
  .loan-index-page {
    --surface: #ffffff;
    --surface-soft: #f6f8fb;
    --line: #e5e9f2;
    --ink: #0f172a;
    --ink-soft: #6b7280;
    --brand: #1f6feb;
    --brand-soft: rgba(31, 111, 235, 0.12);
    font-family: Manrope, "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--ink);
  }
  .loan-index-page .hero {
    background:
      radial-gradient(circle at 10% 10%, rgba(255, 255, 255, 0.7), transparent 50%),
      linear-gradient(120deg, var(--brand-soft), #ffffff 75%);
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
  }
  .loan-index-page .hero::after {
    content: "";
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(31, 111, 235, 0.1);
    right: -80px;
    bottom: -120px;
  }
  .loan-index-page .hero-body {
    position: relative;
    z-index: 1;
    padding: 1rem 1.2rem;
  }
  .loan-index-page .hero-title {
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 0.2rem;
  }
  .loan-index-page .hero-sub {
    color: var(--ink-soft);
    font-size: 0.84rem;
    margin: 0;
  }
  .loan-index-page .btn-modern {
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.45rem 0.75rem;
  }
  .loan-index-page .btn-action {
    border-radius: 10px;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.01em;
  }
  .loan-index-page .btn-outline-secondary.btn-action {
    background: #ffffff !important;
    border-color: #cfd7e6 !important;
    color: #344054 !important;
  }
  .loan-index-page .btn-outline-secondary.btn-action:hover,
  .loan-index-page .btn-outline-secondary.btn-action:focus,
  .loan-index-page .btn-outline-secondary.btn-action:active {
    background: #f2f5fb !important;
    border-color: #b9c5db !important;
    color: #1f2937 !important;
  }
  .loan-index-page .stat-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    height: 100%;
    padding: 0.8rem 0.85rem;
  }
  .loan-index-page .stat-label {
    color: var(--ink-soft);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
  }
  .loan-index-page .stat-value {
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.1;
  }
  .loan-index-page .stat-icon {
    align-items: center;
    border-radius: 10px;
    color: #fff;
    display: inline-flex;
    font-size: 0.85rem;
    height: 30px;
    justify-content: center;
    width: 30px;
  }
  .loan-index-page .table-shell {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }
  .loan-index-page .table-head {
    align-items: center;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    justify-content: space-between;
    padding: 0.9rem 1rem;
  }
  .loan-index-page .table-head h5 {
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    margin: 0;
    text-transform: uppercase;
  }
  .loan-index-page .request-tabs {
    align-items: center;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    padding: .75rem 1rem;
  }
  .loan-index-page .request-tab {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #d7e1ef;
    border-radius: 999px;
    color: #344054;
    display: inline-flex;
    font-size: .78rem;
    font-weight: 700;
    gap: .4rem;
    padding: .42rem .78rem;
    text-decoration: none;
    transition: all .15s ease;
  }
  .loan-index-page .request-tab:hover {
    background: #f0f6ff;
    border-color: #c5d8f8;
    color: #1d4f91;
    text-decoration: none;
  }
  .loan-index-page .request-tab.active {
    background: #1f6feb;
    border-color: #1f6feb;
    box-shadow: 0 8px 18px rgba(31, 111, 235, 0.2);
    color: #1d4f91;
    color: #ffffff;
  }
  .loan-index-page .request-tab-count {
    background: #ffffff;
    border: 1px solid #c9d8f5;
    border-radius: 999px;
    color: #1d4f91;
    display: inline-flex;
    font-size: .7rem;
    font-weight: 800;
    line-height: 1;
    min-width: 1.55rem;
    padding: .22rem .42rem;
  }
  .loan-index-page .request-tab.active .request-tab-count {
    background: rgba(255, 255, 255, .18);
    border-color: rgba(255, 255, 255, .32);
    color: #ffffff;
  }
  .loan-index-page #filter-chips-bar {
    border-bottom: 1px solid #eef2f7;
  }
  .loan-index-page #filter-chips .badge {
    border-radius: 999px;
  }
  .loan-index-page .table-responsive {
    padding: 0.85rem 1rem 1rem;
  }
  .loan-index-page #table-proposals {
    margin-bottom: 0 !important;
  }
  .loan-index-page #table-proposals thead th {
    border-top: 0;
    color: #667085;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .loan-index-page #table-proposals tbody td {
    font-size: 0.83rem;
    padding-top: 0.72rem;
    padding-bottom: 0.72rem;
    vertical-align: middle;
  }
  .loan-index-page .proposal-link {
    color: #1f6feb;
    font-weight: 700;
    text-decoration: none;
  }
  .loan-index-page .proposal-link:hover {
    text-decoration: underline;
  }
  .loan-index-page .pill {
    background: #f8faff;
    border: 1px solid #e6ecf8;
    border-radius: 999px;
    color: #475467;
    display: inline-flex;
    align-items: center;
    font-size: 0.74rem;
    font-weight: 700;
    gap: 0.3rem;
    padding: 0.25rem 0.55rem;
  }
  .loan-index-page .ux-status {
    border: 1px solid transparent;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    padding: 0.3rem 0.56rem;
    text-transform: uppercase;
  }
  .loan-index-page .ux-status--neutral { background:#eef2f6; border-color:#dde4ec; color:#475467; }
  .loan-index-page .ux-status--waiting { background:#fff6db; border-color:#ffe3a3; color:#8a6a13; }
  .loan-index-page .ux-status--review  { background:#e8f4ff; border-color:#cce6ff; color:#0f5c8e; }
  .loan-index-page .ux-status--active  { background:#e8f0ff; border-color:#cfdcff; color:#1e4eb7; }
  .loan-index-page .ux-status--success { background:#e8f8ef; border-color:#cdeed9; color:#1f7a3d; }
  .loan-index-page .ux-status--warning { background:#fff6db; border-color:#ffe3a3; color:#8a6a13; }
  .loan-index-page .ux-status--danger  { background:#ffefef; border-color:#ffd5d5; color:#b42318; }
  .loan-index-page .ux-status--dark    { background:#edf0f5; border-color:#d6dce7; color:#344054; }
  .loan-index-page .empty-box {
    border: 1px dashed #d6deea;
    border-radius: 14px;
    margin: 0.95rem;
    padding: 2rem 1rem;
    text-align: center;
  }
  #filter-overlay {
    background: rgba(2, 6, 23, 0.5) !important;
  }
  #filter-drawer {
    border-left: 1px solid #e4e9f1;
    box-shadow: -8px 0 24px rgba(15, 23, 42, 0.15) !important;
  }
  #filter-drawer .drawer-head {
    border-bottom: 1px solid #edf2f8;
  }
  #filter-drawer .drawer-title {
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }
  #filter-drawer .drawer-section-label {
    color: #667085;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }
  #filter-drawer .btn.btn-outline-secondary,
  #filter-drawer .btn.btn-outline-secondary.btn-sm,
  #filter-drawer .btn.btn-outline-secondary.btn-action {
    background: #ffffff !important;
    border-color: #cfd7e6 !important;
    color: #344054 !important;
  }
  #filter-drawer .btn.btn-outline-secondary:hover,
  #filter-drawer .btn.btn-outline-secondary:focus,
  #filter-drawer .btn.btn-outline-secondary:active,
  #filter-drawer .btn.btn-outline-secondary.active {
    background: #f2f5fb !important;
    border-color: #b9c5db !important;
    color: #1f2937 !important;
  }
  @media (max-width: 991.98px) {
    .loan-index-page .table-head {
      align-items: flex-start;
      flex-direction: column;
      gap: 0.65rem;
    }
    .loan-index-page .hero-body {
      padding: 0.9rem 1rem;
    }
  }
</style>

<div class="loan-index-page">
  <section class="hero">
    <div class="hero-body d-flex flex-wrap align-items-center justify-content-between">
      <div class="pr-3">
        <h1 class="hero-title">Daftar Proposal Peminjaman</h1>
        <p class="hero-sub">Pantau seluruh proses peminjaman lab dan alat dalam satu halaman yang lebih ringkas.</p>
      </div>
      <?php if (activeGroupCan('lending.request.create')): ?>
      <a href="<?= base_url('loans/create') ?>" class="btn btn-primary btn-modern btn-action mt-2 mt-md-0">
        <i class="fas fa-plus mr-1"></i>Buat Proposal
      </a>
      <?php endif; ?>
    </div>
  </section>

  <section class="mb-3">
    <div class="row">
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Total Proposal</span>
            <span class="stat-icon" style="background:#1f6feb"><i class="fas fa-file-alt"></i></span>
          </div>
          <div class="stat-value"><?= $counts['all'] ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3 mb-md-0">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Sedang Berjalan</span>
            <span class="stat-icon" style="background:#f59f00"><i class="fas fa-clock"></i></span>
          </div>
          <div class="stat-value"><?= $counts['active'] ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Sedang Dipakai</span>
            <span class="stat-icon" style="background:#2b8a3e"><i class="fas fa-play-circle"></i></span>
          </div>
          <div class="stat-value"><?= $counts['operational'] ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Ditolak / Batal</span>
            <span class="stat-icon" style="background:#c92a2a"><i class="fas fa-ban"></i></span>
          </div>
          <div class="stat-value"><?= $counts['closed'] ?></div>
        </div>
      </div>
    </div>
  </section>

  <div id="filter-overlay"
       style="display:none;position:fixed;inset:0;z-index:10000;transition:opacity .25s"
       onclick="closeFilterDrawer()"></div>

  <div id="filter-drawer"
       style="position:fixed;top:0;right:0;height:100%;width:340px;max-width:95vw;background:#fff;z-index:10001;
              transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column">

    <div class="drawer-head d-flex align-items-center justify-content-between px-4 py-3 flex-shrink-0">
      <h6 class="drawer-title mb-0"><i class="fas fa-filter mr-2 text-primary"></i>Filter Proposal</h6>
      <button type="button" class="btn btn-sm btn-light btn-action" onclick="closeFilterDrawer()"><i class="fas fa-times"></i></button>
    </div>

    <div class="flex-grow-1 overflow-auto px-4 py-3">
      <div class="mb-4">
        <div class="drawer-section-label mb-2">Tipe Peminjaman</div>
        <div class="d-flex flex-column" style="gap:.5rem">
          <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
            <input type="checkbox" class="filter-type" value="equipment" style="width:16px;height:16px">
            <span class="pill"><i class="fas fa-tools text-warning"></i>Alat</span>
          </label>
          <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
            <input type="checkbox" class="filter-type" value="lab" style="width:16px;height:16px">
            <span class="pill"><i class="fas fa-flask text-info"></i>Laboratorium</span>
          </label>
        </div>
      </div>

      <div class="mb-4">
        <div class="drawer-section-label mb-2">Status</div>
        <div class="d-flex flex-column" style="gap:.5rem">
          <?php foreach ($statusMap as $sKey => $sVal): ?>
          <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
            <input type="checkbox" class="filter-status" value="<?= $sKey ?>" style="width:16px;height:16px">
            <span class="ux-status ux-status--<?= esc($sVal['tone']) ?> px-2 py-1">
              <i class="fas <?= $sVal['icon'] ?> mr-1"></i><?= $sVal['label'] ?>
            </span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-4">
        <div class="drawer-section-label mb-2">Tanggal Pengajuan</div>
        <div class="form-group mb-2">
          <label class="small text-muted mb-1">Dari</label>
          <input type="date" id="filter-date-from" class="form-control form-control-sm">
        </div>
        <div class="form-group mb-0">
          <label class="small text-muted mb-1">Sampai</label>
          <input type="date" id="filter-date-to" class="form-control form-control-sm">
        </div>
      </div>
    </div>

    <div class="px-4 py-3 border-top flex-shrink-0 d-flex" style="gap:.5rem">
      <button type="button" class="btn btn-outline-secondary btn-sm btn-action flex-fill" onclick="resetFilters()">
        <i class="fas fa-undo mr-1"></i>Reset
      </button>
      <button type="button" class="btn btn-primary btn-sm btn-action flex-fill" onclick="applyFilters()">
        <i class="fas fa-check mr-1"></i>Terapkan
      </button>
    </div>
  </div>

  <section class="table-shell">
    <div class="request-tabs">
      <a href="<?= base_url('loans?tab=active') ?>" class="request-tab <?= $activeTab === 'active' ? 'active' : '' ?>">
        <i class="fas fa-clock"></i>Permohonan Aktif
        <span class="request-tab-count"><?= (int) ($tabCounts['active'] ?? 0) ?></span>
      </a>
      <a href="<?= base_url('loans?tab=archive') ?>" class="request-tab <?= $activeTab === 'archive' ? 'active' : '' ?>">
        <i class="fas fa-archive"></i>Archive
        <span class="request-tab-count"><?= (int) ($tabCounts['archive'] ?? 0) ?></span>
      </a>
    </div>

    <div class="table-head">
      <h5>Daftar Proposal</h5>
      <div class="d-flex align-items-center" style="gap:.5rem">
        <button type="button" class="btn btn-outline-secondary btn-modern btn-action" id="btn-filter-open" onclick="openFilterDrawer()">
          <i class="fas fa-filter mr-1"></i>Filter
          <span id="filter-badge" class="badge badge-primary ml-1" style="display:none">0</span>
        </button>
      </div>
    </div>

    <div id="filter-chips-bar" class="px-4 pb-3 pt-0" style="display:none">
      <div class="d-flex align-items-center flex-wrap" style="gap:.4rem">
        <span class="text-muted small mr-1">Filter aktif:</span>
        <div id="filter-chips" class="d-flex flex-wrap" style="gap:.4rem"></div>
        <button type="button" class="btn btn-link btn-sm text-danger p-0 ml-1" onclick="resetFilters()" style="font-size:.8rem">
          Hapus semua
        </button>
      </div>
    </div>

    <?php if (empty($proposals)): ?>
      <div class="empty-box">
        <div class="mb-2" style="font-size:2rem;color:#c2cbd8;"><i class="fas fa-clipboard-list"></i></div>
        <h5 class="mb-1" style="font-weight:800;"><?= $activeTab === 'archive' ? 'Archive Kosong' : 'Belum Ada Permohonan Aktif' ?></h5>
        <p class="text-muted mb-0">
          <?= $activeTab === 'archive'
            ? 'Belum ada proposal dengan status dibatalkan, ditolak, atau selesai.'
            : 'Belum ada proposal aktif. Proposal dengan status dibatalkan, ditolak, dan selesai muncul di tab Archive.' ?>
        </p>
        <?php if (activeGroupCan('lending.request.create')): ?>
        <a href="<?= base_url('loans/create') ?>" class="btn btn-primary btn-modern btn-action mt-3">
          <i class="fas fa-plus mr-1"></i>Buat Proposal Pertama
        </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover" id="table-proposals" width="100%">
          <thead>
            <tr>
              <th>Kode Proposal</th>
              <th>Pengusul</th>
              <th>Judul</th>
              <th>Tipe</th>
              <th>Mulai</th>
              <th>Selesai</th>
              <th>Item</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($proposals as $p):
              $si          = $statusMap[$p['status']] ?? ['label' => $p['status'], 'tone' => 'neutral', 'icon' => 'fa-question'];
              $isEquip     = ($p['loan_type'] ?? '') === 'equipment';
              $startFmt    = $p['start_at'] ? date('d M Y', strtotime($p['start_at'])) : '-';
              $endFmt      = $p['end_at'] ? date('d M Y', strtotime($p['end_at'])) : '-';
              $submittedRaw = substr($p['submitted_at'] ?? '', 0, 10);
            ?>
            <tr data-status="<?= esc($p['status']) ?>"
                data-type="<?= $isEquip ? 'equipment' : 'lab' ?>"
                data-submitted="<?= esc($submittedRaw) ?>">
              <td>
                <a href="<?= base_url('loans/' . ($p['public_id'] ?? '')) ?>" class="proposal-link">
                  <?= esc($p['proposal_code']) ?>
                </a>
              </td>
              <td><?= esc($p['proposer_name'] ?? '-') ?></td>
              <td><?= esc($p['title']) ?></td>
              <td>
                <?php if ($isEquip): ?>
                <span class="pill"><i class="fas fa-tools text-warning"></i>Alat</span>
                <?php else: ?>
                <span class="pill"><i class="fas fa-flask text-info"></i>Laboratorium</span>
                <?php endif; ?>
              </td>
              <td data-order="<?= esc($p['start_at'] ?? '') ?>"><?= $startFmt ?></td>
              <td data-order="<?= esc($p['end_at'] ?? '') ?>"><?= $endFmt ?></td>
              <td class="text-center">
                <span class="pill" style="padding:.2rem .5rem;"><?= (int) $p['total_items'] ?></span>
              </td>
              <td>
                <span class="ux-status ux-status--<?= esc($si['tone']) ?>">
                  <i class="fas <?= $si['icon'] ?> mr-1"></i><?= $si['label'] ?>
                </span>
              </td>
              <td class="text-center">
                <a href="<?= base_url('loans/' . ($p['public_id'] ?? '')) ?>" class="btn btn-sm btn-info btn-action" title="Lihat Detail">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  var activeTabKey = '<?= esc($activeTab) ?>';

  function syncTabInUrl() {
    try {
      var url = new URL(window.location.href);
      url.searchParams.set('tab', activeTabKey || 'active');
      window.history.replaceState({}, '', url.toString());
    } catch (e) {
      // Ignore URL API edge cases.
    }
  }

  // Ensure tab query param is always present so refresh keeps the same context.
  syncTabInUrl();

    // ── Portal drawer & overlay to <body> so position:fixed is viewport-relative
    //    (avoids being trapped inside Stisla's transform containers)
    document.body.appendChild(document.getElementById('filter-overlay'));
    document.body.appendChild(document.getElementById('filter-drawer'));

    // ── Filter state (declared first so search callback can reference them) ──
    var activeTypes    = [];
    var activeStatuses = [];
    var dateFrom       = '';
    var dateTo         = '';

    // ── DataTables ──────────────────────────────────────────
    var table = null;
    if (document.getElementById('table-proposals')) {

        table = $('#table-proposals').DataTable({
            order:      [[4, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            columnDefs: [{ orderable: false, targets: 8 }],
            language: {
                search:       'Cari:',
                lengthMenu:   'Tampilkan _MENU_ data',
                info:         'Menampilkan _START_–_END_ dari <strong>_TOTAL_</strong> proposal',
                infoEmpty:    'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total)',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                emptyTable:  'Belum ada data proposal.',
                zeroRecords: 'Tidak ada proposal yang cocok.',
            },
        });

        // Custom search — registered AFTER table is assigned to avoid null-ref on first draw
        $.fn.dataTable.ext.search.push(function (settings, _data, dataIndex) {
            if (!table || settings.nTable.id !== 'table-proposals') return true;
            var row       = table.row(dataIndex).node();
            var rowStatus = row.getAttribute('data-status')    || '';
            var rowType   = row.getAttribute('data-type')      || '';
            var rowSub    = row.getAttribute('data-submitted') || '';

            if (activeTypes.length    && !activeTypes.includes(rowType))      return false;
            if (activeStatuses.length && !activeStatuses.includes(rowStatus)) return false;
            if (dateFrom && rowSub && rowSub < dateFrom) return false;
            if (dateTo   && rowSub && rowSub > dateTo)   return false;
            if ((dateFrom || dateTo) && !rowSub)         return false;

            return true;
        });

          var dtSearchInput = document.querySelector('#table-proposals_filter input[type="search"]');
          if (dtSearchInput) {
            dtSearchInput.addEventListener('input', syncTabInUrl);
          }
    }

    // ── Status label map for chips ───────────────────────────
    var statusLabels = {
        draft:      'Draft',
        waiting_l1: 'Menunggu Laboran',
        waiting_l2: 'Menunggu Ka.Lab',
        approved:   'Disetujui',
      borrowed:   'Dipinjam',
      late:       'Terlambat',
      returned:   'Dikembalikan',
      problematic:'Bermasalah',
      in_use:     'Sedang Digunakan',
      completed:  'Selesai',
        rejected:   'Ditolak',
        canceled:   'Dibatalkan',
    };
    var typeLabels = { equipment: 'Alat', lab: 'Laboratorium' };

    // ── Drawer helpers ───────────────────────────────────────
    window.openFilterDrawer = function () {
        // Sync checkboxes to current state before opening
        document.querySelectorAll('.filter-type').forEach(function (cb) {
            cb.checked = activeTypes.includes(cb.value);
        });
        document.querySelectorAll('.filter-status').forEach(function (cb) {
            cb.checked = activeStatuses.includes(cb.value);
        });
        document.getElementById('filter-date-from').value = dateFrom;
        document.getElementById('filter-date-to').value   = dateTo;

        var overlay = document.getElementById('filter-overlay');
        var drawer  = document.getElementById('filter-drawer');
        overlay.style.display = 'block';
        requestAnimationFrame(function () {
            overlay.style.opacity = '1';
            drawer.style.transform = 'translateX(0)';
        });
    };

    window.closeFilterDrawer = function () {
        var overlay = document.getElementById('filter-overlay');
        var drawer  = document.getElementById('filter-drawer');
        drawer.style.transform = 'translateX(100%)';
        overlay.style.opacity  = '0';
        setTimeout(function () { overlay.style.display = 'none'; }, 260);
    };

    window.applyFilters = function () {
        activeTypes    = Array.from(document.querySelectorAll('.filter-type:checked')).map(function (cb) { return cb.value; });
        activeStatuses = Array.from(document.querySelectorAll('.filter-status:checked')).map(function (cb) { return cb.value; });
        dateFrom       = document.getElementById('filter-date-from').value;
        dateTo         = document.getElementById('filter-date-to').value;
        closeFilterDrawer();
        renderChips();
        syncTabInUrl();
        if (table) table.draw();
    };

    window.resetFilters = function () {
        activeTypes    = [];
        activeStatuses = [];
        dateFrom       = '';
        dateTo         = '';
        document.querySelectorAll('.filter-type, .filter-status').forEach(function (cb) { cb.checked = false; });
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value   = '';
        closeFilterDrawer();
        renderChips();
        syncTabInUrl();
        if (table) table.draw();
    };

    // ── Chip rendering ───────────────────────────────────────
    function renderChips() {
        var chips    = document.getElementById('filter-chips');
        var chipsBar = document.getElementById('filter-chips-bar');
        var badge    = document.getElementById('filter-badge');
        chips.innerHTML = '';

        var count = 0;

        activeTypes.forEach(function (v) {
            count++;
            chips.appendChild(makeChip(typeLabels[v] || v, function () {
                activeTypes = activeTypes.filter(function (x) { return x !== v; });
                renderChips();
              syncTabInUrl();
                if (table) table.draw();
            }));
        });

        activeStatuses.forEach(function (v) {
            count++;
            chips.appendChild(makeChip(statusLabels[v] || v, function () {
                activeStatuses = activeStatuses.filter(function (x) { return x !== v; });
                renderChips();
              syncTabInUrl();
                if (table) table.draw();
            }));
        });

        if (dateFrom || dateTo) {
            count++;
            var label = 'Pengajuan: ' + (dateFrom || '…') + ' – ' + (dateTo || '…');
            chips.appendChild(makeChip(label, function () {
                dateFrom = '';
                dateTo   = '';
                document.getElementById('filter-date-from').value = '';
                document.getElementById('filter-date-to').value   = '';
                renderChips();
              syncTabInUrl();
                if (table) table.draw();
            }));
        }

        var hasFilter = count > 0;
        chipsBar.style.display = hasFilter ? 'block' : 'none';
        badge.style.display    = hasFilter ? 'inline-block' : 'none';
        badge.textContent      = count;
    }

    function makeChip(label, onRemove) {
        var chip = document.createElement('span');
        chip.className = 'badge badge-primary d-inline-flex align-items-center px-2 py-1';
        chip.style.cssText = 'font-size:.78rem;font-weight:500;gap:.35rem;border-radius:999px';
        chip.innerHTML = '<span>' + label + '</span>';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<i class="fas fa-times" style="font-size:.65rem"></i>';
        btn.style.cssText = 'background:none;border:none;padding:0;color:inherit;line-height:1;cursor:pointer;margin-left:2px;opacity:.8';
        btn.addEventListener('click', onRemove);
        chip.appendChild(btn);
        return chip;
    }

    // Trap Escape key to close drawer
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeFilterDrawer();
    });
});
</script>
