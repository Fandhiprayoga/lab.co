<?php
$proposal            = $proposal ?? [];
$items               = $items ?? [];
$availableEquipments = $availableEquipments ?? [];
$availableLabs       = $availableLabs ?? [];
$loanType            = $proposal['loan_type'] ?? 'equipment';
$isEquipment         = $loanType === 'equipment';
$proposalPublicId    = (string) ($proposal['public_id'] ?? '');

$accentColor  = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg     = $isEquipment ? 'rgba(79,195,247,.08)' : 'rgba(129,199,132,.08)';
$accentBorder = $isEquipment ? '#4fc3f7' : '#81c784';
$typeLabel    = $isEquipment ? 'Alat' : 'Laboratorium';
$typeIcon     = $isEquipment ? 'fa-tools' : 'fa-door-open';

$addedIds = array_column($items, $isEquipment ? 'equipment_id' : 'lab_id');
$itemCount = count($items);
?>

<style>
  .loan-select-page {
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
  .loan-select-page .modern-shell {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    margin-bottom: 0.72rem;
    overflow: hidden;
  }
  .loan-select-page .modern-shell .shell-head {
    border-bottom: 1px solid #eef2f7;
    padding: .82rem .95rem;
  }
  .loan-select-page .modern-shell .shell-body {
    padding: .95rem;
  }
  .loan-select-page .modern-shell .shell-title {
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .06em;
    margin: 0;
    text-transform: uppercase;
  }
  .loan-select-page .summary-grid {
    display: grid;
    gap: .62rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .loan-select-page .summary-item {
    background: var(--surface-soft);
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: .68rem .72rem;
  }
  .loan-select-page .summary-label {
    color: var(--ink-soft);
    font-size: .7rem;
    letter-spacing: .04em;
    margin-bottom: .14rem;
    text-transform: uppercase;
  }
  .loan-select-page .summary-value {
    color: var(--ink);
    font-size: .84rem;
    font-weight: 700;
    line-height: 1.35;
  }
  .loan-select-page .objective-box {
    background: #fcfdff;
    border: 1px solid #e8edf5;
    border-radius: 12px;
    color: #374151;
    font-size: .84rem;
    line-height: 1.6;
    margin-top: .78rem;
    padding: .72rem .78rem;
  }
  .loan-select-page .catalog-layout {
    margin-top: .1rem;
  }
  .loan-select-page .focus-tabs {
    display: grid;
    gap: .6rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  .loan-select-page .focus-tab-btn {
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
  .loan-select-page .focus-tab-btn i {
    color: var(--brand);
    font-size: .92rem;
  }
  .loan-select-page .focus-tab-title {
    color: #111827;
    font-size: .76rem;
    font-weight: 800;
    line-height: 1.35;
  }
  .loan-select-page .focus-tab-sub {
    color: #667085;
    font-size: .7rem;
    line-height: 1.35;
  }
  .loan-select-page .focus-tab-count {
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
  .loan-select-page .focus-tab-btn.active .focus-tab-count {
    background: rgba(255,255,255,.88);
    border-color: rgba(31, 111, 235, 0.28);
    color: var(--brand);
  }
  .loan-select-page .focus-tab-btn:hover {
    border-color: #c8d3e6;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    color: #344054;
    text-decoration: none;
    transform: translateY(-1px);
  }
  .loan-select-page .focus-tab-btn.active {
    background: linear-gradient(165deg, #ffffff, var(--brand-soft));
    border-color: rgba(31, 111, 235, 0.28);
    box-shadow: inset 0 0 0 1px rgba(31, 111, 235, 0.12);
    color: #1d4f91;
  }
  .loan-select-page .focus-panel {
    display: none;
  }
  .loan-select-page .focus-panel.is-visible {
    display: block;
  }
  .loan-select-page .catalog-card { transition: box-shadow .15s, transform .15s; border:1px solid #e7ebf2; }
  .loan-select-page .catalog-card:hover { box-shadow: 0 8px 22px rgba(0,0,0,.1) !important; transform: translateY(-2px); }
  .loan-select-page .catalog-card.added { opacity: .9; }
  .loan-select-page .catalog-item {
    margin-bottom: .58rem !important;
  }
  .loan-select-page .catalog-grid {
    row-gap: .08rem;
  }
  .loan-select-page .btn-soft {
    border-radius: 10px;
    font-size: .8rem;
    font-weight: 700;
  }
  .loan-select-page #catalog-search {
    border-radius: 999px !important;
  }
  .loan-select-page .selected-grid {
    row-gap: .2rem;
  }
  .loan-select-page .selected-item {
    margin-bottom: .58rem !important;
  }
  .loan-select-page .selected-media {
    position: relative;
    height: <?= $isEquipment ? '120px' : '130px' ?>;
    overflow: hidden;
    border-radius: 4px 4px 0 0;
    background: <?= $isEquipment
      ? 'linear-gradient(135deg,rgba(79,195,247,.15),rgba(2,136,209,.1))'
      : 'linear-gradient(135deg,rgba(129,199,132,.2),rgba(56,142,60,.12))' ?>;
  }
  .loan-select-page .selected-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .loan-select-page .selected-media .fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: <?= $isEquipment ? 'rgba(2,136,209,.25)' : 'rgba(56,142,60,.25)' ?>;
    font-size: 2rem;
  }
  .loan-select-page .selected-name {
    font-size: .86rem;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: .18rem;
  }
  .loan-select-page .selected-meta {
    color: #667085;
    font-size: .73rem;
    margin-bottom: .55rem;
  }
  .loan-select-page .action-panel {
    border: 1px solid #e7ebf2;
    border-radius: 12px;
    padding: .82rem;
    background: #fff;
  }
  .loan-select-page .action-meta-grid {
    display: grid;
    gap: .55rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: .75rem;
  }
  .loan-select-page .action-meta-card {
    border: 1px solid #e7ebf2;
    border-radius: 10px;
    background: #f9fbff;
    padding: .55rem .62rem;
  }
  .loan-select-page .action-meta-label {
    color: #667085;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    margin-bottom: .16rem;
    text-transform: uppercase;
  }
  .loan-select-page .action-meta-value {
    color: #111827;
    font-size: .84rem;
    font-weight: 700;
    line-height: 1.3;
  }
  .loan-select-page .action-row {
    display: flex;
    justify-content: flex-end;
    margin-top: .2rem;
  }
  .loan-select-page .action-divider {
    border-top: 1px solid #edf1f6;
    margin: .62rem 0 .58rem;
  }
  .loan-select-page .action-btn-group {
    align-items: center;
    display: flex;
    gap: .58rem;
    margin-left: auto;
  }
  .loan-select-page .action-btn-group form {
    margin: 0;
  }
  .loan-select-page .btn-action-unified {
    border-width: 1px;
    border-radius: 10px;
    font-size: .8rem;
    font-weight: 700;
    min-width: 168px;
    padding: .42rem .8rem;
  }
  .loan-select-page .btn-submit-soft {
    color: #137d3f;
    border-color: #9ed8b3;
    background: #fff;
  }
  .loan-select-page .btn-submit-soft:hover,
  .loan-select-page .btn-submit-soft:focus {
    color: #0f6b36;
    border-color: #86c8a0;
    background: rgba(40, 167, 69, 0.08);
    box-shadow: none;
  }
  .loan-select-page .btn-cancel-soft {
    color: #b42318;
    border-color: #f3c6c3;
    background: #fff;
  }
  .loan-select-page .btn-cancel-soft:hover,
  .loan-select-page .btn-cancel-soft:focus {
    color: #991b1b;
    border-color: #e8aaa6;
    background: rgba(220, 53, 69, 0.08);
    box-shadow: none;
  }
  .loan-select-page .right-sticky {
    position: sticky;
    top: .9rem;
  }
  @media (max-width: 991.98px) {
    .loan-select-page .right-sticky { position: static; }
    .loan-select-page .focus-tabs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .loan-select-page .summary-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 575.98px) {
    .loan-select-page .focus-tabs { grid-template-columns: 1fr; }
    .loan-select-page .action-meta-grid { grid-template-columns: 1fr; }
    .loan-select-page .action-row { justify-content: stretch; }
    .loan-select-page .action-btn-group {
      flex-direction: column;
      margin-left: 0;
      width: 100%;
    }
    .loan-select-page .action-btn-group form,
    .loan-select-page .action-btn-group .btn {
      width: 100%;
    }
  }
</style>

<div class="loan-select-page">

<?php
  $startFmtDetail = ! empty($proposal['start_at']) ? date('d M Y, H:i', strtotime((string) $proposal['start_at'])) : '-';
  $endFmtDetail   = ! empty($proposal['end_at']) ? date('d M Y, H:i', strtotime((string) $proposal['end_at'])) : '-';
?>

<div class="modern-shell">
  <div class="shell-body py-2">
    <div class="focus-tabs" id="focus-tabs">
      <button type="button" class="focus-tab-btn" data-focus-tab="summary">
        <i class="fas fa-file-alt"></i>
        <span class="focus-tab-title">Ringkasan Proposal</span>
        <span class="focus-tab-sub">Lihat pengusul, periode, dan tujuan.</span>
      </button>
      <button type="button" class="focus-tab-btn active" data-focus-tab="catalog">
        <i class="fas <?= $typeIcon ?>"></i>
        <span class="focus-tab-title">Katalog Item</span>
        <span class="focus-tab-sub">Cari dan tambahkan item ke proposal.</span>
      </button>
      <button type="button" class="focus-tab-btn" data-focus-tab="selected">
        <i class="fas fa-shopping-cart"></i>
        <span class="d-flex align-items-center" style="gap:.35rem;">
          <span class="focus-tab-title">Item Dipilih</span>
          <span class="focus-tab-count"><?= (int) $itemCount ?></span>
        </span>
        <span class="focus-tab-sub">Review item sebelum diajukan.</span>
      </button>
      <button type="button" class="focus-tab-btn" data-focus-tab="actions">
        <i class="fas fa-paper-plane"></i>
        <span class="focus-tab-title">Kirim/Batal Proposal</span>
        <span class="focus-tab-sub">Lanjut approval atau batalkan proposal.</span>
      </button>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm focus-panel" id="focus-panel-summary">
  <div class="card-header bg-white py-3" style="border-bottom:1px solid #f0f0f0">
    <div class="d-flex align-items-center" style="gap:.65rem;">
      <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
           style="width:34px;height:34px;background:<?= $accentBg ?>">
        <i class="fas fa-file-alt" style="color:<?= $accentColor ?>;font-size:.85rem"></i>
      </div>
      <div>
        <div class="font-weight-bold mb-0" style="font-size:.9rem">Ringkasan Proposal</div>
        <div class="text-muted" style="font-size:.7rem">Informasi inti pengajuan peminjaman</div>
      </div>
    </div>
  </div>
  <div class="card-body p-3">
    <div class="summary-grid">
      <div class="summary-item">
        <div class="summary-label">Pengusul</div>
        <div class="summary-value"><?= esc($proposal['proposer_name'] ?? '-') ?></div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Tipe Peminjaman</div>
        <div class="summary-value"><i class="fas <?= $typeIcon ?> mr-1"></i><?= esc($typeLabel) ?></div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Waktu Mulai</div>
        <div class="summary-value"><?= $startFmtDetail ?></div>
      </div>
      <div class="summary-item">
        <div class="summary-label">Waktu Selesai</div>
        <div class="summary-value"><?= $endFmtDetail ?></div>
      </div>
    </div>
    <div class="objective-box">
      <?= nl2br(esc($proposal['objective'] ?? '-')) ?>
    </div>
  </div>
</div>

<?php /* ============ CATALOG LAYOUT ============ */ ?>
<div class="row catalog-layout focus-panel is-visible" id="focus-panel-row">

  <?php /* ---- LEFT: Catalog ---- */ ?>
  <div class="col-lg-12" id="focus-panel-catalog">
    <?php /* Catalog header + search */ ?>
    <?php $totalCatalog = $isEquipment ? count($availableEquipments) : count($availableLabs); ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3" style="border-bottom:1px solid #f0f0f0">
        <div class="d-flex align-items-center" style="gap:.75rem;width:100%">

          <!-- Title + count -->
          <div class="d-flex align-items-center" style="gap:.65rem">
            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:34px;height:34px;background:<?= $accentBg ?>">
              <i class="fas <?= $typeIcon ?>" style="color:<?= $accentColor ?>;font-size:.85rem"></i>
            </div>
            <div>
              <div class="font-weight-bold mb-0" style="font-size:.9rem">Katalog <?= $typeLabel ?> Tersedia</div>
              <div class="text-muted" style="font-size:.7rem">
                Menampilkan <strong id="catalog-count"><?= $totalCatalog ?></strong> dari <?= $totalCatalog ?> item
              </div>
            </div>
          </div>

          <!-- Search -->
          <div class="position-relative flex-shrink-0 ml-auto" style="width:280px">
            <i class="fas fa-search position-absolute"
               style="left:11px;top:50%;transform:translateY(-50%);color:#adb5bd;font-size:.78rem;pointer-events:none;z-index:1"></i>
            <input type="text" id="catalog-search"
                   class="form-control form-control-sm"
                   placeholder="Cari <?= $isEquipment ? 'nama alat, kategori…' : 'nama laboratorium…' ?>"
                   style="padding-left:30px;padding-right:30px;border-radius:20px;border-color:#dee2e6;font-size:.82rem">
            <button id="catalog-search-clear" type="button"
                    class="btn p-0 position-absolute d-none"
                    style="right:10px;top:50%;transform:translateY(-50%);line-height:1;color:#adb5bd;background:none;border:none">
              <i class="fas fa-times-circle" style="font-size:.85rem"></i>
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-3">
        <?php if ($isEquipment): ?>
        <?php if (empty($availableEquipments)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3 d-block text-light"></i>
            Tidak ada alat yang tersedia saat ini.
          </div>
        <?php else: ?>
        <div class="row catalog-grid" id="catalog-grid">
          <?php foreach ($availableEquipments as $eq):
            $alreadyAdded = in_array((string)$eq['id'], array_map('strval', $addedIds));
            $stockPct = $eq['stock_total'] > 0 ? round($eq['stock_available'] / $eq['stock_total'] * 100) : 0;
            $stockClass = $stockPct > 50 ? 'success' : ($stockPct > 20 ? 'warning' : 'danger');
          ?>
          <div class="col-sm-6 col-lg-4 col-xl-3 mb-2 catalog-item" data-name="<?= strtolower(esc($eq['name'])) ?> <?= strtolower(esc($eq['lab_name'] ?? '')) ?> <?= strtolower(esc($eq['category'] ?? '')) ?>">
            <div class="card border-0 shadow-sm h-100 catalog-card <?= $alreadyAdded ? 'added' : '' ?>">

              <div class="position-relative" style="height:130px;overflow:hidden;border-radius:4px 4px 0 0;background:linear-gradient(135deg,rgba(79,195,247,.15),rgba(2,136,209,.1))">
                <?php if (! empty($eq['photo'])): ?>
                  <img src="<?= base_url($eq['photo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                  <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.45),transparent)"></div>
                  <button type="button" class="view-photo-btn"
                          data-src="<?= base_url($eq['photo']) ?>"
                          data-caption="<?= esc($eq['name'], 'attr') ?>"
                          style="position:absolute;bottom:8px;right:8px;width:28px;height:28px;padding:0;border:none;border-radius:50%;background:rgba(255,255,255,.92);box-shadow:0 2px 8px rgba(0,0,0,.35);cursor:zoom-in;display:flex;align-items:center;justify-content:center;transition:transform .15s">
                    <i class="fas fa-expand-alt" style="font-size:.65rem;color:#444"></i>
                  </button>
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-tools fa-3x" style="color:rgba(2,136,209,.25)"></i>
                  </div>
                <?php endif; ?>
                <?php if ($alreadyAdded): ?>
                  <span class="badge badge-success" style="position:absolute;top:8px;right:8px;font-size:.72rem">
                    <i class="fas fa-check"></i>
                  </span>
                <?php endif; ?>
                <?php if (! empty($eq['category'])): ?>
                  <span class="badge badge-light" style="position:absolute;top:8px;left:8px;font-size:.68rem"><?= esc($eq['category']) ?></span>
                <?php endif; ?>
              </div>

              <div class="card-body p-3 d-flex flex-column">
                <div class="font-weight-bold mb-1" style="font-size:.88rem;line-height:1.3" title="<?= esc($eq['name']) ?>">
                  <?= esc($eq['name']) ?>
                </div>
                <div class="text-muted mb-2" style="font-size:.75rem">
                  <i class="fas fa-flask fa-xs mr-1"></i><?= esc($eq['lab_name'] ?? '-') ?>
                  <?php if (! empty($eq['lab_location'])): ?>
                    &bull; <i class="fas fa-map-marker-alt fa-xs mr-1"></i><?= esc($eq['lab_location']) ?>
                  <?php endif; ?>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="text-muted" style="font-size:.72rem">Stok tersedia</span>
                  <span class="badge badge-<?= $stockClass ?> px-2"><?= (int)$eq['stock_available'] ?> / <?= (int)$eq['stock_total'] ?></span>
                </div>
                <div class="progress mb-3" style="height:4px;border-radius:4px">
                  <div class="progress-bar bg-<?= $stockClass ?>" role="progressbar" style="width:<?= $stockPct ?>%"></div>
                </div>

                <?php if ($alreadyAdded): ?>
                  <div class="text-success text-center py-1 small font-weight-semibold border rounded mt-auto" style="background:rgba(40,167,69,.06)">
                    <i class="fas fa-check-circle mr-1"></i>Sudah ada dalam proposal
                  </div>
                <?php else: ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/items/equipment') ?>" method="post" class="mt-auto add-item-form">
                  <?= csrf_field() ?>
                  <input type="hidden" name="equipment_id" value="<?= (int)$eq['id'] ?>">
                  <!-- Qty stepper + submit in one row -->
                  <div class="d-flex align-items-center" style="gap:.4rem">
                    <div class="d-flex align-items-center border rounded flex-shrink-0" style="height:32px;overflow:hidden">
                      <button type="button" class="btn btn-light border-0 qty-btn qty-minus px-2"
                              style="height:32px;line-height:1" data-min="1">
                        <i class="fas fa-minus" style="font-size:.55rem"></i>
                      </button>
                      <input type="number" name="qty"
                             class="form-control border-0 text-center px-0 qty-input"
                             min="1" max="<?= (int)$eq['stock_available'] ?>" value="1" required
                             style="width:38px;height:32px;font-size:.82rem;font-weight:600;appearance:textfield;-moz-appearance:textfield;-webkit-appearance:none">
                      <button type="button" class="btn btn-light border-0 qty-btn qty-plus px-2"
                              style="height:32px;line-height:1" data-max="<?= (int)$eq['stock_available'] ?>">
                        <i class="fas fa-plus" style="font-size:.55rem"></i>
                      </button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1 font-weight-semibold"
                            style="height:32px;font-size:.78rem">
                      <i class="fas fa-cart-plus mr-1"></i>Tambah
                    </button>
                  </div>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <?php if (empty($availableLabs)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-door-open fa-3x mb-3 d-block text-light"></i>
            Tidak ada laboratorium yang tersedia saat ini.
          </div>
        <?php else: ?>
        <div class="row catalog-grid" id="catalog-grid">
          <?php foreach ($availableLabs as $lab):
            $alreadyAdded = in_array((string)$lab['id'], array_map('strval', $addedIds));
          ?>
          <div class="col-sm-6 col-lg-4 col-xl-3 mb-2 catalog-item" data-name="<?= strtolower(esc($lab['name'])) ?> <?= strtolower(esc($lab['location'] ?? '')) ?> <?= strtolower(esc($lab['code'] ?? '')) ?>">
            <div class="card border-0 shadow-sm h-100 catalog-card <?= $alreadyAdded ? 'added' : '' ?>">

              <div class="position-relative" style="height:130px;overflow:hidden;border-radius:4px 4px 0 0;background:linear-gradient(135deg,rgba(129,199,132,.2),rgba(56,142,60,.12))">
                <?php if (! empty($lab['logo'])): ?>
                  <img src="<?= base_url($lab['logo']) ?>" alt="" style="width:100%;height:100%;object-fit:cover">
                  <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.5),transparent)"></div>
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-door-open fa-3x" style="color:rgba(56,142,60,.25)"></i>
                  </div>
                <?php endif; ?>
                <?php if ($alreadyAdded): ?>
                  <span class="badge badge-success" style="position:absolute;top:8px;right:8px;font-size:.72rem">
                    <i class="fas fa-check"></i>
                  </span>
                <?php endif; ?>
                <?php if (! empty($lab['code'])): ?>
                  <span class="badge badge-light" style="position:absolute;top:8px;left:8px;font-size:.68rem"><?= esc($lab['code']) ?></span>
                <?php endif; ?>
                <?php if (! empty($lab['logo']) && ! empty($lab['name'])): ?>
                  <div style="position:absolute;bottom:8px;left:10px;right:10px">
                    <div class="text-white font-weight-bold" style="font-size:.85rem;text-shadow:0 1px 3px rgba(0,0,0,.6);line-height:1.2">
                      <?= esc($lab['name']) ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-body p-3 d-flex flex-column">
                <?php if (empty($lab['logo'])): ?>
                  <div class="font-weight-bold mb-1" style="font-size:.88rem;line-height:1.3"><?= esc($lab['name']) ?></div>
                <?php endif; ?>
                <div class="mb-2">
                  <?php if (! empty($lab['code'])): ?>
                    <div class="text-muted" style="font-size:.73rem"><i class="fas fa-hashtag fa-xs mr-1"></i><?= esc($lab['code']) ?></div>
                  <?php endif; ?>
                  <?php if (! empty($lab['location'])): ?>
                    <div class="text-muted" style="font-size:.73rem"><i class="fas fa-map-marker-alt fa-xs mr-1"></i><?= esc($lab['location']) ?></div>
                  <?php endif; ?>
                  <?php if (! empty($lab['capacity'])): ?>
                    <div class="text-muted" style="font-size:.73rem"><i class="fas fa-users fa-xs mr-1"></i>Kapasitas <?= (int)$lab['capacity'] ?> orang</div>
                  <?php endif; ?>
                </div>
                <?php if ($alreadyAdded): ?>
                  <div class="text-success text-center py-1 small font-weight-semibold border rounded mt-auto" style="background:rgba(40,167,69,.06)">
                    <i class="fas fa-check-circle mr-1"></i>Sudah ada dalam proposal
                  </div>
                <?php else: ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/items/lab') ?>" method="post" class="mt-auto">
                  <?= csrf_field() ?>
                  <input type="hidden" name="lab_id" value="<?= (int)$lab['id'] ?>">
                  <button type="submit" class="btn btn-success btn-sm btn-block font-weight-semibold">
                    <i class="fas fa-plus mr-1"></i>Tambah ke Proposal
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Pagination -->
        <div id="catalog-pagination" class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top d-none">
          <div class="text-muted" id="catalog-page-info" style="font-size:.75rem"></div>
          <nav aria-label="Navigasi katalog">
            <ul class="pagination pagination-sm mb-0" id="catalog-page-list"></ul>
          </nav>
        </div>

        <div id="catalog-empty" class="text-center py-4 text-muted d-none">
          <i class="fas fa-search fa-2x mb-2 d-block text-light"></i>
          Tidak ada hasil yang cocok.
        </div>
      </div>
    </div>
  </div>

  <?php /* ---- RIGHT: Summary + Selected Items + Actions ---- */ ?>
  <div class="col-lg-12" id="focus-panel-side" style="display:none;">

    <div class="card border-0 shadow-sm mb-3 focus-panel is-visible" id="focus-panel-selected" style="border-radius:10px;overflow:hidden">
      <div class="card-header bg-white py-3" style="border-bottom:1px solid #f0f0f0">
        <div class="d-flex align-items-center justify-content-between" style="gap:.75rem;">
          <div class="d-flex align-items-center" style="gap:.65rem;">
            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:34px;height:34px;background:<?= $accentBg ?>">
              <i class="fas fa-shopping-cart" style="color:<?= $accentColor ?>;font-size:.85rem"></i>
            </div>
            <div>
              <div class="font-weight-bold mb-0" style="font-size:.9rem">Item Dipilih</div>
              <div class="text-muted" style="font-size:.7rem"><?= $typeLabel ?> yang akan dipinjam</div>
            </div>
          </div>
        </div>
      </div>

      <?php if (empty($items)): ?>
        <div class="card-body text-center py-4 px-3">
          <i class="fas fa-inbox fa-2x mb-2 d-block" style="color:#d0d7e3"></i>
          <div class="font-weight-semibold text-dark mb-1" style="font-size:.85rem">Belum ada item</div>
          <div class="text-muted" style="font-size:.75rem;line-height:1.5;">
            Pilih <?= strtolower($typeLabel) ?> dari katalog<br>lalu klik <strong>"Tambah ke Proposal"</strong>
          </div>
        </div>
      <?php else: ?>
        <div class="px-3 py-3">
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

                  <form action="<?= base_url('loans/' . $proposalPublicId . '/items/' . ($item['public_id'] ?? '') . '/delete') ?>" method="post"
                        class="js-swal-delete-form mt-auto"
                        data-swal-title="Hapus item?"
                        data-swal-text="Item ini akan dihapus dari proposal."
                        data-swal-confirm="Ya, hapus"
                        data-swal-cancel="Batal">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-light btn-sm btn-block font-weight-semibold" style="border:1px solid #e5e9f2;">
                      <i class="fas fa-times-circle mr-1 text-danger"></i>Hapus dari Proposal
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="focus-panel" id="focus-panel-actions">
      <?php
        $canSubmit = activeGroupCan('lending.request.submit');
        $canCancel = activeGroupCan('lending.request.cancel')
          && (((int) ($proposal['proposer_id'] ?? 0) === (int) auth()->id()) || activeGroupCan('lending.request.manage-all'));
      ?>

      <?php if ($canSubmit || $canCancel): ?>
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-3" style="border-bottom:1px solid #f0f0f0">
          <div class="d-flex align-items-center" style="gap:.65rem;">
            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:34px;height:34px;background:rgba(40,167,69,.12)">
              <i class="fas fa-paper-plane" style="color:#1f8f48;font-size:.85rem"></i>
            </div>
            <div>
              <div class="font-weight-bold mb-0" style="font-size:.9rem">Tindak Lanjut Proposal</div>
              <div class="text-muted" style="font-size:.7rem">Finalisasi lalu kirim untuk approval berjenjang</div>
            </div>
          </div>
        </div>
        <div class="card-body p-3">
          <div class="action-panel">
            <div class="action-meta-grid">
              <div class="action-meta-card">
                <div class="action-meta-label">Item Terpilih</div>
                <div class="action-meta-value"><?= (int) $itemCount ?> item</div>
              </div>
              <div class="action-meta-card">
                <div class="action-meta-label">Status Pengajuan</div>
                <div class="action-meta-value" style="color:<?= empty($items) ? '#b54708' : '#1f8f48' ?>;">
                  <?= empty($items) ? 'Belum siap kirim' : 'Siap dikirim' ?>
                </div>
              </div>
            </div>

            <?php if (empty($items)): ?>
              <div class="text-muted mb-2" style="font-size:.76rem;">
                <i class="fas fa-info-circle mr-1"></i>Tambahkan minimal 1 item agar proposal bisa dikirim ke approval.
              </div>
            <?php endif; ?>

            <div class="action-divider"></div>

            <div class="action-row">
              <div class="action-btn-group">
              <?php if ($canSubmit): ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/submit') ?>" method="post"
                      class="js-swal-confirm-form js-preserve-tab-form"
                      data-swal-title="Kirim Proposal?"
                      data-swal-text="Proposal akan dikirim untuk proses approval. Pastikan item sudah lengkap."
                      data-swal-confirm="Ya, Kirim"
                      data-swal-cancel="Batal"
                      data-swal-icon="question"
                      data-swal-confirm-color="#28a745">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-outline-success btn-action-unified btn-submit-soft" <?= empty($items) ? 'disabled' : '' ?>>
                    <i class="fas fa-paper-plane mr-1"></i>Kirim Approval
                  </button>
                </form>
              <?php endif; ?>

              <?php if ($canCancel): ?>
                <form action="<?= base_url('loans/' . $proposalPublicId . '/cancel') ?>" method="post"
                      class="js-swal-cancel-form js-preserve-tab-form"
                      data-swal-title="Batalkan Proposal?"
                      data-swal-text="Proposal akan dibatalkan dan tidak diproses lebih lanjut."
                      data-swal-confirm="Ya, Batalkan"
                      data-swal-cancel="Batal"
                      data-swal-icon="warning"
                      data-swal-confirm-color="#dc3545"
                      data-swal-reason-label="Alasan pembatalan"
                      data-swal-reason-placeholder="Tulis alasan pembatalan proposal..."
                      data-swal-reason-required="Alasan pembatalan wajib diisi.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="cancel_reason" value="">
                  <button type="submit" class="btn btn-outline-danger btn-action-unified btn-cancel-soft">
                    <i class="fas fa-ban mr-1"></i>Batalkan Proposal
                  </button>
                </form>
              <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body p-3 text-muted" style="font-size:.8rem;">
          Tidak ada aksi yang tersedia untuk status/proposal ini.
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

</div>

<!-- Image lightbox -->
<div id="img-lightbox" style="display:none;position:fixed;inset:0;z-index:10050;background:rgba(0,0,0,.88);align-items:center;justify-content:center;cursor:zoom-out">
  <button id="img-lightbox-close" type="button"
          style="position:absolute;top:14px;right:18px;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1rem;cursor:pointer;transition:background .15s">
    <i class="fas fa-times"></i>
  </button>
  <div style="max-width:92vw;max-height:90vh;display:flex;flex-direction:column;align-items:center;gap:.6rem" onclick="event.stopPropagation()">
    <img id="img-lightbox-img" src="" alt=""
         style="max-width:100%;max-height:78vh;border-radius:8px;box-shadow:0 12px 48px rgba(0,0,0,.6);object-fit:contain">
    <div id="img-lightbox-caption" style="color:rgba(255,255,255,.75);font-size:.82rem;text-align:center"></div>
  </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.js-swal-confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title:             form.dataset.swalTitle   || 'Konfirmasi?',
                text:              form.dataset.swalText    || '',
                icon:              form.dataset.swalIcon    || 'question',
                showCancelButton:  true,
                confirmButtonText: form.dataset.swalConfirm || 'Ya',
                cancelButtonText:  form.dataset.swalCancel  || 'Batal',
          confirmButtonColor: form.dataset.swalConfirmColor || '#28a745',
                reverseButtons: true,
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });

        document.querySelectorAll('.js-swal-cancel-form').forEach(function (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
              title:             form.dataset.swalTitle   || 'Batalkan Proposal?',
              text:              form.dataset.swalText    || '',
              icon:              form.dataset.swalIcon    || 'warning',
              input:             'textarea',
              inputLabel:        form.dataset.swalReasonLabel || 'Alasan pembatalan',
              inputPlaceholder:  form.dataset.swalReasonPlaceholder || 'Tulis alasan pembatalan...',
              inputAttributes:   { 'aria-label': form.dataset.swalReasonLabel || 'Alasan pembatalan' },
              inputAutoTrim:     true,
              showCancelButton:  true,
              confirmButtonText: form.dataset.swalConfirm || 'Ya, Batalkan',
              cancelButtonText:  form.dataset.swalCancel  || 'Batal',
              confirmButtonColor: form.dataset.swalConfirmColor || '#dc3545',
              reverseButtons:    true,
              inputValidator: function (value) {
                if (!value || !value.trim()) {
                  return form.dataset.swalReasonRequired || 'Alasan pembatalan wajib diisi.';
                }
              },
            }).then(function (result) {
              if (!result.isConfirmed) return;
              var reasonInput = form.querySelector('input[name="cancel_reason"]');
              if (reasonInput) {
                reasonInput.value = (result.value || '').trim();
              }
              form.submit();
            });
          });
        });

      var focusTabs      = document.querySelectorAll('[data-focus-tab]');
      var summaryPanel   = document.getElementById('focus-panel-summary');
      var rowPanel       = document.getElementById('focus-panel-row');
      var catalogPanel   = document.getElementById('focus-panel-catalog');
      var sidePanel      = document.getElementById('focus-panel-side');
      var selectedPanel  = document.getElementById('focus-panel-selected');
      var actionsPanel   = document.getElementById('focus-panel-actions');
      var tabStorageKey  = 'loanSelectActiveTab';

      function normalizeTabName(tabName) {
        var allowed = ['summary', 'catalog', 'selected', 'actions'];
        return allowed.indexOf(tabName) !== -1 ? tabName : 'catalog';
      }

      function currentActiveTab() {
        var activeBtn = document.querySelector('[data-focus-tab].active');
        return normalizeTabName(activeBtn ? activeBtn.getAttribute('data-focus-tab') : 'catalog');
      }

      function updateTabInUrl(tabName) {
        try {
          var url = new URL(window.location.href);
          url.searchParams.set('tab', tabName);
          window.history.replaceState({}, '', url.toString());
        } catch (e) {
          // Ignore URL parsing issues in older/edge runtimes.
        }
      }

      function setFocusTab(tabName) {
        tabName = normalizeTabName(tabName);
        focusTabs.forEach(function (btn) {
          btn.classList.toggle('active', btn.getAttribute('data-focus-tab') === tabName);
        });

        if (summaryPanel) summaryPanel.classList.toggle('is-visible', tabName === 'summary');

        if (rowPanel) rowPanel.classList.toggle('is-visible', tabName !== 'summary');
        if (catalogPanel) catalogPanel.style.display = tabName === 'catalog' ? '' : 'none';
        if (sidePanel) sidePanel.style.display = (tabName === 'selected' || tabName === 'actions') ? '' : 'none';
        if (selectedPanel) selectedPanel.classList.toggle('is-visible', tabName === 'selected');
        if (actionsPanel) actionsPanel.classList.toggle('is-visible', tabName === 'actions');

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

      setFocusTab(urlTab || savedTab || 'catalog');

      document.querySelectorAll('.js-swal-delete-form').forEach(function (form) {
        form.addEventListener('submit', function () {
          var activeTab = currentActiveTab();
          try {
            var actionUrl = new URL(form.getAttribute('action'), window.location.origin);
            actionUrl.searchParams.set('tab', activeTab);
            form.setAttribute('action', actionUrl.pathname + actionUrl.search);
          } catch (e) {
            var action = form.getAttribute('action') || '';
            var separator = action.indexOf('?') === -1 ? '?' : '&';
            form.setAttribute('action', action + separator + 'tab=' + encodeURIComponent(activeTab));
          }
        });
      });

      document.querySelectorAll('.js-preserve-tab-form').forEach(function (form) {
        form.addEventListener('submit', function () {
          var activeTab = currentActiveTab();
          try {
            var actionUrl = new URL(form.getAttribute('action'), window.location.origin);
            actionUrl.searchParams.set('tab', activeTab);
            form.setAttribute('action', actionUrl.pathname + actionUrl.search);
          } catch (e) {
            var action = form.getAttribute('action') || '';
            var separator = action.indexOf('?') === -1 ? '?' : '&';
            form.setAttribute('action', action + separator + 'tab=' + encodeURIComponent(activeTab));
          }
        });
      });

    var ITEMS_PER_PAGE  = 8;
    var catalogPage     = 1;
    var catalogFiltered = [];

    var searchInput = document.getElementById('catalog-search');
    var clearBtn    = document.getElementById('catalog-search-clear');
    var grid        = document.getElementById('catalog-grid');
    var emptyState  = document.getElementById('catalog-empty');
    var countBadge  = document.getElementById('catalog-count');
    var pagination  = document.getElementById('catalog-pagination');
    var pageList    = document.getElementById('catalog-page-list');
    var pageInfo    = document.getElementById('catalog-page-info');
    if (!grid) return;

    // Build initial filtered list (all items)
    catalogFiltered = Array.prototype.slice.call(grid.querySelectorAll('.catalog-item'));

    function renderPage() {
        var total      = catalogFiltered.length;
        var totalPages = Math.ceil(total / ITEMS_PER_PAGE);
        var start      = (catalogPage - 1) * ITEMS_PER_PAGE;
        var end        = start + ITEMS_PER_PAGE;

        // Hide all, then show current-page slice
        var allCards = grid.querySelectorAll('.catalog-item');
        allCards.forEach(function (el) { el.style.display = 'none'; });
        catalogFiltered.slice(start, end).forEach(function (el) { el.style.display = ''; });

        // Update header count badge
        if (countBadge) countBadge.textContent = total;

        // Empty state
        if (emptyState) emptyState.classList.toggle('d-none', total > 0);

        // Pagination bar
        if (!pagination || !pageList) return;
        if (total === 0 || totalPages <= 1) {
            pagination.classList.add('d-none');
            return;
        }
        pagination.classList.remove('d-none');

        // Page info text
        if (pageInfo) {
            pageInfo.textContent = 'Item ' + (start + 1) + '\u2013' + Math.min(end, total) + ' dari ' + total;
        }

        // Build page buttons
        pageList.innerHTML = '';
        var startPage = Math.max(1, catalogPage - 2);
        var endPage   = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

        function makeItem(label, page, disabled, active) {
            var li  = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var a   = document.createElement('a');
            a.className   = 'page-link';
            a.href        = '#';
            a.innerHTML   = label;
            a.dataset.page = page;
            li.appendChild(a);
            return li;
        }

        pageList.appendChild(makeItem('&laquo;', catalogPage - 1, catalogPage === 1, false));
        for (var i = startPage; i <= endPage; i++) {
            pageList.appendChild(makeItem(i, i, false, i === catalogPage));
        }
        pageList.appendChild(makeItem('&raquo;', catalogPage + 1, catalogPage === totalPages, false));

        // Click handlers
        pageList.querySelectorAll('a.page-link').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var p = parseInt(this.dataset.page);
                if (!isNaN(p) && p >= 1 && p <= totalPages && p !== catalogPage) {
                    catalogPage = p;
                    renderPage();
                    grid.closest('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function runSearch(q) {
        var allCards = grid.querySelectorAll('.catalog-item');
        catalogFiltered = [];
        allCards.forEach(function (el) {
            if ((el.getAttribute('data-name') || '').includes(q)) catalogFiltered.push(el);
        });
        catalogPage = 1;
        renderPage();
    }

    // Initial render
    renderPage();

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            if (clearBtn) clearBtn.classList.toggle('d-none', q === '');
            runSearch(q);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchInput) { searchInput.value = ''; searchInput.focus(); }
            clearBtn.classList.add('d-none');
            runSearch('');
        });
    }

    // --- Lightbox ---
    var lightbox  = document.getElementById('img-lightbox');
    var lbImg     = document.getElementById('img-lightbox-img');
    var lbCaption = document.getElementById('img-lightbox-caption');
    if (lightbox) document.body.appendChild(lightbox); // portal out of any transform container

    function openLightbox(src, caption) {
        if (!lightbox || !lbImg) return;
        lbImg.src             = src;
        lbImg.alt             = caption || '';
        if (lbCaption) lbCaption.textContent = caption || '';
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        if (!lightbox) return;
        lightbox.style.display = 'none';
        if (lbImg) lbImg.src = '';
        document.body.style.overflow = '';
    }

    // Open on view-photo-btn click
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.view-photo-btn');
        if (!btn) return;
        e.stopPropagation();
        openLightbox(btn.dataset.src, btn.dataset.caption);
    });

    // Close on overlay click or close button
    if (lightbox) {
        lightbox.addEventListener('click', closeLightbox);
        var closeBtn = document.getElementById('img-lightbox-close');
        if (closeBtn) closeBtn.addEventListener('click', function (e) { e.stopPropagation(); closeLightbox(); });
    }

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox();
    });

    // Hover lift on view-photo-btn
    document.addEventListener('mouseenter', function (e) {
        if (e.target.closest && e.target.closest('.view-photo-btn')) e.target.closest('.view-photo-btn').style.transform = 'scale(1.15)';
    }, true);
    document.addEventListener('mouseleave', function (e) {
        if (e.target.closest && e.target.closest('.view-photo-btn')) e.target.closest('.view-photo-btn').style.transform = 'scale(1)';
    }, true);

    // Qty stepper — delegated so it works on all paginated cards
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.qty-btn');
        if (!btn) return;
        var input = btn.closest('.d-flex').querySelector('.qty-input');
        if (!input) return;
        var val = parseInt(input.value) || 1;
        var min = parseInt(input.min) || 1;
        var max = parseInt(input.max) || 9999;
        if (btn.classList.contains('qty-minus')) val = Math.max(min, val - 1);
        if (btn.classList.contains('qty-plus'))  val = Math.min(max, val + 1);
        input.value = val;
        // Visual feedback on stepper bounds
        var minusBtn = btn.closest('.d-flex').querySelector('.qty-minus');
        var plusBtn  = btn.closest('.d-flex').querySelector('.qty-plus');
        if (minusBtn) minusBtn.disabled = val <= min;
        if (plusBtn)  plusBtn.disabled  = val >= max;
    });

    // Clamp on direct input
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('qty-input')) return;
        var input = e.target;
        var val = parseInt(input.value) || 1;
        var min = parseInt(input.min) || 1;
        var max = parseInt(input.max) || 9999;
        input.value = Math.min(max, Math.max(min, val));
    });
});
</script>
