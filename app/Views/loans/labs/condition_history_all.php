<?php
$conditionBadge = function (string $c): string {
    if ($c === 'baik') return '<span class="badge badge-success">Baik</span>';
    if ($c === 'perlu_perbaikan') return '<span class="badge badge-warning">Perlu Perbaikan</span>';
    if ($c === 'rusak') return '<span class="badge badge-danger">Rusak</span>';
    return '<span class="badge badge-secondary">-</span>';
};
?>
<?php $histories = $histories ?? []; ?>
<?php $labs = $labs ?? []; ?>
<?php $filters = $filters ?? ['lab_id' => 0, 'date_from' => '', 'date_to' => '']; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Riwayat Perubahan Kondisi Lab</h4>
      </div>
      <div class="card-body">
        <form method="get" class="form-row mb-3">
          <div class="col-md-4 mb-2">
            <label class="small mb-1">Lab</label>
            <select name="lab_id" class="form-control">
              <option value="">Semua Lab</option>
              <?php foreach ($labs as $l): ?>
                <option value="<?= (int) $l['id'] ?>" <?= (int) $filters['lab_id'] === (int) $l['id'] ? 'selected' : '' ?>>
                  <?= esc($l['name']) ?><?= ! empty($l['code']) ? ' (' . esc($l['code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="small mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
          </div>
          <div class="col-md-3 mb-2">
            <label class="small mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
          </div>
          <div class="col-md-2 mb-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Terapkan</button>
            <a href="<?= base_url('admin/loans/labs/condition-history') ?>" class="btn btn-light">Reset</a>
          </div>
        </form>

        <?php if (empty($histories)): ?>
          <div class="alert alert-info mb-0">Tidak ada data riwayat sesuai filter.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Lab</th>
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
                    <td>
                      <a href="<?= base_url('admin/loans/labs/' . (int) $h['lab_id'] . '/condition-history') ?>">
                        <?= esc($h['lab_name'] ?? '-') ?>
                      </a>
                      <?php if (! empty($h['lab_code'])): ?>
                        <small class="text-muted">(<?= esc($h['lab_code']) ?>)</small>
                      <?php endif; ?>
                    </td>
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
