<?php
$proposal            = $proposal ?? [];
$items               = $items ?? [];
$availableEquipments = $availableEquipments ?? [];
$availableLabs       = $availableLabs ?? [];
$proposalStatus      = $proposal['status'] ?? 'draft';
$loanType            = $proposal['loan_type'] ?? 'equipment';
$isEquipment         = $loanType === 'equipment';
$proposalId          = (int) ($proposal['id'] ?? 0);

$accentColor  = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg     = $isEquipment ? 'rgba(79,195,247,.08)' : 'rgba(129,199,132,.08)';
$accentBorder = $isEquipment ? '#4fc3f7' : '#81c784';
$typeLabel    = $isEquipment ? 'Alat' : 'Laboratorium';
$typeIcon     = $isEquipment ? 'fa-tools' : 'fa-door-open';

$addedIds = array_column($items, $isEquipment ? 'equipment_id' : 'lab_id');

$statusMap = [
    'draft'      => ['label' => 'Draft',           'class' => 'secondary'],
    'waiting_l1' => ['label' => 'Menunggu Laboran', 'class' => 'warning'],
    'waiting_l2' => ['label' => 'Menunggu Ka.Lab',  'class' => 'info'],
    'approved'   => ['label' => 'Disetujui',         'class' => 'success'],
    'rejected'   => ['label' => 'Ditolak',           'class' => 'danger'],
    'canceled'   => ['label' => 'Dibatalkan',        'class' => 'dark'],
];
$statusInfo = $statusMap[$proposalStatus] ?? ['label' => $proposalStatus, 'class' => 'secondary'];
?>

<?php
function selectItemsStep(int $step, int $current, string $label, string $sublabel): string {
    if ($step < $current) {
        $circle = '<div class="d-flex align-items-center justify-content-center rounded-circle bg-success text-white" style="width:32px;height:32px;font-size:.8rem;flex-shrink:0"><i class="fas fa-check"></i></div>';
        $textClass = 'text-success';
        $labelStyle = '';
    } elseif ($step === $current) {
        $circle = '<div class="d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold" style="width:32px;height:32px;font-size:.82rem;background:#6777ef;flex-shrink:0">' . $step . '</div>';
        $textClass  = '';
        $labelStyle = 'color:#6777ef';
    } else {
        $circle = '<div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-muted font-weight-bold border" style="width:32px;height:32px;font-size:.82rem;flex-shrink:0">' . $step . '</div>';
        $textClass  = 'text-muted';
        $labelStyle = '';
    }
    return '<div class="d-flex align-items-center flex-shrink-0">'
        . $circle
        . '<div class="ml-2">'
        . '<div class="font-weight-bold small ' . $textClass . '" style="' . $labelStyle . '">' . $label . '</div>'
        . '<div class="text-muted" style="font-size:.72rem">' . $sublabel . '</div>'
        . '</div></div>';
}
$currentStep = 2;
?>

<?php /* ============ STEP WIZARD ============ */ ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-center">
      <a href="<?= base_url('loans/create?type=' . $loanType) ?>" class="text-decoration-none">
        <?= selectItemsStep(1, $currentStep, 'Step 1', 'Info Proposal') ?>
      </a>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#28a745"></div>
      <?= selectItemsStep(2, $currentStep, 'Step 2', 'Pilih ' . $typeLabel) ?>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#e9ecef"></div>
      <div class="text-decoration-none text-muted">
        <?= selectItemsStep(3, $currentStep, 'Step 3', 'Kirim Approval') ?>
      </div>
    </div>
  </div>
</div>

