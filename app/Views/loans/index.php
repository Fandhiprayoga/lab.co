<?php
$proposals = $proposals ?? [];

$statusMap = [
    'draft'      => ['label' => 'Draft',            'badge' => 'badge-secondary', 'icon' => 'fa-file-alt'],
    'waiting_l1' => ['label' => 'Menunggu Laboran',  'badge' => 'badge-warning',   'icon' => 'fa-clock'],
    'waiting_l2' => ['label' => 'Menunggu Ka.Lab',   'badge' => 'badge-info',      'icon' => 'fa-user-check'],
    'approved'   => ['label' => 'Disetujui',          'badge' => 'badge-success',   'icon' => 'fa-check-circle'],
    'rejected'   => ['label' => 'Ditolak',            'badge' => 'badge-danger',    'icon' => 'fa-times-circle'],
    'canceled'   => ['label' => 'Dibatalkan',         'badge' => 'badge-dark',      'icon' => 'fa-ban'],
];

// counts for summary cards
$counts = [
    'all'        => count($proposals),
    'active'     => 0,  // draft + waiting
    'approved'   => 0,
    'closed'     => 0,  // rejected + canceled
];
foreach ($proposals as $p) {
    if (in_array($p['status'], ['draft', 'waiting_l1', 'waiting_l2'])) $counts['active']++;
    elseif ($p['status'] === 'approved') $counts['approved']++;
    elseif (in_array($p['status'], ['rejected', 'canceled'])) $counts['closed']++;
}
?>

