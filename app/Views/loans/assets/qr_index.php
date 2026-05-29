<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php
$assets      = $assets      ?? [];
$labs        = $labs        ?? [];
$filterLabId = $filterLabId ?? 0;
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">QR Code Alat / Peralatan</h4>
        <div class="d-flex gap-2 flex-wrap">
          <button id="btn-select-all" type="button" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-check-square"></i> Pilih Semua
          </button>
          <button id="btn-bulk-print" type="button" class="btn btn-sm btn-success" disabled>
            <i class="fas fa-print"></i> Cetak QR Terpilih
          </button>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          QR Code dapat ditempel pada peralatan untuk memudahkan identifikasi aset.
          Pilih satu atau beberapa alat lalu klik <strong>Cetak QR Terpilih</strong> untuk mencetak sekaligus.
        </p>

        <!-- Filter by Lab -->
        <form method="get" class="form-inline mb-3" id="form-filter">
          <label class="mr-2 font-weight-600" for="lab_id">Filter Lab:</label>
          <select name="lab_id" id="lab_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="0">— Semua Lab —</option>
            <?php foreach ($labs as $lab): ?>
              <option value="<?= (int) $lab['id'] ?>" <?= $filterLabId === (int) $lab['id'] ? 'selected' : '' ?>>
                <?= esc($lab['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($filterLabId > 0): ?>
            <a href="<?= base_url('admin/loans/assets/qr') ?>" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-times"></i> Reset
            </a>
          <?php endif; ?>
        </form>

        <?php if (session()->has('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
          </div>
        <?php endif; ?>

        <div class="table-responsive">
          <table id="table-qr" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th style="width:40px;"><input type="checkbox" id="check-all" title="Pilih semua"></th>
                <th>Nama Alat</th>
                <th>Kode Aset</th>
                <th>Merek / Model</th>
                <th>Lab</th>
                <th style="width:120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($assets as $asset): ?>
                <tr>
                  <td class="text-center">
                    <input type="checkbox" class="asset-check" value="<?= (int) $asset['id'] ?>">
                  </td>
                  <td><?= esc($asset['name']) ?></td>
                  <td><code><?= esc($asset['asset_code'] ?? '-') ?></code></td>
                  <td><?= esc(trim(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? ''))) ?: '-' ?></td>
                  <td><?= esc($asset['lab_name'] ?? '-') ?></td>
                  <td>
                    <a href="<?= base_url('admin/loans/assets/' . (int) $asset['id'] . '/qr') ?>"
                       target="_blank"
                       class="btn btn-sm btn-primary"
                       title="Lihat & Cetak QR Satuan">
                      <i class="fas fa-qrcode"></i> Cetak
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    var table = $('#table-qr').DataTable({
      pageLength: 25,
      order: [[1, 'asc']],
      columnDefs: [
        { targets: [0, 5], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data alat.', zeroRecords: 'Data tidak ditemukan',
        paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
      }
    });

    // Select-all (header checkbox)
    $('#check-all').on('change', function () {
      var checked = this.checked;
      table.rows({ search: 'applied' }).nodes().to$().find('.asset-check').prop('checked', checked);
      updateBulkBtn();
    });

    // Per-row checkbox
    $('#table-qr tbody').on('change', '.asset-check', function () {
      updateBulkBtn();
      var total   = table.rows({ search: 'applied' }).nodes().to$().find('.asset-check').length;
      var checked = table.rows({ search: 'applied' }).nodes().to$().find('.asset-check:checked').length;
      $('#check-all').prop('indeterminate', checked > 0 && checked < total);
      $('#check-all').prop('checked', checked === total);
    });

    // Select-all button
    $('#btn-select-all').on('click', function () {
      var allChecked = $('.asset-check:checked').length === $('.asset-check').length;
      $('.asset-check').prop('checked', !allChecked);
      $('#check-all').prop('checked', !allChecked);
      updateBulkBtn();
    });

    function updateBulkBtn() {
      var count = $('.asset-check:checked').length;
      var $btn  = $('#btn-bulk-print');
      $btn.prop('disabled', count === 0);
      $btn.text(count > 0 ? 'Cetak QR Terpilih (' + count + ')' : 'Cetak QR Terpilih');
      // re-add icon
      $btn.prepend('<i class="fas fa-print"></i> ');
    }

    // Bulk print
    $('#btn-bulk-print').on('click', function () {
      var ids = [];
      $('.asset-check:checked').each(function () {
        ids.push($(this).val());
      });
      if (ids.length === 0) return;

      var qs  = ids.map(function (id) { return 'ids[]=' + encodeURIComponent(id); }).join('&');
      var url = '<?= base_url('admin/loans/assets/qr/bulk') ?>?' + qs;
      window.open(url, '_blank');
    });
  });
</script>
<?= $this->endSection() ?>
