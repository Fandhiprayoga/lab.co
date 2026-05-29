<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $movements = $movements ?? []; ?>
<?php $asset = $asset ?? null; ?>
<?php $assetId = (int) ($assetId ?? 0); ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>
          <?php if ($asset): ?>
            Mutasi: <?= esc($asset['name']) ?> <small class="text-muted">(<?= esc($asset['asset_code'] ?? '-') ?>)</small>
          <?php else: ?>
            Riwayat Mutasi Aset
          <?php endif; ?>
        </h4>
        <div class="card-header-action">
          <?php if ($asset): ?>
            <a href="<?= base_url('admin/loans/movements') ?>" class="btn btn-light btn-sm">
              <i class="fas fa-list"></i> Semua Mutasi
            </a>
          <?php endif; ?>
          <a href="<?= base_url('admin/loans/movements/create' . ($assetId ? '?asset_id=' . $assetId : '')) ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Mutasi
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-movements" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Aset</th>
                <th>Tipe</th>
                <th>Qty</th>
                <th>Dari Lab</th>
                <th>Ke Lab</th>
                <th>Referensi</th>
                <th>Catatan</th>
                <th>Oleh</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($movements as $m): ?>
                <?php
                  $type = (string) ($m['movement_type'] ?? '');
                  $badgeMap = [
                      'in' => 'badge-success', 'out' => 'badge-warning', 'transfer' => 'badge-info',
                      'borrow' => 'badge-primary', 'return' => 'badge-success',
                      'adjustment' => 'badge-secondary', 'disposal' => 'badge-dark',
                  ];
                  $badge = $badgeMap[$type] ?? 'badge-secondary';
                ?>
                <tr>
                  <td><?= esc($m['movement_date']) ?></td>
                  <td>
                    <?= esc($m['asset_name'] ?? '-') ?>
                    <?php if (! empty($m['asset_code'])): ?>
                      <br><small class="text-muted"><code><?= esc($m['asset_code']) ?></code></small>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge <?= $badge ?>"><?= esc(ucfirst($type)) ?></span></td>
                  <td><?= (int) $m['quantity'] ?></td>
                  <td><?= esc($m['from_lab_name'] ?? '-') ?></td>
                  <td><?= esc($m['to_lab_name'] ?? '-') ?></td>
                  <td>
                    <?php if (! empty($m['reference_type'])): ?>
                      <code><?= esc($m['reference_type']) ?><?= ! empty($m['reference_id']) ? '#' . (int) $m['reference_id'] : '' ?></code>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                  <td><?= esc($m['notes'] ?? '-') ?></td>
                  <td><?= esc($m['created_by_name'] ?? '-') ?></td>
                  <td>
                    <form action="<?= base_url('admin/loans/movements/delete/' . (int) $m['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus mutasi?"
                          data-swal-text="Catatan mutasi akan dihapus permanen."
                          data-swal-confirm="Ya, hapus"
                          data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
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
    $('#table-movements').DataTable({
      pageLength: 25,
      order: [[0, 'desc']],
      columnDefs: [{ targets: [9], orderable: false, searchable: false }],
      language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_', paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' }, zeroRecords: 'Belum ada catatan mutasi.' }
    });
  });
</script>
<?= $this->endSection() ?>
