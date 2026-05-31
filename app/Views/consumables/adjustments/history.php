<?php
$labs       = $labs       ?? [];
$typeLabels = $typeLabels ?? [];
?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ─────────────────────────────────── */
  .adj-filter-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.35);
    opacity: 0; visibility: hidden;
    transition: opacity .2s ease;
    z-index: 2147483000;
  }
  .adj-filter-overlay.is-open { opacity: 1; visibility: visible; }

  .adj-filter-drawer {
    position: fixed; top: 0; right: 0;
    width: min(360px, 92vw); height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0,0,0,.18);
    transform: translateX(100%);
    transition: transform .25s ease;
    z-index: 2147483010;
    display: flex; flex-direction: column;
  }
  .adj-filter-drawer.is-open { transform: translateX(0); }

  .adj-filter-drawer__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }
  .adj-filter-drawer__body  { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
  .adj-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex; justify-content: space-between; gap: .5rem;
  }

  /* ── Active filter chips ─────────────────────────── */
  .adj-active-filters { display: none; align-items: center; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
  .adj-active-filters.is-visible { display: flex; }

  .adj-filter-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .65rem; border-radius: 999px;
    background: #f1f3f5; color: #343a40; font-size: .8rem; line-height: 1;
  }
  .adj-filter-chip__remove {
    border: 0; background: transparent; color: inherit;
    padding: 0; line-height: 1; cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<!-- ── Main card ──────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Riwayat Penyesuaian Stok BHP</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-adj-filter" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <div class="dropdown">
        <button class="btn btn-success dropdown-toggle" type="button" id="adj-export-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-file-excel mr-1"></i> Export Excel
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="adj-export-dropdown">
          <a class="dropdown-item" id="adj-export-all" href="#">
            <i class="fas fa-table mr-2"></i> Semua Data
          </a>
          <a class="dropdown-item" id="adj-export-filtered" href="#">
            <i class="fas fa-filter mr-2"></i> Sesuai Filter Aktif
          </a>
        </div>
      </div>
      <a href="<?= site_url('consumables') ?>" class="btn btn-light">
        <i class="fas fa-flask mr-1"></i> Katalog Bahan
      </a>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="adj-active-filters" class="adj-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="adj-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-adj" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Tanggal</th>
            <th>Nama Bahan</th>
            <th>Lab</th>
            <th>Tipe</th>
            <th>Qty</th>
            <th>Alasan</th>
            <th>Oleh</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ───────────────────────────────── -->
<div id="adj-filter-overlay" class="adj-filter-overlay"></div>

<!-- ── Side Drawer ──────────────────────────────────── -->
<aside id="adj-filter-drawer" class="adj-filter-drawer" aria-hidden="true">
  <div class="adj-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter Riwayat</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-adj-filter" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="adj-filter-drawer__body">

    <div class="form-group">
      <label for="filter-adj-lab" class="font-weight-bold small">Lab</label>
      <select id="filter-adj-lab" class="form-control">
        <option value="">Semua Lab</option>
        <?php foreach ($labs as $lab): ?>
        <option value="<?= $lab['id'] ?>"><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-adj-type" class="font-weight-bold small">Tipe Penyesuaian</label>
      <select id="filter-adj-type" class="form-control">
        <option value="">Semua Tipe</option>
        <?php foreach ($typeLabels as $val => $label): ?>
        <option value="<?= $val ?>"><?= esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="font-weight-bold small">Rentang Tanggal</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <span class="input-group-text" style="font-size:12px;">Dari</span>
        </div>
        <input type="date" id="filter-adj-from" class="form-control">
      </div>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text" style="font-size:12px;">S/d</span>
        </div>
        <input type="date" id="filter-adj-until" class="form-control">
      </div>
    </div>

  </div>

  <div class="adj-filter-drawer__footer">
    <button type="button" id="reset-adj-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-adj-filter" class="btn btn-primary flex-fill">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  var drawer      = $('#adj-filter-drawer');
  var overlay     = $('#adj-filter-overlay');
  var activeWrap  = $('#adj-active-filters');
  var activeChips = $('#adj-active-filter-chips');

  if (drawer.parent()[0] !== document.body)  drawer.appendTo('body');
  if (overlay.parent()[0] !== document.body) overlay.appendTo('body');

  /* ── Drawer state ─────────────────────── */
  function setDrawerState(open) {
    drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
    overlay.toggleClass('is-open', open);
    $('body').toggleClass('overflow-hidden', open);
  }

  /* ── Label maps ───────────────────────── */
  var labLabels = {};
  <?php foreach ($labs as $lab): ?>
  labLabels[<?= $lab['id'] ?>] = '<?= addslashes(esc($lab['name'])) ?>';
  <?php endforeach; ?>

  var typeLabels = {};
  <?php foreach ($typeLabels as $val => $label): ?>
  typeLabels['<?= $val ?>'] = '<?= addslashes(esc($label)) ?>';
  <?php endforeach; ?>

  /* ── Filter chips ─────────────────────── */
  function getFilterDefs() {
    return [
      { key: 'lab',   label: 'Lab',   el: '#filter-adj-lab',   map: labLabels },
      { key: 'type',  label: 'Tipe',  el: '#filter-adj-type',  map: typeLabels },
      { key: 'from',  label: 'Dari',  el: '#filter-adj-from',  map: null },
      { key: 'until', label: 'S/d',   el: '#filter-adj-until', map: null },
    ];
  }

  function renderChips() {
    var active = getFilterDefs().filter(function (f) { return $(f.el).val() !== ''; });
    activeChips.empty();
    if (!active.length) { activeWrap.removeClass('is-visible'); return; }

    active.forEach(function (f) {
      var raw     = $(f.el).val();
      var display = f.map ? (f.map[raw] || raw) : raw;
      var chip    = $('<span class="adj-filter-chip"></span>');
      chip.append(document.createTextNode(f.label + ': ' + display));
      var rm = $('<button type="button" class="adj-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
      rm.data('fk', f.key);
      chip.append(rm);
      activeChips.append(chip);
    });
    activeWrap.addClass('is-visible');
  }

  /* ── DataTable ────────────────────────── */
  var table = $('#table-adj').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[1, 'desc']],
    ajax: {
      url : '<?= site_url('consumables/adjustments/datatable') ?>',
      data: function (d) {
        d.filter_lab   = $('#filter-adj-lab').val();
        d.filter_type  = $('#filter-adj-type').val();
        d.filter_from  = $('#filter-adj-from').val();
        d.filter_until = $('#filter-adj-until').val();
      }
    },
    columnDefs: [
      { targets: [0, 3, 4, 6, 7], orderable: false },
      { targets: [0, 4, 6, 7],    searchable: false },
    ],
    drawCallback: function () {
      var api   = this.api();
      var start = api.page.info().start;
      api.column(0).nodes().each(function (td, i) {
        $(td).text(start + i + 1);
      });
    },
    language: {
      search      : 'Cari:',
      lengthMenu  : 'Tampilkan _MENU_ data',
      info        : 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
      infoEmpty   : 'Tidak ada data',
      emptyTable  : 'Belum ada riwayat penyesuaian.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ──────────────────────────── */
  $('#open-adj-filter').on('click', function () { setDrawerState(true); });
  $('#close-adj-filter, #adj-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-adj-filter').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-adj-filter').on('click', function () {
    $('#filter-adj-lab, #filter-adj-type').val('');
    $('#filter-adj-from, #filter-adj-until').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = {
    lab:   '#filter-adj-lab',
    type:  '#filter-adj-type',
    from:  '#filter-adj-from',
    until: '#filter-adj-until',
  };
  activeChips.on('click', '.adj-filter-chip__remove', function () {
    var key = $(this).data('fk');
    if (keyMap[key]) $(keyMap[key]).val('');
    table.ajax.reload();
    renderChips();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') setDrawerState(false);
  });

  /* ── Export ───────────────────────────── */
  var exportBaseUrl = '<?= site_url('consumables/adjustments/export') ?>';

  function buildExportUrl(withFilters) {
    var params = {};
    if (withFilters) {
      var lab   = $('#filter-adj-lab').val();
      var type  = $('#filter-adj-type').val();
      var from  = $('#filter-adj-from').val();
      var until = $('#filter-adj-until').val();
      if (lab)   params.filter_lab   = lab;
      if (type)  params.filter_type  = type;
      if (from)  params.filter_from  = from;
      if (until) params.filter_until = until;
    }
    var qs = $.param(params);
    return exportBaseUrl + (qs ? '?' + qs : '');
  }

  $('#adj-export-all').on('click', function (e) {
    e.preventDefault();
    window.location.href = buildExportUrl(false);
  });

  $('#adj-export-filtered').on('click', function (e) {
    e.preventDefault();
    window.location.href = buildExportUrl(true);
  });
});
</script>
<?= $this->endSection() ?>
