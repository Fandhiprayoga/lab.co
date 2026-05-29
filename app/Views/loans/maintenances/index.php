<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $maintenances = $maintenances ?? []; ?>
<?php $asset = $asset ?? null; ?>
<?php $assetId = (int) ($assetId ?? 0); ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>
          <?php if ($asset): ?>
            Perawatan: <?= esc($asset['name']) ?> <small class="text-muted">(<?= esc($asset['asset_code'] ?? '-') ?>)</small>
          <?php else: ?>
            Riwayat Perawatan Aset
          <?php endif; ?>
        </h4>
        <div class="card-header-action">
          <?php if ($asset): ?>
            <a href="<?= base_url('admin/loans/maintenances') ?>" class="btn btn-light btn-sm"><i class="fas fa-list"></i> Semua</a>
          <?php endif; ?>
          <a href="<?= base_url('admin/loans/maintenances/create' . ($assetId ? '?asset_id=' . $assetId : '')) ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Catat Perawatan
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-maintenances" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Aset</th>
                <th>Tipe</th>
                <th>Jadwal</th>
                <th>Dikerjakan</th>
                <th>Status</th>
                <th>Pelaksana</th>
                <th>Biaya</th>
                <th>Jadwal Berikutnya</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($maintenances as $m): ?>
                <?php
                  $statusBadge = [
                      'scheduled' => 'badge-secondary',
                      'in_progress' => 'badge-warning',
                      'completed' => 'badge-success',
                      'cancelled' => 'badge-dark',
                  ][$m['status']] ?? 'badge-secondary';
                ?>
                <tr>
                  <td>
                    <?= esc($m['asset_name'] ?? '-') ?>
                    <?php if (! empty($m['asset_code'])): ?>
                      <br><small class="text-muted"><code><?= esc($m['asset_code']) ?></code></small>
                    <?php endif; ?>
                  </td>
                  <td><?= esc(ucfirst($m['maintenance_type'])) ?></td>
                  <td><?= esc($m['scheduled_date'] ?? '-') ?></td>
                  <td><?= esc($m['performed_date'] ?? '-') ?></td>
                  <td><span class="badge <?= $statusBadge ?>"><?= esc(str_replace('_', ' ', ucfirst($m['status']))) ?></span></td>
                  <td><?= esc($m['performed_by'] ?? '-') ?></td>
                  <td><?= $m['cost'] !== null ? 'Rp ' . number_format((float) $m['cost'], 0, ',', '.') : '-' ?></td>
                  <td><?= esc($m['next_maintenance_date'] ?? '-') ?></td>
                  <td>
                    <a href="<?= base_url('admin/loans/maintenances/edit/' . (int) $m['id']) ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                    <form action="<?= base_url('admin/loans/maintenances/delete/' . (int) $m['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus perawatan?"
                          data-swal-text="Catatan perawatan akan dihapus permanen."
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
    $('#table-maintenances').DataTable({
      pageLength: 25,
      order: [[2, 'desc']],
      columnDefs: [{ targets: [8], orderable: false, searchable: false }],
      language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_', paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' }, zeroRecords: 'Belum ada catatan perawatan.' }
    });
  });
</script>
<?= $this->endSection() ?>
