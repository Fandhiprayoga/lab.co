<?php
/** @var array $assets */
/** @var array $types */
/** @var array $statuses */
/** @var array|null $maintenance */
/** @var int $assetId */
$mode        = ($mode ?? 'create');
$isEdit      = $mode === 'edit';
$maintenance = $maintenance ?? [];
$assets      = $assets ?? [];
$types       = $types ?? [];
$statuses    = $statuses ?? [];
$assetId     = (int) ($assetId ?? ($maintenance['asset_id'] ?? 0));
$formAction  = $isEdit
    ? base_url('admin/loans/maintenances/update/' . (int) ($maintenance['id'] ?? 0))
    : base_url('admin/loans/maintenances/store');
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4><?= $isEdit ? 'Edit Perawatan Aset' : 'Catat Perawatan Aset' ?></h4>
      </div>
      <div class="card-body">
        <form action="<?= $formAction ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="asset_id">Aset</label>
            <?php $selAsset = (string) old('asset_id', (string) ($maintenance['asset_id'] ?? $assetId)); ?>
            <select id="asset_id" name="asset_id" class="form-control" required>
              <option value="">- Pilih Aset -</option>
              <?php foreach ($assets as $a): ?>
                <option value="<?= (int) $a['id'] ?>" <?= $selAsset === (string) $a['id'] ? 'selected' : '' ?>>
                  <?= esc($a['name']) ?> <?= ! empty($a['asset_code']) ? '(' . esc($a['asset_code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="maintenance_type">Tipe Perawatan</label>
              <?php $selType = (string) old('maintenance_type', (string) ($maintenance['maintenance_type'] ?? 'corrective')); ?>
              <select id="maintenance_type" name="maintenance_type" class="form-control" required>
                <?php foreach ($types as $t): ?>
                  <option value="<?= esc($t) ?>" <?= $selType === $t ? 'selected' : '' ?>><?= esc(ucfirst($t)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="status">Status</label>
              <?php $selStatus = (string) old('status', (string) ($maintenance['status'] ?? 'scheduled')); ?>
              <select id="status" name="status" class="form-control" required>
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= esc($s) ?>" <?= $selStatus === $s ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', ucfirst($s))) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Status <b>in_progress</b> otomatis menonaktifkan peminjaman aset.</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="scheduled_date">Tanggal Dijadwalkan</label>
              <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" value="<?= old('scheduled_date', $maintenance['scheduled_date'] ?? '') ?>">
            </div>
            <div class="form-group col-md-4">
              <label for="performed_date">Tanggal Dikerjakan</label>
              <input type="date" id="performed_date" name="performed_date" class="form-control" value="<?= old('performed_date', $maintenance['performed_date'] ?? '') ?>">
            </div>
            <div class="form-group col-md-4">
              <label for="next_maintenance_date">Jadwal Berikutnya</label>
              <input type="date" id="next_maintenance_date" name="next_maintenance_date" class="form-control" value="<?= old('next_maintenance_date', $maintenance['next_maintenance_date'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="performed_by">Pelaksana / Vendor</label>
              <input type="text" id="performed_by" name="performed_by" class="form-control" value="<?= old('performed_by', $maintenance['performed_by'] ?? '') ?>" maxlength="150">
            </div>
            <div class="form-group col-md-6">
              <label for="cost">Biaya (Rp)</label>
              <input type="number" step="0.01" min="0" id="cost" name="cost" class="form-control" value="<?= old('cost', $maintenance['cost'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="description">Deskripsi Pekerjaan</label>
            <textarea id="description" name="description" class="form-control" rows="3" required maxlength="5000"><?= old('description', $maintenance['description'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="result_notes">Catatan Hasil</label>
            <textarea id="result_notes" name="result_notes" class="form-control" rows="3" maxlength="5000"><?= old('result_notes', $maintenance['result_notes'] ?? '') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/maintenances' . ($assetId ? '?asset_id=' . $assetId : '')) ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