<?php /* ============ CATALOG LAYOUT ============ */ ?>
<div class="row">

  <?php /* ---- LEFT: Catalog ---- */ ?>
  <div class="col-lg-8">

    <?php /* ---- Proposal info card ---- */ ?>
    <?php
      $startFmt = ! empty($proposal['start_at']) ? date('d M Y', strtotime($proposal['start_at'])) : '-';
      $endFmt   = ! empty($proposal['end_at'])   ? date('d M Y', strtotime($proposal['end_at']))   : '-';
    ?>
    <div class="card border-0 shadow-sm mb-3 overflow-hidden">
      <!-- Accent bar + main info row -->
      <div style="border-left:4px solid <?= $accentColor ?>;background:linear-gradient(135deg,<?= $accentBg ?>,#fff 60%)">
        <div class="px-4 py-3 d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem">

          <!-- Left: icon + title + code -->
          <div class="d-flex align-items-center" style="gap:.75rem;min-width:0">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:<?= $accentBg ?>;border:2px solid <?= $accentBorder ?>">
              <i class="fas <?= $typeIcon ?>" style="color:<?= $accentColor ?>"></i>
            </div>
            <div style="min-width:0">
              <div class="font-weight-bold text-truncate" style="font-size:.95rem;max-width:260px">
                <?= esc($proposal['title'] ?? '-') ?>
              </div>
              <div class="d-flex align-items-center flex-wrap mt-1" style="gap:.35rem">
                <code class="small px-2 py-0 rounded" style="background:rgba(0,0,0,.06);color:inherit;font-size:.72rem">
                  <?= esc($proposal['proposal_code'] ?? '-') ?>
                </code>
                <span class="badge badge-<?= $isEquipment ? 'primary' : 'success' ?>" style="font-size:.68rem">
                  <i class="fas <?= $typeIcon ?> mr-1"></i><?= $typeLabel ?>
                </span>
                <span class="badge badge-<?= $statusInfo['class'] ?>" style="font-size:.68rem">
                  <?= $statusInfo['label'] ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Right: meta pills + toggle -->
          <div class="d-flex align-items-center flex-shrink-0" style="gap:.5rem">
            <div class="d-none d-md-flex flex-column text-right" style="font-size:.72rem;line-height:1.5">
              <span class="text-muted"><i class="fas fa-calendar-check mr-1 text-success"></i><?= $startFmt ?></span>
              <span class="text-muted"><i class="fas fa-calendar-times mr-1 text-danger"></i><?= $endFmt ?></span>
            </div>
            <button class="btn btn-sm btn-light border" type="button"
                    data-toggle="collapse" data-target="#proposal-detail"
                    aria-expanded="false" id="proposal-detail-toggle"
                    style="font-size:.78rem;white-space:nowrap">
              <i class="fas fa-info-circle mr-1 text-muted"></i>Detail
              <i class="fas fa-chevron-down ml-1 text-muted" id="detail-chevron"
                 style="font-size:.65rem;transition:transform .2s"></i>
            </button>
          </div>

        </div>
      </div>

      <!-- Collapsible detail body -->
      <div class="collapse" id="proposal-detail">
        <div class="px-4 py-3 border-top" style="background:#fafafa">
          <div class="row small" style="row-gap:.65rem">
            <div class="col-sm-4">
              <div class="text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">Pengusul</div>
              <div><i class="fas fa-user mr-1 text-muted"></i><?= esc($proposal['proposer_name'] ?? '-') ?></div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">Mulai</div>
              <div><i class="fas fa-calendar-check mr-1 text-success"></i><?= $startFmt ?></div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">Selesai</div>
              <div><i class="fas fa-calendar-times mr-1 text-danger"></i><?= $endFmt ?></div>
            </div>
            <div class="col-12">
              <div class="text-muted mb-1" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">Tujuan</div>
              <div class="p-2 rounded" style="background:#fff;border:1px solid #e9ecef;line-height:1.6">
                <?= nl2br(esc($proposal['objective'] ?? '-')) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
    // Rotate chevron when collapse toggles
    (function () {
      var el = document.getElementById('proposal-detail');
      if (!el) return;
      el.addEventListener('show.bs.collapse', function () {
        document.getElementById('detail-chevron').style.transform = 'rotate(180deg)';
      });
      el.addEventListener('hide.bs.collapse', function () {
        document.getElementById('detail-chevron').style.transform = 'rotate(0deg)';
      });
    })();
    </script>

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
        <div class="row" id="catalog-grid">
          <?php foreach ($availableEquipments as $eq):
            $alreadyAdded = in_array((string)$eq['id'], array_map('strval', $addedIds));
            $stockPct = $eq['stock_total'] > 0 ? round($eq['stock_available'] / $eq['stock_total'] * 100) : 0;
            $stockClass = $stockPct > 50 ? 'success' : ($stockPct > 20 ? 'warning' : 'danger');
          ?>
          <div class="col-md-6 mb-3 catalog-item" data-name="<?= strtolower(esc($eq['name'])) ?> <?= strtolower(esc($eq['lab_name'] ?? '')) ?> <?= strtolower(esc($eq['category'] ?? '')) ?>">
            <div class="card border-0 shadow-sm h-100 catalog-card <?= $alreadyAdded ? 'added' : '' ?>">

              <div class="position-relative" style="height:120px;overflow:hidden;border-radius:4px 4px 0 0;background:linear-gradient(135deg,rgba(79,195,247,.15),rgba(2,136,209,.1))">
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
                    <i class="fas fa-check mr-1"></i>Ditambahkan
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
                <form action="<?= base_url('loans/' . $proposalId . '/items/equipment') ?>" method="post" class="mt-auto add-item-form">
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
                             style="width:38px;height:32px;font-size:.82rem;font-weight:600;-moz-appearance:textfield;-webkit-appearance:none">
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
        <div class="row" id="catalog-grid">
          <?php foreach ($availableLabs as $lab):
            $alreadyAdded = in_array((string)$lab['id'], array_map('strval', $addedIds));
          ?>
          <div class="col-md-6 mb-3 catalog-item" data-name="<?= strtolower(esc($lab['name'])) ?> <?= strtolower(esc($lab['location'] ?? '')) ?> <?= strtolower(esc($lab['faculty_name'] ?? '')) ?>">
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
                    <i class="fas fa-check mr-1"></i>Ditambahkan
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
                  <?php if (! empty($lab['faculty_name'])): ?>
                    <div class="text-muted" style="font-size:.73rem"><i class="fas fa-university fa-xs mr-1"></i><?= esc($lab['faculty_name']) ?></div>
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
                <form action="<?= base_url('loans/' . $proposalId . '/items/lab') ?>" method="post" class="mt-auto">
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
  <div class="col-lg-4">

    <?php $itemCount = count($items); ?>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;overflow:hidden">
      <div class="px-3 pt-3 pb-2" style="background:<?= $accentColor ?>">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <div class="rounded d-flex align-items-center justify-content-center mr-2"
                 style="width:30px;height:30px;background:rgba(255,255,255,.2)">
              <i class="fas fa-shopping-cart fa-sm text-white"></i>
            </div>
            <div>
              <div class="text-white font-weight-bold" style="font-size:.88rem;line-height:1">Item Dipilih</div>
              <div class="text-white-50" style="font-size:.7rem"><?= $typeLabel ?> yang akan dipinjam</div>
            </div>
          </div>
          <div class="rounded-circle d-flex align-items-center justify-content-center font-weight-bold text-white"
               style="width:32px;height:32px;background:rgba(255,255,255,.25);font-size:.82rem">
            <?= $itemCount ?>
          </div>
        </div>
        <?php if ($itemCount > 0): ?>
        <div class="mt-2" style="background:rgba(255,255,255,.15);border-radius:4px;height:4px">
          <div style="background:#fff;border-radius:4px;height:4px;width:<?= min(100, $itemCount * 20) ?>%;transition:width .4s"></div>
        </div>
        <?php endif; ?>
      </div>

      <?php if (empty($items)): ?>
        <div class="card-body text-center py-4 px-3">
          <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
               style="width:52px;height:52px;background:<?= $accentBg ?>;border:2px dashed <?= $accentBorder ?>">
            <i class="fas fa-inbox" style="color:<?= $accentColor ?>;font-size:1.2rem"></i>
          </div>
          <div class="font-weight-semibold text-dark mb-1" style="font-size:.85rem">Keranjang kosong</div>
          <div class="text-muted" style="font-size:.75rem;line-height:1.5">
            Pilih <?= strtolower($typeLabel) ?> dari katalog<br>lalu klik <strong>"Tambah ke Proposal"</strong>
          </div>
        </div>
      <?php else: ?>
        <div style="max-height:320px;overflow-y:auto">
          <?php foreach ($items as $idx => $item): ?>
          <div class="px-3 py-2 d-flex align-items-center"
               style="border-bottom:1px solid #f4f5f7;<?= $idx === $itemCount - 1 ? 'border-bottom:none' : '' ?>">
            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 mr-2 font-weight-bold text-white"
                 style="width:22px;height:22px;font-size:.6rem;background:<?= $accentColor ?>">
              <?= $idx + 1 ?>
            </div>
            <div class="flex-grow-1 min-width-0 mr-2">
              <div class="text-truncate font-weight-semibold text-dark" style="font-size:.82rem">
                <?= esc($isEquipment ? ($item['equipment_name'] ?? '-') : ($item['lab_name'] ?? '-')) ?>
              </div>
              <?php if ($isEquipment && isset($item['qty'])): ?>
                <div style="font-size:.68rem;color:<?= $accentColor ?>">
                  <i class="fas fa-layer-group fa-xs mr-1"></i><?= (int)$item['qty'] ?> unit
                </div>
              <?php endif; ?>
            </div>
            <form action="<?= base_url('loans/' . $proposalId . '/items/' . (int)$item['id'] . '/delete') ?>" method="post"
                  class="js-swal-delete-form flex-shrink-0"
                  data-swal-title="Hapus item?"
                  data-swal-text="Item ini akan dihapus dari proposal."
                  data-swal-confirm="Ya, hapus"
                  data-swal-cancel="Batal">
              <?= csrf_field() ?>
              <button type="submit" title="Hapus"
                      class="border-0 bg-transparent p-0 d-flex align-items-center justify-content-center rounded-circle"
                      style="width:24px;height:24px;color:#adb5bd;cursor:pointer"
                      onmouseover="this.style.color='#dc3545';this.style.background='rgba(220,53,69,.08)'"
                      onmouseout="this.style.color='#adb5bd';this.style.background='transparent'">
                <i class="fas fa-times-circle" style="font-size:.8rem"></i>
              </button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="px-3 py-2 d-flex align-items-center justify-content-between"
             style="background:<?= $accentBg ?>;border-top:1px solid <?= $accentBorder ?>">
          <span style="font-size:.73rem;color:<?= $accentColor ?>">
            <i class="fas fa-check-circle mr-1"></i><strong><?= $itemCount ?></strong> item siap diajukan
          </span>
          <?php if ($isEquipment): ?>
            <span class="text-muted" style="font-size:.7rem">Total <?= array_sum(array_column($items, 'qty')) ?> unit</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php /* Submit */ ?>
    <?php if (activeGroupCan('lending.request.submit')): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-top:2px solid #28a745!important">
      <div class="card-body p-3">
        <div class="d-flex align-items-start mb-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white mr-2 flex-shrink-0"
               style="width:32px;height:32px;background:#28a745">3</div>
          <div>
            <div class="font-weight-bold small">Kirim ke Approval</div>
            <div class="text-muted" style="font-size:.72rem">Laboran &rarr; Kepala Lab</div>
          </div>
        </div>
        <?php if (empty($items)): ?>
          <div class="alert alert-warning border-0 py-2 px-3 small mb-2">
            <i class="fas fa-exclamation-triangle mr-1"></i>Tambahkan minimal 1 item sebelum mengirim.
          </div>
        <?php endif; ?>
        <form action="<?= base_url('loans/' . $proposalId . '/submit') ?>" method="post"
              class="js-swal-confirm-form"
              data-swal-title="Kirim Proposal?"
              data-swal-text="Proposal akan dikirim untuk proses approval. Pastikan item sudah lengkap."
              data-swal-confirm="Ya, Kirim"
              data-swal-cancel="Batal"
              data-swal-icon="question">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-success btn-block font-weight-semibold" <?= empty($items) ? 'disabled' : '' ?>>
            <i class="fas fa-paper-plane mr-2"></i>Kirim Approval
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Cancel */ ?>
    <?php if (activeGroupCan('lending.request.cancel') && ((int)($proposal['proposer_id'] ?? 0) === (int)auth()->id() || activeGroupCan('lending.request.manage-all'))): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3">
        <a class="btn btn-outline-danger btn-sm btn-block" data-toggle="collapse" href="#cancel-form">
          <i class="fas fa-ban mr-1"></i> Batalkan Proposal
        </a>
        <div class="collapse mt-2" id="cancel-form">
          <form action="<?= base_url('loans/' . $proposalId . '/cancel') ?>" method="post">
            <?= csrf_field() ?>
            <textarea name="cancel_reason" class="form-control form-control-sm mb-2" rows="2"
                      placeholder="Alasan pembatalan..." required></textarea>
            <button type="submit" class="btn btn-danger btn-sm btn-block">Konfirmasi Batalkan</button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

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

<style>
.catalog-card { transition:box-shadow .15s,transform .15s; }
.catalog-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.1)!important; transform:translateY(-2px); }
.catalog-card.added { opacity:.85; }
</style>

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
                confirmButtonColor: '#28a745',
                reverseButtons: true,
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
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
