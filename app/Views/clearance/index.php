<?php
$isManager    = $isManager ?? false;
$isAlumni     = $isAlumni ?? false;
$prodiOptions = $prodiOptions ?? [];
?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  .clr-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }
  .clr-filter-overlay.is-open { opacity: 1; visibility: visible; }

  .clr-filter-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: min(380px, 92vw);
    height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0, 0, 0, 0.18);
    transform: translateX(100%);
    transition: transform 0.25s ease;
    z-index: 2147483010;
    display: flex;
    flex-direction: column;
  }
  .clr-filter-drawer.is-open { transform: translateX(0); }

  .clr-filter-drawer__header,
  .clr-filter-drawer__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
  }
  .clr-filter-drawer__header { border-bottom: 1px solid #e9ecef; }
  .clr-filter-drawer__footer { border-top: 1px solid #e9ecef; gap: 0.5rem; }
  .clr-filter-drawer__body { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }

  .clr-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }
  .clr-active-filters.is-visible { display: flex; }

  .clr-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    background: #f1f3f5;
    color: #343a40;
    font-size: 0.8rem;
    line-height: 1;
  }
  .clr-filter-chip__remove {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    line-height: 1;
    cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-file-signature mr-1"></i> <?= $isManager ? 'Daftar Pengajuan Surat Bebas Lab' : ($isAlumni ? 'Riwayat Surat Bebas Lab' : 'Surat Bebas Lab Saya') ?></h4>
        <div class="card-header-action">
          <button type="button" id="open-clr-filter" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-filter"></i> Filter
          </button>
          <div class="dropdown d-inline-block mr-2">
            <button class="btn btn-success dropdown-toggle" type="button" id="clr-export-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="clr-export-dropdown">
              <a class="dropdown-item" id="clr-export-all" href="#">
                <i class="fas fa-table mr-2"></i> Semua Data
              </a>
              <a class="dropdown-item" id="clr-export-filtered" href="#">
                <i class="fas fa-filter mr-2"></i> Sesuai Filter Aktif
              </a>
            </div>
          </div>
          <?php if (! $isAlumni && activeGroupCan('clearance.request.create')): ?>
          <a href="<?= base_url('clearance/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Ajukan Surat Bebas
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div id="clr-active-filters" class="clr-active-filters" aria-live="polite">
          <span class="text-muted small">Filter aktif:</span>
          <div id="clr-active-filter-chips" class="d-flex flex-wrap" style="gap: 0.5rem;"></div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-striped" id="table-clearance" style="width:100%;">
            <thead>
              <tr>
                <th width="40">#</th>
                <th>Kode</th>
                <th>Pemohon</th>
                <th>Prodi</th>
                <th>Lab</th>
                <th>Status</th>
                <th>Diajukan</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="clr-filter-overlay" class="clr-filter-overlay"></div>
<aside id="clr-filter-drawer" class="clr-filter-drawer" aria-hidden="true">
  <div class="clr-filter-drawer__header">
    <h6 class="mb-0">Filter Surat Bebas Lab</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-clr-filter" aria-label="Tutup filter">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="clr-filter-drawer__body">
    <div class="form-group">
      <label for="filter-clearance-status">Status</label>
      <select id="filter-clearance-status" class="form-control">
        <option value="">Semua Status</option>
        <option value="submitted">Diajukan</option>
        <option value="approved">Terbit</option>
        <option value="rejected">Ditolak</option>
        <option value="canceled">Dibatalkan</option>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-clearance-prodi">Program Studi</label>
      <select id="filter-clearance-prodi" class="form-control">
        <option value="">Semua Prodi</option>
        <?php foreach ($prodiOptions as $prodi): ?>
        <option value="<?= esc($prodi, 'attr') ?>"><?= esc($prodi) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-clearance-from">Tanggal Diajukan (Dari)</label>
      <input type="date" id="filter-clearance-from" class="form-control">
    </div>

    <div class="form-group mb-0">
      <label for="filter-clearance-until">Tanggal Diajukan (Sampai)</label>
      <input type="date" id="filter-clearance-until" class="form-control">
    </div>
  </div>
  <div class="clr-filter-drawer__footer">
    <button type="button" id="reset-clr-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-clr-filter" class="btn btn-primary">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script>
  $(function () {
    var isManager = <?= $isManager ? 'true' : 'false' ?>;

    var drawer       = $('#clr-filter-drawer');
    var overlay      = $('#clr-filter-overlay');
    var activeWrap   = $('#clr-active-filters');
    var activeChips  = $('#clr-active-filter-chips');

    if (drawer.parent()[0] !== document.body) { drawer.appendTo('body'); }
    if (overlay.parent()[0] !== document.body) { overlay.appendTo('body'); }

    function setDrawerState(isOpen) {
      drawer.toggleClass('is-open', isOpen).attr('aria-hidden', isOpen ? 'false' : 'true');
      overlay.toggleClass('is-open', isOpen);
      $('body').toggleClass('overflow-hidden', isOpen);
    }

    var statusLabels = {
      submitted: 'Diajukan', approved: 'Terbit', rejected: 'Ditolak', canceled: 'Dibatalkan'
    };

    function renderChips() {
      var filters = [
        { key: 'status', label: 'Status', value: $('#filter-clearance-status').val(), map: statusLabels },
        { key: 'prodi',  label: 'Prodi',  value: $('#filter-clearance-prodi').val() },
        { key: 'from',   label: 'Dari',   value: $('#filter-clearance-from').val() },
        { key: 'until',  label: 'Sampai', value: $('#filter-clearance-until').val() }
      ].filter(function (f) { return f.value; });

      activeChips.empty();
      if (!filters.length) { activeWrap.removeClass('is-visible'); return; }

      filters.forEach(function (f) {
        var display = f.map ? (f.map[f.value] || f.value) : f.value;
        var chip = $('<span class="clr-filter-chip"></span>');
        chip.append(document.createTextNode(f.label + ': ' + display));
        var rm = $('<button type="button" class="clr-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
        rm.attr('data-fk', f.key);
        chip.append(rm);
        activeChips.append(chip);
      });
      activeWrap.addClass('is-visible');
    }

    var columnDefs = [
      { targets: [0, 7], orderable: false, searchable: false },
      { targets: [0], className: 'text-center' },
      { targets: [7], className: 'text-right' }
    ];
    if (!isManager) {
      columnDefs.push({ targets: [2], visible: false }); // sembunyikan kolom Pemohon
    }

    var table = $('#table-clearance').DataTable({
      serverSide: true,
      processing: true,
      pageLength: 10,
      order: [[6, 'desc']],
      columnDefs: columnDefs,
      ajax: {
        url: '<?= base_url('clearance/datatable') ?>',
        data: function (d) {
          d.filter_status = $('#filter-clearance-status').val();
          d.filter_prodi  = $('#filter-clearance-prodi').val();
          d.filter_from   = $('#filter-clearance-from').val();
          d.filter_until  = $('#filter-clearance-until').val();
        }
      },
      drawCallback: function () {
        var api   = this.api();
        var start = api.page.info().start;
        api.column(0).nodes().each(function (cell, i) {
          cell.innerHTML = start + i + 1;
        });
      },
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ \u2013 _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        emptyTable: 'Belum ada pengajuan surat bebas lab.',
        zeroRecords: 'Data tidak ditemukan.',
        processing: '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
        paginate: { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
      }
    });

    function applyFilters() {
      table.ajax.reload();
      renderChips();
    }

    $('#open-clr-filter').on('click', function () { setDrawerState(true); });
    $('#close-clr-filter, #clr-filter-overlay').on('click', function () { setDrawerState(false); });

    $('#apply-clr-filter').on('click', function () {
      applyFilters();
      setDrawerState(false);
    });

    $('#reset-clr-filter').on('click', function () {
      $('#filter-clearance-status, #filter-clearance-prodi').val('');
      $('#filter-clearance-from, #filter-clearance-until').val('');
      applyFilters();
    });

    var keyMap = {
      status: '#filter-clearance-status',
      prodi:  '#filter-clearance-prodi',
      from:   '#filter-clearance-from',
      until:  '#filter-clearance-until'
    };
    activeChips.on('click', '.clr-filter-chip__remove', function () {
      var key = $(this).attr('data-fk');
      if (keyMap[key]) { $(keyMap[key]).val(''); }
      applyFilters();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') { setDrawerState(false); }
    });

    /* ── Export ───────────────────────────── */
    var exportBaseUrl = '<?= base_url('clearance/export') ?>';

    function buildExportUrl(withFilters) {
      var params = {};
      if (withFilters) {
        var status = $('#filter-clearance-status').val();
        var prodi  = $('#filter-clearance-prodi').val();
        var from   = $('#filter-clearance-from').val();
        var until  = $('#filter-clearance-until').val();
        if (status) { params.filter_status = status; }
        if (prodi)  { params.filter_prodi  = prodi; }
        if (from)   { params.filter_from   = from; }
        if (until)  { params.filter_until  = until; }
      }
      var qs = $.param(params);
      return exportBaseUrl + (qs ? '?' + qs : '');
    }

    $('#clr-export-all').on('click', function (e) {
      e.preventDefault();
      window.location.href = buildExportUrl(false);
    });

    $('#clr-export-filtered').on('click', function (e) {
      e.preventDefault();
      window.location.href = buildExportUrl(true);
    });
  });
</script>
<?= $this->endSection() ?>


