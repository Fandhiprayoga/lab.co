<?php
$labs       = $labs       ?? [];
$categories = $categories ?? [];
?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ─────────────────────────────────── */
  .bhp-filter-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.35);
    opacity: 0; visibility: hidden;
    transition: opacity .2s ease;
    z-index: 2147483000;
  }
  .bhp-filter-overlay.is-open { opacity: 1; visibility: visible; }

  .bhp-filter-drawer {
    position: fixed; top: 0; right: 0;
    width: min(360px, 92vw); height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0,0,0,.18);
    transform: translateX(100%);
    transition: transform .25s ease;
    z-index: 2147483010;
    display: flex; flex-direction: column;
  }
  .bhp-filter-drawer.is-open { transform: translateX(0); }

  .bhp-filter-drawer__header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }
  .bhp-filter-drawer__body {
    flex: 1; overflow-y: auto;
    padding: 1rem 1.25rem;
  }
  .bhp-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex; justify-content: space-between; gap: .5rem;
  }

  /* ── Active filter chips ─────────────────────────── */
  .bhp-active-filters { display: none; align-items: center; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
  .bhp-active-filters.is-visible { display: flex; }

  .bhp-filter-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .65rem; border-radius: 999px;
    background: #f1f3f5; color: #343a40; font-size: .8rem; line-height: 1;
  }
  .bhp-filter-chip__remove {
    border: 0; background: transparent; color: inherit;
    padding: 0; line-height: 1; cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<!-- ── Main card ──────────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Katalog Bahan Habis Pakai</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-bhp-filter" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <?php if (activeGroupCan('bhp.request.create')): ?>
      <a href="<?= site_url('consumables/requests/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Buat Permintaan
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="bhp-active-filters" class="bhp-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="bhp-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-bhp" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th>Lab</th>
            <th>Stok Tersedia</th>
            <th>Min. Stok</th>
            <th>Lokasi</th>
            <th>Kedaluwarsa</th>
            <th>Status</th>
            <?php if (activeGroupCan('bhp.stock.adjust')): ?><th width="60"></th><?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ───────────────────────────────── -->
<div id="bhp-filter-overlay" class="bhp-filter-overlay"></div>

<!-- ── Side Drawer ──────────────────────────────────── -->
<aside id="bhp-filter-drawer" class="bhp-filter-drawer" aria-hidden="true">
  <div class="bhp-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter Bahan</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-bhp-filter" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="bhp-filter-drawer__body">

    <div class="form-group">
      <label for="filter-bhp-lab" class="font-weight-bold small">Lab</label>
      <select id="filter-bhp-lab" class="form-control">
        <option value="">Semua Lab</option>
        <?php foreach ($labs as $lab): ?>
        <option value="<?= $lab['id'] ?>"><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-bhp-category" class="font-weight-bold small">Kategori</label>
      <select id="filter-bhp-category" class="form-control">
        <option value="">Semua Kategori</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group mb-0">
      <label for="filter-bhp-status" class="font-weight-bold small">Kondisi Stok</label>
      <select id="filter-bhp-status" class="form-control">
        <option value="">Semua Kondisi</option>
        <option value="ok">Aman</option>
        <option value="low_stock">Stok Rendah</option>
        <option value="expired">Kedaluwarsa</option>
      </select>
    </div>

  </div>

  <div class="bhp-filter-drawer__footer">
    <button type="button" id="reset-bhp-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-bhp-filter" class="btn btn-primary flex-fill">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  var drawer      = $('#bhp-filter-drawer');
  var overlay     = $('#bhp-filter-overlay');
  var activeWrap  = $('#bhp-active-filters');
  var activeChips = $('#bhp-active-filter-chips');

  if (drawer.parent()[0] !== document.body)  drawer.appendTo('body');
  if (overlay.parent()[0] !== document.body) overlay.appendTo('body');

  /* ── Drawer state ─────────────────────── */
  function setDrawerState(open) {
    drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
    overlay.toggleClass('is-open', open);
    $('body').toggleClass('overflow-hidden', open);
  }

  /* ── Label maps ───────────────────────── */
  var labLabels      = {};
  var categoryLabels = {};
  <?php foreach ($labs as $lab): ?>
  labLabels[<?= $lab['id'] ?>] = '<?= addslashes(esc($lab['name'])) ?>';
  <?php endforeach; ?>
  <?php foreach ($categories as $cat): ?>
  categoryLabels[<?= $cat['id'] ?>] = '<?= addslashes(esc($cat['name'])) ?>';
  <?php endforeach; ?>
  var statusLabels = { ok: 'Aman', low_stock: 'Stok Rendah', expired: 'Kedaluwarsa' };

  /* ── Filter chips ─────────────────────── */
  function renderChips() {
    var filters = [
      { key: 'lab',      label: 'Lab',      el: '#filter-bhp-lab',      map: labLabels },
      { key: 'category', label: 'Kategori', el: '#filter-bhp-category', map: categoryLabels },
      { key: 'status',   label: 'Kondisi',  el: '#filter-bhp-status',   map: statusLabels },
    ].filter(function (f) { return $(f.el).val() !== ''; });

    activeChips.empty();
    if (!filters.length) { activeWrap.removeClass('is-visible'); return; }

    filters.forEach(function (f) {
      var raw     = $(f.el).val();
      var display = f.map ? (f.map[raw] || raw) : raw;
      var chip    = $('<span class="bhp-filter-chip"></span>');
      chip.append(document.createTextNode(f.label + ': ' + display));
      var rm = $('<button type="button" class="bhp-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
      rm.data('fk', f.key);
      chip.append(rm);
      activeChips.append(chip);
    });
    activeWrap.addClass('is-visible');
  }

  /* ── DataTable ────────────────────────── */
  var colCount = $('#table-bhp thead th').length;
  var table = $('#table-bhp').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[1, 'asc']],
    ajax: {
      url : '<?= site_url('consumables/datatable') ?>',
      data: function (d) {
        d.filter_lab      = $('#filter-bhp-lab').val();
        d.filter_category = $('#filter-bhp-category').val();
        d.filter_status   = $('#filter-bhp-status').val();
      }
    },
    columnDefs: [
      { targets: [0, 2, 3, 5, 6, 7, 8, colCount - 1], orderable: false },
      { targets: [0, 5, 6, 8, colCount - 1],            searchable: false },
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
      emptyTable  : 'Belum ada data bahan.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ─────────────────────────────── */
  $('#open-bhp-filter').on('click', function () { setDrawerState(true); });
  $('#close-bhp-filter, #bhp-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-bhp-filter').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-bhp-filter').on('click', function () {
    $('#filter-bhp-lab, #filter-bhp-category, #filter-bhp-status').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = {
    lab:      '#filter-bhp-lab',
    category: '#filter-bhp-category',
    status:   '#filter-bhp-status',
  };
  activeChips.on('click', '.bhp-filter-chip__remove', function () {
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

