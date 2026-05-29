<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  .asset-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }

  .asset-filter-overlay.is-open {
    opacity: 1;
    visibility: visible;
  }

  .asset-filter-drawer {
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

  .asset-filter-drawer.is-open {
    transform: translateX(0);
  }

  .asset-filter-drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }

  .asset-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
  }

  .asset-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
  }

  .asset-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  .asset-active-filters.is-visible {
    display: flex;
  }

  .asset-filter-chip {
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

  .asset-filter-chip__remove {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    line-height: 1;
    cursor: pointer;
  }

  /* Pastikan modal download muncul di atas overlay filter drawer */
  #modal-download {
    z-index: 2147483030 !important;
  }
  #modal-download ~ .modal-backdrop,
  body > .modal-backdrop {
    z-index: 2147483025 !important;
  }
</style>
<?= $this->endSection() ?>
<?php $labs = $labs ?? []; ?>
<?php $assets = $assets ?? []; ?>
<?php
$categoryMap = [];
foreach ($assets as $assetRow) {
  $categoryName = trim((string) ($assetRow['category'] ?? ''));
  if ($categoryName === '') {
    continue;
  }
  $categoryMap[$categoryName] = true;
}
$categoryOptions = array_keys($categoryMap);
sort($categoryOptions);
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Master Alat</h4>
        <div class="card-header-action">
          <button type="button" id="open-filter-drawer" class="btn btn-outline-secondary mr-2">
            <i class="fas fa-filter"></i> Filter
          </button>
          <button type="button" class="btn btn-outline-success mr-2" data-target="#modal-download">
            <i class="fas fa-download"></i> Download
          </button>
          <a href="<?= base_url('admin/loans/assets/import') ?>" class="btn btn-outline-primary mr-2">
            <i class="fas fa-file-import"></i> Import
          </a>
          <a href="<?= base_url('admin/loans/assets/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Alat
          </a>
        </div>
      </div>
      <div class="card-body">
        <div id="asset-active-filters" class="asset-active-filters" aria-live="polite">
          <span class="text-muted small">Filter aktif:</span>
          <div id="asset-active-filter-chips" class="d-flex flex-wrap" style="gap: 0.5rem;"></div>
        </div>

        <div class="table-responsive">
          <table id="table-assets" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Lab</th>
                <th>Kategori</th>
                <th>Boleh Dipinjam</th>
                <th>Kondisi</th>
                <th>Inventaris</th>
                <th>Stok</th>
                <th>Maks Jam</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($assets)): ?>
                <?php foreach ($assets as $asset): ?>
                <tr>
                  <td>
                    <img src="<?= ! empty($asset['photo']) ? base_url($asset['photo']) : base_url('assets/img/stisla-fill.svg') ?>" alt="foto alat" class="img-thumbnail" style="width: 48px; height: 48px; object-fit: contain;">
                  </td>
                  <td><code><?= esc($asset['asset_code'] ?? '-') ?></code></td>
                  <td>
                    <?= esc($asset['name']) ?>
                    <?php if (! empty($asset['brand']) || ! empty($asset['model'])): ?>
                      <br><small class="text-muted"><?= esc(trim(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? ''))) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= esc($asset['lab_name'] ?? '-') ?></td>
                  <td><?= esc($asset['category'] ?? '-') ?></td>
                  <td>
                    <?php if ((int) ($asset['is_loanable'] ?? 0) === 1): ?>
                      <span class="badge badge-success">Ya</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Tidak</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php $condition = (string) ($asset['condition_status'] ?? 'baik'); ?>
                    <?php if ($condition === 'baik'): ?>
                      <span class="badge badge-success">Baik</span>
                    <?php elseif ($condition === 'perlu_perbaikan'): ?>
                      <span class="badge badge-warning">Perlu Perbaikan</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Rusak</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                      $invStatus = (string) ($asset['inventory_status'] ?? 'aktif');
                      $invBadge = [
                          'aktif' => 'badge-success',
                          'dipinjam' => 'badge-info',
                          'dalam_perbaikan' => 'badge-warning',
                          'dihapuskan' => 'badge-dark',
                          'hilang' => 'badge-danger',
                      ][$invStatus] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?= $invBadge ?>"><?= esc(str_replace('_', ' ', ucfirst($invStatus))) ?></span>
                  </td>
                  <td><?= (int) $asset['stock_available'] ?>/<?= (int) $asset['stock_total'] ?><?= ! empty($asset['unit_symbol']) ? ' ' . esc($asset['unit_symbol']) : '' ?></td>
                  <td><?= (int) $asset['max_loan_hours'] === 0 ? 'Unlimited' : (int) $asset['max_loan_hours'] . ' jam' ?></td>
                  <td>
                    <?php if ((int) $asset['is_active'] === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/assets/edit/' . $asset['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/assets/delete/' . $asset['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus alat?"
                          data-swal-text="Alat '<?= esc($asset['name']) ?>' akan dihapus permanen."
                          data-swal-confirm="Ya, hapus"
                          data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="asset-filter-overlay" class="asset-filter-overlay"></div>

<!-- Modal Download -->
<div class="modal fade" id="modal-download" tabindex="-1" role="dialog" aria-labelledby="modal-download-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-download-label"><i class="fas fa-download mr-1"></i> Download Data Master Alat</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="form-download" action="<?= base_url('admin/loans/assets/download') ?>" method="get" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Format File</label>
            <div class="d-flex" style="gap: 1rem;">
              <div class="custom-control custom-radio">
                <input type="radio" id="fmt-csv" name="format" value="csv" class="custom-control-input" checked>
                <label class="custom-control-label" for="fmt-csv"><i class="fas fa-file-csv text-success mr-1"></i> CSV</label>
              </div>
              <div class="custom-control custom-radio">
                <input type="radio" id="fmt-excel" name="format" value="excel" class="custom-control-input">
                <label class="custom-control-label" for="fmt-excel"><i class="fas fa-file-excel text-success mr-1"></i> Excel (.xls)</label>
              </div>
            </div>
          </div>
          <hr>
          <p class="text-muted small mb-2">Filter data yang akan diunduh (opsional — kosongkan untuk mengunduh semua data):</p>

          <div class="form-group">
            <label for="dl-lab">Lab</label>
            <select id="dl-lab" name="lab_id" class="form-control form-control-sm">
              <option value="">Semua Lab</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= (int) $lab['id'] ?>"><?= esc($lab['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="dl-category">Kategori</label>
            <select id="dl-category" name="category" class="form-control form-control-sm">
              <option value="">Semua Kategori</option>
              <?php foreach ($categoryOptions as $categoryOption): ?>
                <option value="<?= esc($categoryOption) ?>"><?= esc($categoryOption) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="dl-condition">Kondisi</label>
              <select id="dl-condition" name="condition_status" class="form-control form-control-sm">
                <option value="">Semua</option>
                <option value="baik">Baik</option>
                <option value="perlu_perbaikan">Perlu Perbaikan</option>
                <option value="rusak">Rusak</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="dl-inventory">Status Inventaris</label>
              <select id="dl-inventory" name="inventory_status" class="form-control form-control-sm">
                <option value="">Semua</option>
                <option value="aktif">Aktif</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="dalam_perbaikan">Dalam Perbaikan</option>
                <option value="dihapuskan">Dihapuskan</option>
                <option value="hilang">Hilang</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="dl-loanable">Boleh Dipinjam</label>
              <select id="dl-loanable" name="is_loanable" class="form-control form-control-sm">
                <option value="">Semua</option>
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="dl-active">Status Aktif</label>
              <select id="dl-active" name="is_active" class="form-control form-control-sm">
                <option value="">Semua</option>
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-download mr-1"></i> Download</button>
        </div>
      </form>
    </div>
  </div>
</div>

<aside id="asset-filter-drawer" class="asset-filter-drawer" aria-hidden="true">
  <div class="asset-filter-drawer__header">
    <h6 class="mb-0">Filter Master Alat</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-filter-drawer" aria-label="Tutup filter">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="asset-filter-drawer__body">
    <div class="form-group">
      <label for="filter-lab">Lab</label>
      <select id="filter-lab" class="form-control">
        <option value="">Semua Lab</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= esc($lab['name']) ?>"><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-category">Kategori</label>
      <select id="filter-category" class="form-control">
        <option value="">Semua Kategori</option>
        <?php foreach ($categoryOptions as $categoryOption): ?>
          <option value="<?= esc($categoryOption) ?>"><?= esc($categoryOption) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="filter-condition">Kondisi</label>
      <select id="filter-condition" class="form-control">
        <option value="">Semua Kondisi</option>
        <option value="Baik">Baik</option>
        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
        <option value="Rusak">Rusak</option>
      </select>
    </div>

    <div class="form-group mb-0">
      <label for="filter-loanable">Status Boleh Dipinjam</label>
      <select id="filter-loanable" class="form-control">
        <option value="">Semua Status</option>
        <option value="Ya">Ya</option>
        <option value="Tidak">Tidak</option>
      </select>
    </div>
  </div>
  <div class="asset-filter-drawer__footer">
    <button type="button" id="reset-filter-drawer" class="btn btn-light">Reset</button>
    <button type="button" id="apply-filter-drawer" class="btn btn-primary">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    var drawer = $('#asset-filter-drawer');
    var overlay = $('#asset-filter-overlay');
    var activeFilterWrap = $('#asset-active-filters');
    var activeFilterChips = $('#asset-active-filter-chips');

    // Move drawer, overlay, and download modal to body to escape parent stacking contexts.
    if (drawer.parent()[0] !== document.body) {
      drawer.appendTo('body');
    }
    if (overlay.parent()[0] !== document.body) {
      overlay.appendTo('body');
    }
    var dlModal = $('#modal-download');
    if (dlModal.parent()[0] !== document.body) {
      dlModal.appendTo('body');
    }

    $('[data-target="#modal-download"]').on('click', function (e) {
      e.preventDefault();
      dlModal.modal('show');
    });

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

    function applyDrawerFilters(table) {
      applyExactFilter(table, 2, $('#filter-lab').val());
      applyExactFilter(table, 3, $('#filter-category').val());
      applyExactFilter(table, 4, $('#filter-loanable').val());
      applyExactFilter(table, 5, $('#filter-condition').val());
      table.draw();
      renderActiveFilterChips();
    }

    function renderActiveFilterChips() {
      var filters = [
        { key: 'lab', label: 'Lab', value: $('#filter-lab').val() },
        { key: 'category', label: 'Kategori', value: $('#filter-category').val() },
        { key: 'condition', label: 'Kondisi', value: $('#filter-condition').val() },
        { key: 'loanable', label: 'Boleh Dipinjam', value: $('#filter-loanable').val() }
      ].filter(function (item) {
        return item.value;
      });

      activeFilterChips.empty();

      if (!filters.length) {
        activeFilterWrap.removeClass('is-visible');
        return;
      }

      filters.forEach(function (filter) {
        var chip = $('<span class="asset-filter-chip"></span>');
        chip.append(document.createTextNode(filter.label + ': ' + filter.value));

        var removeBtn = $('<button type="button" class="asset-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
        removeBtn.attr('data-filter-key', filter.key);
        chip.append(removeBtn);
        activeFilterChips.append(chip);
      });

      activeFilterWrap.addClass('is-visible');
    }

    var tableAssets = $('#table-assets').DataTable({
      pageLength: 10,
      order: [[1, 'asc']],
      columnDefs: [
        { targets: [0, 9], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data aset.',
        zeroRecords: 'Data tidak ditemukan',
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

    $('#close-filter-drawer, #asset-filter-overlay').on('click', function () {
      setDrawerState(false);
    });

    $('#apply-filter-drawer').on('click', function () {
      applyDrawerFilters(tableAssets);
      setDrawerState(false);
    });

    $('#reset-filter-drawer').on('click', function () {
      $('#filter-lab').val('');
      $('#filter-category').val('');
      $('#filter-condition').val('');
      $('#filter-loanable').val('');
      applyDrawerFilters(tableAssets);
    });

    activeFilterChips.on('click', '.asset-filter-chip__remove', function () {
      var key = $(this).attr('data-filter-key');
      if (key === 'lab') {
        $('#filter-lab').val('');
      } else if (key === 'category') {
        $('#filter-category').val('');
      } else if (key === 'condition') {
        $('#filter-condition').val('');
      } else if (key === 'loanable') {
        $('#filter-loanable').val('');
      }

      applyDrawerFilters(tableAssets);
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