<!-- Summary cards -->
<div class="row mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Proposal</h4></div>
        <div class="card-body"><?= $counts['all'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-clock"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Sedang Berjalan</h4></div>
        <div class="card-body"><?= $counts['active'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Disetujui</h4></div>
        <div class="card-body"><?= $counts['approved'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Ditolak / Batal</h4></div>
        <div class="card-body"><?= $counts['closed'] ?></div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     FILTER DRAWER OVERLAY + PANEL
═══════════════════════════════════════════════ -->
<div id="filter-overlay"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:10000;transition:opacity .25s"
     onclick="closeFilterDrawer()"></div>

<div id="filter-drawer"
     style="position:fixed;top:0;right:0;height:100%;width:340px;max-width:95vw;
            background:#fff;z-index:10001;box-shadow:-4px 0 24px rgba(0,0,0,.18);
            transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1);
            display:flex;flex-direction:column">

  <!-- Drawer header -->
  <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom flex-shrink-0">
    <h6 class="mb-0 font-weight-bold"><i class="fas fa-filter mr-2 text-primary"></i>Filter Proposal</h6>
    <button type="button" class="btn btn-sm btn-light" onclick="closeFilterDrawer()">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <!-- Drawer scrollable body -->
  <div class="flex-grow-1 overflow-auto px-4 py-3">

    <!-- Tipe -->
    <div class="mb-4">
      <div class="text-muted font-weight-bold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
        Tipe Peminjaman
      </div>
      <div class="d-flex flex-column" style="gap:.5rem">
        <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
          <input type="checkbox" class="filter-type" value="equipment" style="width:16px;height:16px">
          <span class="badge badge-light border px-2 py-1">
            <i class="fas fa-tools text-warning mr-1"></i>Alat
          </span>
        </label>
        <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
          <input type="checkbox" class="filter-type" value="lab" style="width:16px;height:16px">
          <span class="badge badge-light border px-2 py-1">
            <i class="fas fa-flask text-info mr-1"></i>Laboratorium
          </span>
        </label>
      </div>
    </div>

    <!-- Status -->
    <div class="mb-4">
      <div class="text-muted font-weight-bold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
        Status
      </div>
      <div class="d-flex flex-column" style="gap:.5rem">
        <?php foreach ($statusMap as $sKey => $sVal): ?>
        <label class="d-flex align-items-center mb-0" style="cursor:pointer;gap:.6rem">
          <input type="checkbox" class="filter-status" value="<?= $sKey ?>" style="width:16px;height:16px">
          <span class="badge <?= $sVal['badge'] ?> px-2 py-1">
            <i class="fas <?= $sVal['icon'] ?> mr-1"></i><?= $sVal['label'] ?>
          </span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tanggal Pengajuan -->
    <div class="mb-4">
      <div class="text-muted font-weight-bold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
        Tanggal Pengajuan
      </div>
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

  <!-- Drawer footer -->
  <div class="px-4 py-3 border-top flex-shrink-0 d-flex" style="gap:.5rem">
    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="resetFilters()">
      <i class="fas fa-undo mr-1"></i>Reset
    </button>
    <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="applyFilters()">
      <i class="fas fa-check mr-1"></i>Terapkan
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     TABLE CARD
═══════════════════════════════════════════════ -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Proposal</h4>
        <div class="card-header-action d-flex align-items-center" style="gap:.5rem">
          <button type="button" class="btn btn-outline-secondary" id="btn-filter-open" onclick="openFilterDrawer()">
            <i class="fas fa-filter mr-1"></i>Filter
            <span id="filter-badge" class="badge badge-primary ml-1" style="display:none">0</span>
          </button>
          <?php if (activeGroupCan('lending.request.create')): ?>
          <a href="<?= base_url('loans/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Proposal
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Active filter chips -->
      <div id="filter-chips-bar" class="px-4 pb-3 pt-0" style="display:none">
        <div class="d-flex align-items-center flex-wrap" style="gap:.4rem">
          <span class="text-muted small mr-1">Filter aktif:</span>
          <div id="filter-chips" class="d-flex flex-wrap" style="gap:.4rem"></div>
          <button type="button" class="btn btn-link btn-sm text-danger p-0 ml-1" onclick="resetFilters()" style="font-size:.8rem">
            Hapus semua
          </button>
        </div>
      </div>

      <div class="card-body">
        <?php if (empty($proposals)): ?>
        <div class="empty-state" data-height="400">
          <div class="empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
          <h2>Belum Ada Proposal</h2>
          <p class="lead">Belum ada proposal peminjaman yang tersedia.</p>
          <?php if (activeGroupCan('lending.request.create')): ?>
          <a href="<?= base_url('loans/create') ?>" class="btn btn-primary mt-4">
            <i class="fas fa-plus"></i> Buat Proposal Pertama
          </a>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover" id="table-proposals" width="100%">
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
                $si          = $statusMap[$p['status']] ?? ['label' => $p['status'], 'badge' => 'badge-light', 'icon' => 'fa-question'];
                $isEquip     = ($p['loan_type'] ?? '') === 'equipment';
                $startFmt    = $p['start_at']    ? date('d M Y', strtotime($p['start_at']))    : '-';
                $endFmt      = $p['end_at']      ? date('d M Y', strtotime($p['end_at']))      : '-';
                $submittedRaw = substr($p['submitted_at'] ?? '', 0, 10); // YYYY-MM-DD
              ?>
              <tr data-status="<?= esc($p['status']) ?>"
                  data-type="<?= $isEquip ? 'equipment' : 'lab' ?>"
                  data-submitted="<?= esc($submittedRaw) ?>">
                <td>
                  <a href="<?= base_url('loans/' . $p['id']) ?>" class="font-weight-bold text-primary">
                    <?= esc($p['proposal_code']) ?>
                  </a>
                </td>
                <td><?= esc($p['proposer_name'] ?? '-') ?></td>
                <td><?= esc($p['title']) ?></td>
                <td>
                  <?php if ($isEquip): ?>
                  <span class="badge badge-light border"><i class="fas fa-tools text-warning mr-1"></i> Alat</span>
                  <?php else: ?>
                  <span class="badge badge-light border"><i class="fas fa-flask text-info mr-1"></i> Lab</span>
                  <?php endif; ?>
                </td>
                <td data-order="<?= esc($p['start_at'] ?? '') ?>"><?= $startFmt ?></td>
                <td data-order="<?= esc($p['end_at'] ?? '') ?>"><?= $endFmt ?></td>
                <td class="text-center">
                  <span class="badge badge-pill badge-light border"><?= (int) $p['total_items'] ?></span>
                </td>
                <td>
                  <span class="badge <?= $si['badge'] ?>">
                    <i class="fas <?= $si['icon'] ?> mr-1"></i><?= $si['label'] ?>
                  </span>
                </td>
                <td class="text-center">
                  <a href="<?= base_url('loans/' . $p['id']) ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                  </a>
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

<script>
document.addEventListener('DOMContentLoaded', function () {

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
    }

    // ── Status label map for chips ───────────────────────────
    var statusLabels = {
        draft:      'Draft',
        waiting_l1: 'Menunggu Laboran',
        waiting_l2: 'Menunggu Ka.Lab',
        approved:   'Disetujui',
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
                if (table) table.draw();
            }));
        });

        activeStatuses.forEach(function (v) {
            count++;
            chips.appendChild(makeChip(statusLabels[v] || v, function () {
                activeStatuses = activeStatuses.filter(function (x) { return x !== v; });
                renderChips();
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
