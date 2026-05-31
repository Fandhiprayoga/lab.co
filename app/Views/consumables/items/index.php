<?php
$labs       = $labs ?? [];
$categories = $categories ?? [];
?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ─────────────────────────────────── */
  .item-filter-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.35);
    opacity: 0; visibility: hidden;
    transition: opacity .2s ease;
    z-index: 99999 !important;
  }
  .item-filter-overlay.is-open {
    opacity: 1; visibility: visible;
  }
  .item-filter-drawer {
    position: fixed; top: 0; right: 0;
    width: 320px; max-width: 90vw;
    height: 100vh;
    background: #fff;
    box-shadow: -2px 0 8px rgba(0,0,0,.15);
    transform: translateX(100%);
    transition: transform .2s ease;
    z-index: 100000 !important;
    display: flex; flex-direction: column;
  }
  .item-filter-drawer.is-open {
    transform: translateX(0);
  }
  .item-filter-drawer__header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .item-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
  }
  .item-filter-drawer__footer {
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: .5rem;
  }
  .item-active-filters {
    margin-bottom: 1rem;
    display: none;
  }
  .item-active-filters.has-filters {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .5rem;
  }
  .item-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .25rem .5rem;
    font-size: .875rem;
    background: #e9ecef;
    border-radius: .25rem;
  }
  .item-filter-chip__remove {
    cursor: pointer;
    margin-left: .25rem;
    opacity: .7;
  }
  .item-filter-chip__remove:hover {
    opacity: 1;
  }
</style>
<?= $this->endSection() ?>

<!-- ── Main card ──────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Katalog Bahan Habis Pakai</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-item-filter" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <a href="<?= site_url('admin/consumables/items/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Tambah Bahan
      </a>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="item-active-filters" class="item-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="item-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-items" class="table table-striped table-bordered">
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
            <th width="120">Status</th>
            <th width="80"></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ───────────────────────────────── -->
<div id="item-filter-overlay" class="item-filter-overlay"></div>

<!-- ── Side Drawer ──────────────────────────────────── -->
<aside id="item-filter-drawer" class="item-filter-drawer" aria-hidden="true">
  <div class="item-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter Bahan</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-item-filter" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="item-filter-drawer__body">
    <div class="form-group">
      <label for="filter-item-lab" class="font-weight-bold">Laboratorium</label>
      <select id="filter-item-lab" class="form-control">
        <option value="">— Semua Lab —</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= $lab['id'] ?>"><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-item-category" class="font-weight-bold">Kategori</label>
      <select id="filter-item-category" class="form-control">
        <option value="">— Semua Kategori —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-item-status" class="font-weight-bold">Status</label>
      <select id="filter-item-status" class="form-control">
        <option value="">— Semua Status —</option>
        <option value="low_stock">Stok Rendah</option>
        <option value="expired">Kedaluwarsa</option>
        <option value="ok">Normal/Tersedia</option>
      </select>
    </div>
  </div>

  <div class="item-filter-drawer__footer">
    <button type="button" id="reset-item-filter" class="btn btn-light flex-fill">
      <i class="fas fa-undo mr-1"></i> Reset
    </button>
    <button type="button" id="apply-item-filter" class="btn btn-primary flex-fill">
      <i class="fas fa-check mr-1"></i> Terapkan
    </button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  /* ── Move drawer to body level to avoid z-index issues ──────────────── */
  var drawer  = document.getElementById('item-filter-drawer');
  var overlay = document.getElementById('item-filter-overlay');
  if (drawer && overlay) {
    document.body.appendChild(overlay);
    document.body.appendChild(drawer);
  }

  /* ── Filter Drawer Toggle ──────────────── */
  drawer      = $('#item-filter-drawer');
  overlay     = $('#item-filter-overlay');
  var activeChips = $('#item-active-filter-chips');

  function setDrawerState(open) {
    if (open) {
      drawer.addClass('is-open').attr('aria-hidden', 'false');
      overlay.addClass('is-open');
    } else {
      drawer.removeClass('is-open').attr('aria-hidden', 'true');
      overlay.removeClass('is-open');
    }
  }

  function renderChips() {
    var chips = [];
    var lab = $('#filter-item-lab option:selected').text();
    var category = $('#filter-item-category option:selected').text();
    var status = $('#filter-item-status option:selected').text();

    if ($('#filter-item-lab').val()) {
      chips.push('<span class="item-filter-chip">' + lab + '<i class="fas fa-times item-filter-chip__remove" data-fk="lab"></i></span>');
    }
    if ($('#filter-item-category').val()) {
      chips.push('<span class="item-filter-chip">' + category + '<i class="fas fa-times item-filter-chip__remove" data-fk="category"></i></span>');
    }
    if ($('#filter-item-status').val()) {
      chips.push('<span class="item-filter-chip">' + status + '<i class="fas fa-times item-filter-chip__remove" data-fk="status"></i></span>');
    }

    if (chips.length) {
      activeChips.html(chips.join(''));
      $('#item-active-filters').addClass('has-filters');
    } else {
      $('#item-active-filters').removeClass('has-filters');
    }
  }

  /* ── DataTable ────────────────────────── */
  var table = $('#table-items').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[1, 'asc']],
    ajax: {
      url : '<?= site_url('consumables/datatable') ?>',
      data: function (d) {
        d.filter_lab      = $('#filter-item-lab').val();
        d.filter_category = $('#filter-item-category').val();
        d.filter_status   = $('#filter-item-status').val();
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
      emptyTable  : 'Belum ada bahan.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ──────────────────────────── */
  $('#open-item-filter').on('click', function () { setDrawerState(true); });
  $('#close-item-filter, #item-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-item-filter').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-item-filter').on('click', function () {
    $('#filter-item-lab').val('');
    $('#filter-item-category').val('');
    $('#filter-item-status').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = {
    lab:      '#filter-item-lab',
    category: '#filter-item-category',
    status:   '#filter-item-status',
  };
  activeChips.on('click', '.item-filter-chip__remove', function () {
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
