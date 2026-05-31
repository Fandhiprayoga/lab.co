<?php
$canManageAll = $canManageAll ?? false;
$statusLabels = $statusLabels ?? [];
?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ─────────────────────────────────── */
  .req-filter-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.35);
    opacity: 0; visibility: hidden;
    transition: opacity .2s ease;
    z-index: 2147483000;
  }
  .req-filter-overlay.is-open { opacity: 1; visibility: visible; }

  .req-filter-drawer {
    position: fixed; top: 0; right: 0;
    width: min(360px, 92vw); height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0,0,0,.18);
    transform: translateX(100%);
    transition: transform .25s ease;
    z-index: 2147483010;
    display: flex; flex-direction: column;
  }
  .req-filter-drawer.is-open { transform: translateX(0); }

  .req-filter-drawer__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }
  .req-filter-drawer__body  { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
  .req-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex; justify-content: space-between; gap: .5rem;
  }

  /* ── Active filter chips ─────────────────────────── */
  .req-active-filters { display: none; align-items: center; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
  .req-active-filters.is-visible { display: flex; }

  .req-filter-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .65rem; border-radius: 999px;
    background: #f1f3f5; color: #343a40; font-size: .8rem; line-height: 1;
  }
  .req-filter-chip__remove {
    border: 0; background: transparent; color: inherit;
    padding: 0; line-height: 1; cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<!-- ── Main card ──────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Daftar Permintaan BHP</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-req-filter" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <?php if (activeGroupCan('bhp.request.create')): ?>
      <a href="<?= site_url('consumables/requests/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Buat Permintaan
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="req-active-filters" class="req-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="req-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-req" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Kode</th>
            <th>Pemohon</th>
            <th>Lab</th>
            <th>Tujuan</th>
            <th>Jadwal</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th width="80"></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ───────────────────────────────── -->
<div id="req-filter-overlay" class="req-filter-overlay"></div>

<!-- ── Side Drawer ──────────────────────────────────── -->
<aside id="req-filter-drawer" class="req-filter-drawer" aria-hidden="true">
  <div class="req-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter Permintaan</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-req-filter" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="req-filter-drawer__body">

    <div class="form-group">
      <label for="filter-req-status" class="font-weight-bold small">Status</label>
      <select id="filter-req-status" class="form-control">
        <option value="">Semua Status</option>
        <?php foreach ($statusLabels as $val => $label): ?>
        <option value="<?= $val ?>"><?= esc($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="font-weight-bold small">Rentang Tanggal Pengajuan</label>
      <div class="input-group mb-2">
        <div class="input-group-prepend">
          <span class="input-group-text" style="font-size:12px;">Dari</span>
        </div>
        <input type="date" id="filter-req-from" class="form-control">
      </div>
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text" style="font-size:12px;">S/d</span>
        </div>
        <input type="date" id="filter-req-until" class="form-control">
      </div>
    </div>

  </div>

  <div class="req-filter-drawer__footer">
    <button type="button" id="reset-req-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-req-filter" class="btn btn-primary flex-fill">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  var drawer      = $('#req-filter-drawer');
  var overlay     = $('#req-filter-overlay');
  var activeWrap  = $('#req-active-filters');
  var activeChips = $('#req-active-filter-chips');

  if (drawer.parent()[0] !== document.body)  drawer.appendTo('body');
  if (overlay.parent()[0] !== document.body) overlay.appendTo('body');

  /* ── Drawer state ─────────────────────── */
  function setDrawerState(open) {
    drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
    overlay.toggleClass('is-open', open);
    $('body').toggleClass('overflow-hidden', open);
  }

  /* ── Label maps ───────────────────────── */
  var statusLabels = {};
  <?php foreach ($statusLabels as $val => $label): ?>
  statusLabels['<?= $val ?>'] = '<?= addslashes(esc($label)) ?>';
  <?php endforeach; ?>

  /* ── Filter chips ─────────────────────── */
  function getFilterDefs() {
    return [
      { key: 'status', label: 'Status', el: '#filter-req-status', map: statusLabels },
      { key: 'from',   label: 'Dari',   el: '#filter-req-from',   map: null },
      { key: 'until',  label: 'S/d',    el: '#filter-req-until',  map: null },
    ];
  }

  function renderChips() {
    var active = getFilterDefs().filter(function (f) { return $(f.el).val() !== ''; });
    activeChips.empty();
    if (!active.length) { activeWrap.removeClass('is-visible'); return; }

    active.forEach(function (f) {
      var raw     = $(f.el).val();
      var display = f.map ? (f.map[raw] || raw) : raw;
      var chip    = $('<span class="req-filter-chip"></span>');
      chip.append(document.createTextNode(f.label + ': ' + display));
      var rm = $('<button type="button" class="req-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
      rm.data('fk', f.key);
      chip.append(rm);
      activeChips.append(chip);
    });
    activeWrap.addClass('is-visible');
  }

  /* ── Column defs ──────────────────────── */
  var colDefs = [
    { targets: [0, 4, 6, 8], orderable: false },
    { targets: [0, 5, 6, 7, 8], searchable: false },
  ];
  <?php if (! $canManageAll): ?>
  colDefs.push({ targets: [2], visible: false });
  <?php endif; ?>

  /* ── DataTable ────────────────────────── */
  var table = $('#table-req').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[7, 'desc']],
    columnDefs : colDefs,
    ajax: {
      url : '<?= site_url('consumables/requests/datatable') ?>',
      data: function (d) {
        d.filter_status = $('#filter-req-status').val();
        d.filter_from   = $('#filter-req-from').val();
        d.filter_until  = $('#filter-req-until').val();
      }
    },
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
      info        : 'Menampilkan _START_ \u2013 _END_ dari _TOTAL_ data',
      infoEmpty   : 'Tidak ada data',
      emptyTable  : 'Belum ada permintaan.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ──────────────────────────── */
  $('#open-req-filter').on('click', function () { setDrawerState(true); });
  $('#close-req-filter, #req-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-req-filter').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-req-filter').on('click', function () {
    $('#filter-req-status').val('');
    $('#filter-req-from, #filter-req-until').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = {
    status: '#filter-req-status',
    from:   '#filter-req-from',
    until:  '#filter-req-until',
  };
  activeChips.on('click', '.req-filter-chip__remove', function () {
    var key = $(this).data('fk');
    if (keyMap[key]) $(keyMap[key]).val('');
    table.ajax.reload();
    renderChips();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') setDrawerState(false);
  });
});
</script>
<?= $this->endSection() ?>
