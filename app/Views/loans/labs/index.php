<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  .lab-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }

  .lab-filter-overlay.is-open {
    opacity: 1;
    visibility: visible;
  }

  .lab-filter-drawer {
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

  .lab-filter-drawer.is-open {
    transform: translateX(0);
  }

  .lab-filter-drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }

  .lab-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
  }

  .lab-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
  }

  .lab-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .lab-active-filters.is-visible {
    display: flex;
  }

  .lab-filter-chip {
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

  .lab-filter-chip__remove {
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
<?php $dtUrl = base_url('admin/loans/labs/datatable'); ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Lab/Ruangan</h4>
        <div class="card-header-action">
          <button type="button" id="open-filter-drawer" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-filter"></i> Filter
          </button>
          <a href="<?= base_url('admin/loans/labs/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Lab
          </a>
        </div>
      </div>
      <div class="card-body">
        <div id="lab-active-filters" class="lab-active-filters" aria-live="polite">
          <span class="text-muted small">Filter aktif:</span>
          <div id="lab-active-filter-chips" class="d-flex flex-wrap" style="gap: 0.5rem;"></div>
        </div>

        <div class="table-responsive">
          <table id="table-labs" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Deskripsi</th>
                <th>Status Boleh Dipinjam</th>
                <th>Status Kondisi</th>
                <th>Lokasi</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="lab-filter-overlay" class="lab-filter-overlay"></div>
<aside id="lab-filter-drawer" class="lab-filter-drawer" aria-hidden="true">
  <div class="lab-filter-drawer__header">
    <h6 class="mb-0">Filter Master Lab</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-filter-drawer" aria-label="Tutup filter">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="lab-filter-drawer__body">
    <div class="form-group">
      <label for="filter-loanable">Status Boleh Dipinjam</label>
      <select id="filter-loanable" class="form-control">
        <option value="">Semua Status</option>
        <option value="1">Ya</option>
        <option value="0">Tidak</option>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-condition">Status Kondisi</label>
      <select id="filter-condition" class="form-control">
        <option value="">Semua Kondisi</option>
        <option value="baik">Baik</option>
        <option value="perlu_perbaikan">Perlu Perbaikan</option>
        <option value="rusak">Rusak</option>
      </select>
    </div>

    <div class="form-group mb-0">
      <label for="filter-active">Status Lab</label>
      <select id="filter-active" class="form-control">
        <option value="">Semua Status</option>
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
      </select>
    </div>
  </div>
  <div class="lab-filter-drawer__footer">
    <button type="button" id="reset-filter-drawer" class="btn btn-light">Reset</button>
    <button type="button" id="apply-filter-drawer" class="btn btn-primary">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    var drawer = $('#lab-filter-drawer');
    var overlay = $('#lab-filter-overlay');
    var activeFilterWrap = $('#lab-active-filters');
    var activeFilterChips = $('#lab-active-filter-chips');

    if (drawer.parent()[0] !== document.body) {
      drawer.appendTo('body');
    }
    if (overlay.parent()[0] !== document.body) {
      overlay.appendTo('body');
    }

    function setDrawerState(isOpen) {
      drawer.toggleClass('is-open', isOpen).attr('aria-hidden', isOpen ? 'false' : 'true');
      overlay.toggleClass('is-open', isOpen);
      $('body').toggleClass('overflow-hidden', isOpen);
    }

    function applyExactFilter(table, columnIndex, value) {
      if (!value) {
        table.column(columnIndex).search('');
        return;
      }

      table
        .column(columnIndex)
        .search('^' + $.fn.dataTable.util.escapeRegex(value) + '$', true, false);
    }

    function renderActiveFilterChips() {
      var conditionLabels = { 'baik': 'Baik', 'perlu_perbaikan': 'Perlu Perbaikan', 'rusak': 'Rusak' };
      var loanableLabels  = { '1': 'Ya', '0': 'Tidak' };
      var activeLabels    = { '1': 'Aktif', '0': 'Nonaktif' };
      var filters = [
        { key: 'loanable', label: 'Boleh Dipinjam', value: $('#filter-loanable').val(), display: loanableLabels },
        { key: 'condition', label: 'Kondisi', value: $('#filter-condition').val(), display: conditionLabels },
        { key: 'active', label: 'Status Lab', value: $('#filter-active').val(), display: activeLabels }
      ].filter(function (item) {
        return item.value;
      });

      activeFilterChips.empty();

      if (!filters.length) {
        activeFilterWrap.removeClass('is-visible');
        return;
      }

      filters.forEach(function (filter) {
        var chip = $('<span class="lab-filter-chip"></span>');
        var displayValue = filter.display[filter.value] || filter.value;
        chip.append(document.createTextNode(filter.label + ': ' + displayValue));

        var removeBtn = $('<button type="button" class="lab-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
        removeBtn.attr('data-filter-key', filter.key);
        chip.append(removeBtn);
        activeFilterChips.append(chip);
      });

      activeFilterWrap.addClass('is-visible');
    }

    function applyDrawerFilters(table) {
      table.ajax.reload();
      renderActiveFilterChips();
    }

    var tableLabs = $('#table-labs').DataTable({
      serverSide: true,
      processing: true,
      pageLength: 10,
      order: [[1, 'asc']],
      ajax: {
        url: '<?= $dtUrl ?>',
        data: function (d) {
          d.filter_loanable  = $('#filter-loanable').val();
          d.filter_condition = $('#filter-condition').val();
          d.filter_active    = $('#filter-active').val();
        }
      },
      columnDefs: [
        { targets: [0, 4, 5, 8, 9], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data lab.',
        zeroRecords: 'Data tidak ditemukan',
        processing: 'Memuat...',
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: 'Berikutnya',
          previous: 'Sebelumnya'
        }
      }
    });

    $('#open-filter-drawer').on('click', function () {
      setDrawerState(true);
    });

    $('#close-filter-drawer, #lab-filter-overlay').on('click', function () {
      setDrawerState(false);
    });

    $('#apply-filter-drawer').on('click', function () {
      applyDrawerFilters(tableLabs);
      setDrawerState(false);
    });

    $('#reset-filter-drawer').on('click', function () {
      $('#filter-loanable').val('');
      $('#filter-condition').val('');
      $('#filter-active').val('');
      applyDrawerFilters(tableLabs);
    });

    activeFilterChips.on('click', '.lab-filter-chip__remove', function () {
      var key = $(this).attr('data-filter-key');
      if (key === 'loanable') {
        $('#filter-loanable').val('');
      } else if (key === 'condition') {
        $('#filter-condition').val('');
      } else if (key === 'active') {
        $('#filter-active').val('');
      }

      applyDrawerFilters(tableLabs);
    });

    $(document).on('keydown', function (event) {
      if (event.key === 'Escape') {
        setDrawerState(false);
      }
    });

    renderActiveFilterChips();
  });
</script>
<?= $this->endSection() ?>
