<?php $successRows = $successRows ?? []; ?>
<?php $errorRows   = $errorRows   ?? []; ?>
<?php $successCount = $successCount ?? 0; ?>
<?php $errorCount   = $errorCount   ?? 0; ?>

<!-- Summary stats -->
<div class="row mb-3">
  <div class="col-md-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Berhasil Diimport</h4></div>
        <div class="card-body"><?= $successCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-times"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Baris Dilewati (Error)</h4></div>
        <div class="card-body"><?= $errorCount ?></div>
      </div>
    </div>
  </div>
</div>

<?php if ($successCount > 0): ?>
<div class="alert alert-success">
  <i class="fas fa-check-circle mr-1"></i>
  <strong><?= $successCount ?> data</strong> berhasil diimport ke master aset.
</div>
<?php endif; ?>

<?php if ($errorCount > 0): ?>
<div class="card mb-3">
  <div class="card-header">
    <h4 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Detail Baris Error (<?= $errorCount ?>)</h4>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" style="font-size: 0.83rem;">
        <thead class="thead-light">
          <tr>
            <th class="text-center" style="width:60px;">Baris CSV</th>
            <th>Nama Alat</th>
            <th>Lab</th>
            <th>Kategori</th>
            <th>Alasan Error</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($errorRows as $row): ?>
          <tr class="table-danger">
            <td class="text-center"><?= $row['row_num'] ?></td>
            <td><?= $row['name'] !== '' ? esc($row['name']) : '<em class="text-muted">—</em>' ?></td>
            <td><?= esc($row['lab_name']) ?></td>
            <td><?= esc($row['category']) ?></td>
            <td>
              <ul class="mb-0 pl-3">
                <?php foreach ($row['errors'] as $err): ?>
                  <li class="text-danger"><?= esc($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (! empty($successRows)): ?>
<div class="card mb-3">
  <div class="card-header">
    <h4 class="text-success"><i class="fas fa-check-circle mr-2"></i>Data Berhasil Diimport (<?= $successCount ?>)</h4>
    <div class="card-header-action">
      <button class="btn btn-sm btn-outline-secondary" id="toggle-success-table">
        <i class="fas fa-eye mr-1"></i> Tampilkan / Sembunyikan
      </button>
    </div>
  </div>
  <div id="success-table-wrap" class="card-body p-0" style="display:none;">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" style="font-size: 0.83rem;">
        <thead class="thead-light">
          <tr>
            <th class="text-center">Baris CSV</th>
            <th>Kode Aset</th>
            <th>Nama Alat</th>
            <th>Lab</th>
            <th>Kategori</th>
            <th>Kondisi</th>
            <th class="text-center">Stok</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($successRows as $row): ?>
          <tr class="table-success">
            <td class="text-center"><?= $row['row_num'] ?></td>
            <td><code><?= esc($row['asset_code']) ?></code></td>
            <td><?= esc($row['name']) ?></td>
            <td><?= esc($row['lab_name']) ?></td>
            <td><?= esc($row['resolved_category'] ?? $row['category']) ?></td>
            <td><?= esc($row['condition_status']) ?></td>
            <td class="text-center"><?= (int) $row['stock_available'] ?>/<?= (int) $row['stock_total'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between">
  <a href="<?= base_url('admin/loans/assets/import') ?>" class="btn btn-outline-secondary">
    <i class="fas fa-file-import mr-1"></i> Import Lagi
  </a>
  <a href="<?= base_url('admin/loans/assets') ?>" class="btn btn-primary">
    <i class="fas fa-list mr-1"></i> Kembali ke Daftar Alat
  </a>
</div>

<?= $this->section('js') ?>
<script>
  document.getElementById('toggle-success-table')?.addEventListener('click', function () {
    var wrap = document.getElementById('success-table-wrap');
    wrap.style.display = wrap.style.display === 'none' ? '' : 'none';
  });
</script>
<?= $this->endSection() ?>
