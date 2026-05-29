<?php $rows = $rows ?? []; ?>
<?php $validCount = $validCount ?? 0; ?>
<?php $errorCount = $errorCount ?? 0; ?>
<?php $totalCount = $totalCount ?? 0; ?>

<!-- Summary stats -->
<div class="row mb-3">
  <div class="col-md-4">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-list"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Baris</h4></div>
        <div class="card-body"><?= $totalCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Baris Valid</h4></div>
        <div class="card-body"><?= $validCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-times"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Baris Error</h4></div>
        <div class="card-body"><?= $errorCount ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h4>Preview Data Import</h4>
    <div class="card-header-action">
      <a href="<?= base_url('admin/loans/assets/import') ?>" class="btn btn-sm btn-outline-secondary mr-2">
        <i class="fas fa-redo mr-1"></i> Upload Ulang
      </a>
      <?php if ($validCount > 0): ?>
      <form action="<?= base_url('admin/loans/assets/import/process') ?>" method="post" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="fas fa-upload mr-1"></i> Proses Import (<?= $validCount ?> baris valid)
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body p-0">

    <?php if ($validCount === 0): ?>
      <div class="alert alert-danger mx-3 mt-3 mb-3">
        <i class="fas fa-times-circle mr-1"></i>
        <strong>Tidak ada baris yang valid.</strong> Semua baris mengandung error. Perbaiki file CSV dan upload ulang.
      </div>
    <?php elseif ($errorCount > 0): ?>
      <div class="alert alert-warning mx-3 mt-3 mb-0">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong><?= $errorCount ?> baris error</strong> akan dilewati. Hanya <strong><?= $validCount ?> baris valid</strong> yang akan diimport.
      </div>
    <?php else: ?>
      <div class="alert alert-success mx-3 mt-3 mb-0">
        <i class="fas fa-check-circle mr-1"></i>
        Semua <strong><?= $validCount ?> baris</strong> valid dan siap diimport.
      </div>
    <?php endif; ?>

    <div class="table-responsive mt-3">
      <table class="table table-bordered table-sm mb-0" style="font-size: 0.82rem;">
        <thead class="thead-light">
          <tr>
            <th class="text-center" style="width:55px;">Baris</th>
            <th class="text-center" style="width:80px;">Status</th>
            <th>Nama Alat</th>
            <th>Lab</th>
            <th>Kategori</th>
            <th>Kondisi</th>
            <th class="text-center">Stok Total</th>
            <th class="text-center">Stok Tersedia</th>
            <th>Kode Aset</th>
            <th>Merk / Model</th>
            <th>Alasan Error</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr class="<?= $row['valid'] ? 'table-success' : 'table-danger' ?>">
            <td class="text-center"><?= $row['row_num'] ?></td>
            <td class="text-center">
              <?php if ($row['valid']): ?>
                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Valid</span>
              <?php else: ?>
                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Error</span>
              <?php endif; ?>
            </td>
            <td><?= $row['name'] !== '' ? esc($row['name']) : '<em class="text-muted">—</em>' ?></td>
            <td><?= $row['lab_name'] !== '' ? esc($row['lab_name']) : '<em class="text-muted">—</em>' ?></td>
            <td><?= $row['category'] !== '' ? esc($row['category']) : '<em class="text-muted">—</em>' ?></td>
            <td><?= $row['condition_status'] !== '' ? esc($row['condition_status']) : '<em class="text-muted">—</em>' ?></td>
            <td class="text-center"><?= (int) $row['stock_total'] ?></td>
            <td class="text-center"><?= (int) $row['stock_available'] ?></td>
            <td>
              <?php if ($row['asset_code'] !== ''): ?>
                <code><?= esc($row['asset_code']) ?></code>
              <?php else: ?>
                <em class="text-muted">auto</em>
              <?php endif; ?>
            </td>
            <td>
              <?php $bm = trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? '')); ?>
              <?= $bm !== '' ? esc($bm) : '<em class="text-muted">—</em>' ?>
            </td>
            <td>
              <?php if (! $row['valid']): ?>
                <ul class="mb-0 pl-3">
                  <?php foreach ($row['errors'] as $err): ?>
                    <li class="text-danger"><?= esc($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <span class="text-success"><i class="fas fa-check"></i></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($validCount > 0): ?>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <a href="<?= base_url('admin/loans/assets/import') ?>" class="btn btn-outline-secondary">
      <i class="fas fa-redo mr-1"></i> Upload Ulang
    </a>
    <form action="<?= base_url('admin/loans/assets/import/process') ?>" method="post">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-upload mr-1"></i> Proses Import (<?= $validCount ?> baris valid)
      </button>
    </form>
  </div>
  <?php else: ?>
  <div class="card-footer">
    <a href="<?= base_url('admin/loans/assets/import') ?>" class="btn btn-outline-secondary">
      <i class="fas fa-redo mr-1"></i> Upload Ulang
    </a>
  </div>
  <?php endif; ?>
</div>
