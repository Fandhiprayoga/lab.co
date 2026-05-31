<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ─────────────────────────────────── */
  .visit-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }
  .visit-filter-overlay.is-open {
    opacity: 1;
    visibility: visible;
  }

  .visit-filter-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: min(380px, 92vw);
    height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0,0,0,.18);
    transform: translateX(100%);
    transition: transform .25s ease;
    z-index: 2147483010;
    display: flex;
    flex-direction: column;
  }
  .visit-filter-drawer.is-open { transform: translateX(0); }

  .visit-filter-drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }
  .visit-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
  }
  .visit-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    gap: .5rem;
  }

  /* ── Active filter chips ────────────────────────── */
  .visit-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
  }
  .visit-active-filters.is-visible { display: flex; }

  .visit-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .65rem;
    border-radius: 999px;
    background: #f1f3f5;
    color: #343a40;
    font-size: .8rem;
    line-height: 1;
  }
  .visit-filter-chip__remove {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    line-height: 1;
    cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<?php $labs = $labs ?? []; ?>

<!-- ── Stat cards ──────────────────────────────────────── -->
<div class="row">
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Kunjungan Hari Ini</h4></div>
        <div class="card-body"><?= (int) $todayTotal ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-door-open"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Masih di Dalam</h4></div>
        <div class="card-body"><?= (int) $nowInside ?></div>
      </div>
    </div>
  </div>
</div>

<!-- ── Main card ───────────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Daftar Kunjungan</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-visit-filter-drawer" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <div class="dropdown">
        <button class="btn btn-success dropdown-toggle" type="button" id="visit-export-dropdown"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-file-excel mr-1"></i> Export Excel
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="visit-export-dropdown">
          <a class="dropdown-item" id="visit-export-all" href="#">
            <i class="fas fa-table mr-2"></i> Semua Data
          </a>
          <a class="dropdown-item" id="visit-export-filtered" href="#">
            <i class="fas fa-filter mr-2"></i> Sesuai Filter Aktif
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="visit-active-filters" class="visit-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="visit-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-visits" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Lab</th>
            <th>Nama Pengunjung</th>
            <th>Instansi / Kelas</th>
            <th>Keperluan</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
            <th>Durasi</th>
            <th>Status</th>
            <th width="100">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ─────────────────────────────────── -->
<div id="visit-filter-overlay" class="visit-filter-overlay"></div>

<!-- ── Side Drawer ────────────────────────────────────── -->
<aside id="visit-filter-drawer" class="visit-filter-drawer" aria-hidden="true">
  <div class="visit-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter Kunjungan</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-visit-filter-drawer" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="visit-filter-drawer__body">
    <div class="form-group">
      <label for="filter-visit-lab" class="font-weight-bold small">Lab</label>
      <select id="filter-visit-lab" class="form-control">
        <option value="">Semua Lab</option>
        <?php foreach ($labs as $l): ?>
        <option value="<?= (int) $l['id'] ?>"><?= esc($l['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-visit-date-from" class="font-weight-bold small">Dari Tanggal</label>
      <input type="date" id="filter-visit-date-from" class="form-control">
    </div>

    <div class="form-group">
      <label for="filter-visit-date-to" class="font-weight-bold small">Sampai Tanggal</label>
      <input type="date" id="filter-visit-date-to" class="form-control">
    </div>

    <div class="form-group mb-0">
      <label for="filter-visit-status" class="font-weight-bold small">Status</label>
      <select id="filter-visit-status" class="form-control">
        <option value="">Semua Status</option>
        <option value="checkedin">Masih di Dalam</option>
        <option value="checkedout">Sudah Keluar</option>
      </select>
    </div>
  </div>

  <div class="visit-filter-drawer__footer">
    <button type="button" id="reset-visit-filter-drawer" class="btn btn-light">Reset</button>
    <button type="button" id="apply-visit-filter-drawer" class="btn btn-primary flex-fill">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  var drawer      = $('#visit-filter-drawer');
  var overlay     = $('#visit-filter-overlay');
  var activeWrap  = $('#visit-active-filters');
  var activeChips = $('#visit-active-filter-chips');

  // Move to body so it isn't clipped by any ancestor overflow
  if (drawer.parent()[0] !== document.body)  drawer.appendTo('body');
  if (overlay.parent()[0] !== document.body) overlay.appendTo('body');

  /* ── Drawer state ───────────────────────── */
  function setDrawerState(open) {
    drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
    overlay.toggleClass('is-open', open);
    $('body').toggleClass('overflow-hidden', open);
  }

  /* ── Filter chip rendering ─────────────── */
  var labLabels = {};
  <?php foreach ($labs as $l): ?>
  labLabels[<?= (int) $l['id'] ?>] = '<?= addslashes(esc($l['name'])) ?>';
  <?php endforeach; ?>
  var statusLabels = { 'checkedin': 'Masih di Dalam', 'checkedout': 'Sudah Keluar' };

  function renderChips() {
    var filters = [
      { key: 'lab',       label: 'Lab',     el: '#filter-visit-lab',       display: labLabels },
      { key: 'date_from', label: 'Dari',    el: '#filter-visit-date-from', display: null },
      { key: 'date_to',   label: 'Sampai',  el: '#filter-visit-date-to',   display: null },
      { key: 'status',    label: 'Status',  el: '#filter-visit-status',    display: statusLabels },
    ].filter(function (f) { return $(f.el).val() !== ''; });

    activeChips.empty();
    if (!filters.length) { activeWrap.removeClass('is-visible'); return; }

    filters.forEach(function (f) {
      var raw     = $(f.el).val();
      var display = f.display ? (f.display[raw] || raw) : raw;
      var chip = $('<span class="visit-filter-chip"></span>');
      chip.append(document.createTextNode(f.label + ': ' + display));
      var rm = $('<button type="button" class="visit-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
      rm.data('filter-key', f.key);
      chip.append(rm);
      activeChips.append(chip);
    });
    activeWrap.addClass('is-visible');
  }

  /* ── DataTable ──────────────────────────── */
  var table = $('#table-visits').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[5, 'desc']],
    ajax: {
      url : '<?= base_url('admin/visits/datatable') ?>',
      data: function (d) {
        d.filter_lab_id    = $('#filter-visit-lab').val();
        d.filter_date_from = $('#filter-visit-date-from').val();
        d.filter_date_to   = $('#filter-visit-date-to').val();
        d.filter_status    = $('#filter-visit-status').val();
      }
    },
    columnDefs: [
      { targets: [0, 4, 7, 8, 9], orderable: false, searchable: false },
      { targets: [6],              searchable: false },
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
      emptyTable  : 'Belum ada data kunjungan.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ─────────────────────────────── */
  $('#open-visit-filter-drawer').on('click', function () { setDrawerState(true); });
  $('#close-visit-filter-drawer, #visit-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-visit-filter-drawer').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-visit-filter-drawer').on('click', function () {
    $('#filter-visit-lab, #filter-visit-status').val('');
    $('#filter-visit-date-from, #filter-visit-date-to').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = { lab: '#filter-visit-lab', date_from: '#filter-visit-date-from', date_to: '#filter-visit-date-to', status: '#filter-visit-status' };
  activeChips.on('click', '.visit-filter-chip__remove', function () {
    var key = $(this).data('filter-key');
    if (keyMap[key]) $(keyMap[key]).val('');
    table.ajax.reload();
    renderChips();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') setDrawerState(false);
  });

  /* ── Force Checkout ─────────────────────── */
  var csrfToken = '<?= csrf_token() ?>';
  var csrfHash  = '<?= csrf_hash() ?>';

  $(document).on('click', '.force-checkout-btn', function () {
    var btn     = $(this);
    var visitId = btn.data('id');
    var name    = btn.data('name');

    if (! confirm('Force checkout untuk "' + name + '"?\nWaktu keluar akan diisi dengan waktu sekarang.')) {
      return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    var postData = {};
    postData[csrfToken] = csrfHash;

    $.ajax({
      url     : '<?= base_url('admin/visits/') ?>' + visitId + '/force-checkout',
      method  : 'POST',
      data    : postData,
      dataType: 'json',
      success : function (res) {
        if (res.success) {
          table.ajax.reload(null, false);
        }
      },
      error   : function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.error)
          ? xhr.responseJSON.error
          : 'Gagal melakukan force checkout.';
        alert(msg);
        btn.prop('disabled', false).html('<i class="fas fa-sign-out-alt mr-1"></i>Checkout');
      }
    });
  });

  /* ── Export ──────────────────────────────── */
  var exportBaseUrl = '<?= base_url('admin/visits/export') ?>';

  function buildVisitExportUrl(withFilters) {
    var params = {};
    if (withFilters) {
      var lab    = $('#filter-visit-lab').val();
      var from   = $('#filter-visit-date-from').val();
      var until  = $('#filter-visit-date-to').val();
      var status = $('#filter-visit-status').val();
      if (lab)    { params.filter_lab_id    = lab; }
      if (from)   { params.filter_date_from = from; }
      if (until)  { params.filter_date_to   = until; }
      if (status) { params.filter_status    = status; }
    }
    var qs = $.param(params);
    return exportBaseUrl + (qs ? '?' + qs : '');
  }

  $('#visit-export-all').on('click', function (e) {
    e.preventDefault();
    window.location.href = buildVisitExportUrl(false);
  });

  $('#visit-export-filtered').on('click', function (e) {
    e.preventDefault();
    window.location.href = buildVisitExportUrl(true);
  });
});
</script>
<?= $this->endSection() ?>
