<?php
$assets   = $assets ?? [];
$types    = $types ?? [];
$statuses = $statuses ?? [];
?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>"/>
<style>
  .mt-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }
  .mt-filter-overlay.is-open { opacity: 1; visibility: visible; }

  .mt-filter-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: min(400px, 92vw);
    height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0, 0, 0, 0.18);
    transform: translateX(100%);
    transition: transform 0.25s ease;
    z-index: 2147483010;
    display: flex;
    flex-direction: column;
  }
  .mt-filter-drawer.is-open { transform: translateX(0); }

  .mt-filter-drawer__header,
  .mt-filter-drawer__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
  }
  .mt-filter-drawer__header { border-bottom: 1px solid #e9ecef; }
  .mt-filter-drawer__footer { border-top: 1px solid #e9ecef; gap: 0.5rem; }
  .mt-filter-drawer__body { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }

  .mt-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }
  .mt-active-filters.is-visible { display: flex; }

  .mt-filter-chip {
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
  .mt-filter-chip__remove {
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
        <h4><i class="fas fa-tools mr-1"></i> Riwayat Perawatan Aset</h4>
        <div class="card-header-action">
          <button type="button" id="open-mt-filter" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-filter"></i> Filter
          </button>
          <a href="<?= base_url('admin/loans/maintenances/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Perawatan
          </a>
        </div>
      </div>
      <div class="card-body">
        <div id="mt-active-filters" class="mt-active-filters" aria-live="polite">
          <span class="text-muted small">Filter aktif:</span>
          <div id="mt-active-filter-chips" class="d-flex flex-wrap" style="gap: 0.5rem;"></div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-striped" id="table-maintenances" style="width:100%;">
            <thead>
              <tr>
                <th width="40">#</th>
                <th>Aset</th>
                <th>Tipe</th>
                <th>Jadwal</th>
                <th>Dikerjakan</th>
                <th>Status</th>
                <th>Pelaksana</th>
                <th>Biaya</th>
                <th>Jadwal Berikutnya</th>
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

<div id="mt-filter-overlay" class="mt-filter-overlay"></div>
<aside id="mt-filter-drawer" class="mt-filter-drawer" aria-hidden="true">
  <div class="mt-filter-drawer__header">
    <h6 class="mb-0">Filter Perawatan</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-mt-filter" aria-label="Tutup filter">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="mt-filter-drawer__body">
    <div class="form-group">
      <label for="filter-mt-asset">Aset</label>
      <select id="filter-mt-asset" class="form-control select2">
        <option value="">Semua Aset</option>
        <?php foreach ($assets as $a): ?>
          <option value="<?= (int) $a['id'] ?>"><?= esc($a['name']) ?> <?= ! empty($a['asset_code']) ? '(' . esc($a['asset_code']) . ')' : '' ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-mt-type">Tipe Perawatan</label>
      <select id="filter-mt-type" class="form-control">
        <option value="">Semua Tipe</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= esc($t) ?>"><?= esc(ucfirst($t)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-mt-status">Status</label>
      <select id="filter-mt-status" class="form-control">
        <option value="">Semua Status</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= esc($s) ?>"><?= esc(str_replace('_', ' ', ucfirst($s))) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Tanggal Jadwal</label>
      <div class="form-row">
        <div class="col">
          <input type="date" id="filter-mt-scheduled-from" class="form-control" placeholder="Dari">
        </div>
        <div class="col">
          <input type="date" id="filter-mt-scheduled-until" class="form-control" placeholder="Sampai">
        </div>
      </div>
    </div>

    <div class="form-group">
      <label>Tanggal Dikerjakan</label>
      <div class="form-row">
        <div class="col">
          <input type="date" id="filter-mt-performed-from" class="form-control" placeholder="Dari">
        </div>
        <div class="col">
          <input type="date" id="filter-mt-performed-until" class="form-control" placeholder="Sampai">
        </div>
      </div>
    </div>

    <div class="form-group mb-0">
      <label>Biaya (Rp)</label>
      <div class="form-row">
        <div class="col">
          <input type="number" id="filter-mt-cost-min" class="form-control" placeholder="Min" min="0">
        </div>
        <div class="col">
          <input type="number" id="filter-mt-cost-max" class="form-control" placeholder="Max" min="0">
        </div>
      </div>
    </div>
  </div>
  <div class="mt-filter-drawer__footer">
    <button type="button" id="reset-mt-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-mt-filter" class="btn btn-primary">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>"></script>
<script>
  $(function () {
    var drawer       = $('#mt-filter-drawer');
    var overlay      = $('#mt-filter-overlay');
    var activeWrap   = $('#mt-active-filters');
    var activeChips  = $('#mt-active-filter-chips');

    if (drawer.parent()[0] !== document.body) { drawer.appendTo('body'); }
    if (overlay.parent()[0] !== document.body) { overlay.appendTo('body'); }

    function setDrawerState(isOpen) {
      drawer.toggleClass('is-open', isOpen).attr('aria-hidden', isOpen ? 'false' : 'true');
      overlay.toggleClass('is-open', isOpen);
      $('body').toggleClass('overflow-hidden', isOpen);
    }

    var typeLabels   = { preventive: 'Preventif', corrective: 'Korektif', calibration: 'Kalibrasi', inspection: 'Inspeksi' };
    var statusLabels = { scheduled: 'Terjadwal', in_progress: 'Diproses', completed: 'Selesai', cancelled: 'Dibatalkan' };

    function getVal(sel) { return $(sel).val(); }

    function renderChips() {
      var filters = [
        { key: 'asset_id',  label: 'Aset',   value: $('#filter-mt-asset option:selected').text(), raw: getVal('#filter-mt-asset') },
        { key: 'type',      label: 'Tipe',   value: getVal('#filter-mt-type'), map: typeLabels },
        { key: 'status',    label: 'Status', value: getVal('#filter-mt-status'), map: statusLabels },
        { key: 'scheduled_from', label: 'Jadwal Dari',   value: getVal('#filter-mt-scheduled-from') },
        { key: 'scheduled_until',label: 'Jadwal Sampai', value: getVal('#filter-mt-scheduled-until') },
        { key: 'performed_from', label: 'Dikerjakan Dari',   value: getVal('#filter-mt-performed-from') },
        { key: 'performed_until',label: 'Dikerjakan Sampai', value: getVal('#filter-mt-performed-until') },
        { key: 'cost_min',  label: 'Biaya Min', value: getVal('#filter-mt-cost-min') },
        { key: 'cost_max',  label: 'Biaya Max', value: getVal('#filter-mt-cost-max') },
      ].filter(function (f) {
        if (f.key === 'asset_id') return f.raw && f.raw !== '0' && f.raw !== '';
        return f.value !== '';
      });

      activeChips.empty();
      if (!filters.length) { activeWrap.removeClass('is-visible'); return; }

      filters.forEach(function (f) {
        var display = f.map ? (f.map[f.value] || f.value) : f.value;
        var chip = $('<span class="mt-filter-chip"></span>');
        chip.append(document.createTextNode(f.label + ': ' + display));
        var rm = $('<button type="button" class="mt-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
        rm.attr('data-fk', f.key);
        chip.append(rm);
        activeChips.append(chip);
      });
      activeWrap.addClass('is-visible');
    }

    var table = $('#table-maintenances').DataTable({
      serverSide: true,
      processing: true,
      pageLength: 25,
      order: [[3, 'desc']],
      columnDefs: [
        { targets: [0, 9], orderable: false, searchable: false },
        { targets: [0], className: 'text-center' },
        { targets: [9], className: 'text-right' }
      ],
      ajax: {
        url: '<?= base_url('admin/loans/maintenances/datatable') ?>',
        data: function (d) {
          d.filter_asset_id        = $('#filter-mt-asset').val();
          d.filter_type            = $('#filter-mt-type').val();
          d.filter_status          = $('#filter-mt-status').val();
          d.filter_scheduled_from  = $('#filter-mt-scheduled-from').val();
          d.filter_scheduled_until = $('#filter-mt-scheduled-until').val();
          d.filter_performed_from  = $('#filter-mt-performed-from').val();
          d.filter_performed_until = $('#filter-mt-performed-until').val();
          d.filter_cost_min        = $('#filter-mt-cost-min').val();
          d.filter_cost_max        = $('#filter-mt-cost-max').val();
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
        emptyTable: 'Belum ada catatan perawatan.',
        zeroRecords: 'Data tidak ditemukan.',
        processing: '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
        paginate: { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
      }
    });

    function applyFilters() {
      table.ajax.reload();
      renderChips();
    }

    $('#open-mt-filter').on('click', function () { setDrawerState(true); });
    $('#close-mt-filter, #mt-filter-overlay').on('click', function () { setDrawerState(false); });

    $('#apply-mt-filter').on('click', function () {
      applyFilters();
      setDrawerState(false);
    });

    $('#reset-mt-filter').on('click', function () {
      $('#filter-mt-asset, #filter-mt-type, #filter-mt-status').val('');
      $('#filter-mt-scheduled-from, #filter-mt-scheduled-until').val('');
      $('#filter-mt-performed-from, #filter-mt-performed-until').val('');
      $('#filter-mt-cost-min, #filter-mt-cost-max').val('');
      applyFilters();
    });

    var keyMap = {
      asset_id:        '#filter-mt-asset',
      type:            '#filter-mt-type',
      status:          '#filter-mt-status',
      scheduled_from:  '#filter-mt-scheduled-from',
      scheduled_until: '#filter-mt-scheduled-until',
      performed_from:  '#filter-mt-performed-from',
      performed_until: '#filter-mt-performed-until',
      cost_min:        '#filter-mt-cost-min',
      cost_max:        '#filter-mt-cost-max'
    };
    activeChips.on('click', '.mt-filter-chip__remove', function () {
      var key = $(this).attr('data-fk');
      if (keyMap[key]) { $(keyMap[key]).val('').trigger('change'); }
      applyFilters();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') { setDrawerState(false); }
    });

    // Re-init select2 inside drawer when drawer opens
    $('#open-mt-filter').on('click', function () {
      setTimeout(function () {
        $('#filter-mt-asset').select2({ dropdownParent: $('#mt-filter-drawer'), width: '100%' });
      }, 300);
    });
  });
</script>
<?= $this->endSection() ?>
