<?php $assets = $assets ?? []; ?>
<?php $labs = $labs ?? []; ?>
<?php $filterAssetId = (int) ($filterAssetId ?? 0); ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<style>
  .asset-item-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }

  .asset-item-filter-overlay.is-open {
    opacity: 1;
    visibility: visible;
  }

  .asset-item-filter-drawer {
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

  .asset-item-filter-drawer.is-open {
    transform: translateX(0);
  }

  .asset-item-filter-drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }

  .asset-item-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
  }

  .asset-item-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
  }

  .asset-item-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .asset-item-active-filters.is-visible {
    display: flex;
  }

  .asset-item-filter-chip {
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

  .asset-item-filter-chip__remove {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    line-height: 1;
    cursor: pointer;
  }

  .asset-item-filter-drawer .select2-container {
    width: 100% !important;
  }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Manajemen Item Alat</h4>
        <div class="card-header-action">
          <button type="button" id="open-item-filter" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-filter"></i> Filter
          </button>
          <button type="button" class="btn btn-outline-success mr-2" data-toggle="modal" data-target="#modal-bulk-generate">
            <i class="fas fa-magic"></i> Generate Item
          </button>
          <a href="<?= base_url('admin/loans/asset-items/create' . ($filterAssetId > 0 ? '?asset_id=' . $filterAssetId : '')) ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Item
          </a>
        </div>
      </div>
      <div class="card-body">
        <div id="asset-item-active-filters" class="asset-item-active-filters" aria-live="polite">
          <span class="text-muted small">Filter aktif:</span>
          <div id="asset-item-active-filter-chips" class="d-flex flex-wrap" style="gap: 0.5rem;"></div>
        </div>

        <div class="table-responsive">
          <table id="table-asset-items" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th width="40">#</th>
                <th>Item Code</th>
                <th>Master Alat</th>
                <th>Serial</th>
                <th>Lab</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Loanable</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="asset-item-filter-overlay" class="asset-item-filter-overlay"></div>

<aside id="asset-item-filter-drawer" class="asset-item-filter-drawer" aria-hidden="true">
  <div class="asset-item-filter-drawer__header">
    <h6 class="mb-0">Filter Item Alat</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-item-filter" aria-label="Tutup filter">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="asset-item-filter-drawer__body">
    <div class="form-group">
      <label for="filter-asset-id">Master Alat</label>
      <select id="filter-asset-id" class="form-control">
        <option value="">Semua Master Alat</option>
        <?php foreach ($assets as $asset): ?>
          <?php $assetId = (int) ($asset['id'] ?? 0); ?>
          <option value="<?= $assetId ?>" <?= $filterAssetId === $assetId ? 'selected' : '' ?>>
            <?= esc(($asset['asset_code'] ?? '-') . ' - ' . ($asset['name'] ?? '')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-lab-id">Lab</label>
      <select id="filter-lab-id" class="form-control">
        <option value="">Semua Lab</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= (int) $lab['id'] ?>"><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-condition">Kondisi</label>
      <select id="filter-condition" class="form-control">
        <option value="">Semua Kondisi</option>
        <option value="baik">Baik</option>
        <option value="perlu_perbaikan">Perlu Perbaikan</option>
        <option value="rusak">Rusak</option>
        <option value="rusak_ringan">Rusak Ringan</option>
        <option value="rusak_berat">Rusak Berat</option>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-inventory">Status Inventaris</label>
      <select id="filter-inventory" class="form-control">
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="dipinjam">Dipinjam</option>
        <option value="dalam_perbaikan">Dalam Perbaikan</option>
        <option value="dihapuskan">Dihapuskan</option>
        <option value="hilang">Hilang</option>
      </select>
    </div>

    <div class="form-group mb-0">
      <label for="filter-loanable">Boleh Dipinjam</label>
      <select id="filter-loanable" class="form-control">
        <option value="">Semua</option>
        <option value="1">Ya</option>
        <option value="0">Tidak</option>
      </select>
    </div>
  </div>
  <div class="asset-item-filter-drawer__footer">
    <button type="button" id="reset-item-filter" class="btn btn-light">Reset</button>
    <button type="button" id="apply-item-filter" class="btn btn-primary">Terapkan Filter</button>
  </div>
</aside>

<div class="modal fade" id="modal-bulk-generate" tabindex="-1" role="dialog" aria-labelledby="modal-bulk-generate-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-bulk-generate-label"><i class="fas fa-magic mr-1"></i> Generate Bulk Item</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form method="post" action="<?= base_url('admin/loans/asset-items/bulk-generate') ?>">
        <div class="modal-body">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="bulk_asset_id">Master Alat</label>
            <select name="asset_id" id="bulk_asset_id" class="form-control" required>
              <option value="">Pilih Master Alat</option>
              <?php foreach ($assets as $asset): ?>
                <?php $assetId = (int) ($asset['id'] ?? 0); ?>
                <option value="<?= $assetId ?>" <?= $filterAssetId === $assetId ? 'selected' : '' ?>>
                  <?= esc(($asset['asset_code'] ?? '-') . ' - ' . ($asset['name'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group mb-0">
            <label for="bulk_qty">Jumlah Item</label>
            <input type="number" name="qty" id="bulk_qty" class="form-control" min="1" max="500" value="1" required>
            <small class="text-muted">Maksimum 500 item per generate.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Generate</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>"></script>
<script>
  $(function () {
    var drawer = $('#asset-item-filter-drawer');
    var overlay = $('#asset-item-filter-overlay');
    var activeFilterWrap = $('#asset-item-active-filters');
    var activeFilterChips = $('#asset-item-active-filter-chips');

    if (drawer.parent()[0] !== document.body) {
      drawer.appendTo('body');
    }
    if (overlay.parent()[0] !== document.body) {
      overlay.appendTo('body');
    }
    var genModal = $('#modal-bulk-generate');
    if (genModal.parent()[0] !== document.body) {
      genModal.appendTo('body');
    }

    function initFilterAssetSelect2() {
      var filterSelect = $('#filter-asset-id');
      if (!filterSelect.length || !$.fn.select2) {
        return;
      }

      if (filterSelect.hasClass('select2-hidden-accessible')) {
        return;
      }

      filterSelect.select2({
        placeholder: 'Semua Master Alat',
        allowClear: true,
        width: '100%',
        dropdownParent: drawer
      });
    }

    if ($.fn.select2) {
      initFilterAssetSelect2();
    } else {
      if (!$('link[data-asset-select2-css]').length) {
        $('<link>', {
          rel: 'stylesheet',
          href: '<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>',
          'data-asset-select2-css': '1'
        }).appendTo('head');
      }

      $.getScript('<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>')
        .done(function () {
          initFilterAssetSelect2();
        });
    }

    function setDrawerState(open) {
      drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
      overlay.toggleClass('is-open', open);
      $('body').toggleClass('overflow-hidden', open);
    }

    function renderFilterChips() {
      var filters = [];

      var assetVal = $('#filter-asset-id').val();
      if (assetVal) {
        filters.push({
          key: 'asset',
          label: 'Master',
          value: $('#filter-asset-id option:selected').text()
        });
      }

      var labVal = $('#filter-lab-id').val();
      if (labVal) {
        filters.push({
          key: 'lab',
          label: 'Lab',
          value: $('#filter-lab-id option:selected').text()
        });
      }

      var condVal = $('#filter-condition').val();
      if (condVal) {
        filters.push({
          key: 'condition',
          label: 'Kondisi',
          value: $('#filter-condition option:selected').text()
        });
      }

      var invVal = $('#filter-inventory').val();
      if (invVal) {
        filters.push({
          key: 'inventory',
          label: 'Status',
          value: $('#filter-inventory option:selected').text()
        });
      }

      var loanVal = $('#filter-loanable').val();
      if (loanVal !== '') {
        filters.push({
          key: 'loanable',
          label: 'Loanable',
          value: $('#filter-loanable option:selected').text()
        });
      }

      activeFilterChips.empty();
      if (!filters.length) {
        activeFilterWrap.removeClass('is-visible');
        return;
      }

      filters.forEach(function (f) {
        var chip = $('<span class="asset-item-filter-chip"></span>').text(f.label + ': ' + f.value);
        var removeBtn = $('<button type="button" class="asset-item-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
        removeBtn.attr('data-filter-key', f.key);
        chip.append(removeBtn);
        activeFilterChips.append(chip);
      });

      activeFilterWrap.addClass('is-visible');
    }

    var table = $('#table-asset-items').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 25,
      order: [[1, 'asc']],
      ajax: {
        url: '<?= base_url('admin/loans/asset-items/datatable') ?>',
        data: function (d) {
          d.filter_asset_id = $('#filter-asset-id').val();
          d.filter_lab_id = $('#filter-lab-id').val();
          d.filter_condition = $('#filter-condition').val();
          d.filter_inventory = $('#filter-inventory').val();
          d.filter_loanable = $('#filter-loanable').val();
        }
      },
      columnDefs: [
        { orderable: false, targets: [0, 8] },
        { searchable: false, targets: [0, 8] },
      ],
      drawCallback: function () {
        if (window.initSwalDeleteForms) {
          window.initSwalDeleteForms(document);
        }
      },
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(difilter dari _MAX_ total data)',
        zeroRecords: 'Data tidak ditemukan',
        emptyTable: 'Belum ada item alat',
        processing: '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: '&rsaquo;',
          previous: '&lsaquo;'
        }
      }
    });

    $('#open-item-filter').on('click', function () {
      initFilterAssetSelect2();
      setDrawerState(true);
    });

    $('#close-item-filter, #asset-item-filter-overlay').on('click', function () {
      setDrawerState(false);
    });

    $('#apply-item-filter').on('click', function () {
      table.ajax.reload();
      renderFilterChips();
      setDrawerState(false);
    });

    $('#reset-item-filter').on('click', function () {
      $('#filter-asset-id').val('').trigger('change.select2');
      $('#filter-lab-id').val('');
      $('#filter-condition').val('');
      $('#filter-inventory').val('');
      $('#filter-loanable').val('');
      table.ajax.reload();
      renderFilterChips();
    });

    activeFilterChips.on('click', '.asset-item-filter-chip__remove', function () {
      var key = $(this).data('filter-key');
      if (key === 'asset') {
        $('#filter-asset-id').val('').trigger('change.select2');
      }
      if (key === 'lab') {
        $('#filter-lab-id').val('');
      }
      if (key === 'condition') {
        $('#filter-condition').val('');
      }
      if (key === 'inventory') {
        $('#filter-inventory').val('');
      }
      if (key === 'loanable') {
        $('#filter-loanable').val('');
      }
      table.ajax.reload();
      renderFilterChips();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') {
        setDrawerState(false);
      }
    });

    $(document).on('submit', '.js-swal-delete-form', function (e) {
      if (window.Swal) {
        e.preventDefault();
        var form = this;
        Swal.fire({
          title: $(form).data('swal-title') || 'Hapus data?',
          text: $(form).data('swal-text') || 'Data akan dihapus permanen.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: $(form).data('swal-confirm') || 'Ya, hapus',
          cancelButtonText: $(form).data('swal-cancel') || 'Batal',
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      }
    });

    renderFilterChips();
  });
</script>
<?= $this->endSection() ?>
