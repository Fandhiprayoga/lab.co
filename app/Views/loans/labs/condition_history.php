<?php
$conditionBadge = function (string $c): string {
    if ($c === 'baik') return '<span class="badge badge-success">Baik</span>';
    if ($c === 'perlu_perbaikan') return '<span class="badge badge-warning">Perlu Perbaikan</span>';
    if ($c === 'rusak') return '<span class="badge badge-danger">Rusak</span>';
    return '<span class="badge badge-secondary">-</span>';
};
?>
<?php $histories = $histories ?? []; ?>
<?php $lab = $lab ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Riwayat Kondisi - <?= esc($lab['name'] ?? '-') ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/labs/condition-history') ?>" class="btn btn-light"><i class="fas fa-list"></i> Semua Lab</a>
          <a href="<?= base_url('admin/loans/labs/edit/' . (int) ($lab['id'] ?? 0)) ?>" class="btn btn-info"><i class="fas fa-edit"></i> Edit Lab</a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($histories)): ?>
          <div class="alert alert-info mb-0">Belum ada riwayat perubahan kondisi untuk lab ini.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Dari</th>
                  <th>Menjadi</th>
                  <th>Alasan</th>
                  <th>Diubah Oleh</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($histories as $h): ?>
                  <tr>
                    <td><?= esc($h['created_at'] ?? '-') ?></td>
                    <td><?= $conditionBadge((string) ($h['previous_condition'] ?? '')) ?></td>
                    <td><?= $conditionBadge((string) ($h['new_condition'] ?? '')) ?></td>
                    <td><?= esc($h['reason'] ?? '-') ?></td>
                    <td><?= esc($h['changed_by_name'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
