<?php
/** @var array $assets */
/** @var array $items */
/** @var array $labs */
/** @var array $types */
/** @var array $statuses */
/** @var array|null $maintenance */
/** @var int $assetId */
/** @var int $prefillLabId */
$mode        = ($mode ?? 'create');
$isEdit      = $mode === 'edit';
$maintenance = $maintenance ?? [];
$assets      = $assets ?? [];
$items       = $items ?? [];
$labs        = $labs ?? [];
$types       = $types ?? [];
$statuses    = $statuses ?? [];
$assetId     = (int) ($assetId ?? ($maintenance['asset_id'] ?? 0));
$prefillLabId = (int) ($prefillLabId ?? 0);
$editItemId  = (int) ($maintenance['asset_item_id'] ?? 0);
$formAction  = $isEdit
    ? base_url('admin/loans/maintenances/update/' . (int) ($maintenance['id'] ?? 0))
    : base_url('admin/loans/maintenances/store');
?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<?= $this->endSection() ?>

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
            <label for="lab_filter_id">Lab</label>
            <?php $selLab = (string) old('lab_filter_id', (string) $prefillLabId); ?>
            <select id="lab_filter_id" name="lab_filter_id" class="form-control select2" required>
              <option value="">- Pilih Lab -</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= (int) $lab['id'] ?>" <?= $selLab === (string) $lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="asset_id">Aset</label>
            <?php $selAsset = (string) old('asset_id', (string) ($maintenance['asset_id'] ?? $assetId)); ?>
            <select id="asset_id" name="asset_id" class="form-control select2" required>
              <option value="">- Pilih Aset -</option>
              <?php foreach ($assets as $a): ?>
                <option value="<?= (int) $a['id'] ?>"
                        data-lab-id="<?= (int) ($a['lab_id'] ?? 0) ?>"
                        <?= $selAsset === (string) $a['id'] ? 'selected' : '' ?>>
                  <?= esc($a['name']) ?> <?= ! empty($a['asset_code']) ? '(' . esc($a['asset_code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="asset_item_id">Item</label>
            <?php $selItem = (string) old('asset_item_id', (string) $editItemId); ?>
            <select id="asset_item_id" name="asset_item_id" class="form-control select2">
              <option value="">- Pilih Item (opsional) -</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int) $it['id'] ?>"
                        data-lab-id="<?= (int) ($it['lab_id'] ?? 0) ?>"
                        data-asset-id="<?= (int) ($it['asset_id'] ?? 0) ?>"
                        <?= $selItem === (string) $it['id'] ? 'selected' : '' ?>>
                  <?= esc($it['item_code'] ?? '-') ?> - <?= esc($it['asset_name'] ?? '-') ?> <?= ! empty($it['asset_code']) ? '(' . esc($it['asset_code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Urutan: pilih lab -> aset -> item.</small>
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
              <small class="form-text text-muted">Status <b>in_progress</b> otomatis menonaktifkan peminjaman di level item dan header aset.</small>
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

<?= $this->section('js') ?>
<script src="<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>"></script>
<script>
$(function () {
  if (!$.fn.select2) return;

  var labSelect  = $('#lab_filter_id');
  var assetSelect = $('#asset_id');
  var itemSelect = $('#asset_item_id');
  var initialLab  = <?= json_encode((string) old('lab_filter_id', (string) $prefillLabId)) ?>;
  var initialAsset = <?= json_encode((string) old('asset_id', (string) ($maintenance['asset_id'] ?? $assetId))) ?>;
  var initialItem  = <?= json_encode((string) old('asset_item_id', (string) $editItemId)) ?>;

  var assetsData = assetSelect.find('option').map(function () {
    var opt = $(this);
    return {
      value: String(opt.val() || ''),
      text:  opt.text(),
      labId: String(opt.data('lab-id') || ''),
    };
  }).get();

  var itemsData = itemSelect.find('option').map(function () {
    var opt = $(this);
    return {
      value:   String(opt.val() || ''),
      text:    opt.text(),
      labId:   String(opt.data('lab-id') || ''),
      assetId: String(opt.data('asset-id') || ''),
    };
  }).get();

  labSelect.select2({ placeholder: '- Pilih Lab -', allowClear: true, width: '100%' });
  assetSelect.select2({
    placeholder: '- Pilih Aset -',
    allowClear: true,
    width: '100%',
    language: { noResults: function () { return 'Data aset tidak ditemukan'; } }
  });
  itemSelect.select2({
    placeholder: '- Pilih Item (opsional) -',
    allowClear: true,
    width: '100%',
    language: { noResults: function () { return 'Data item tidak ditemukan'; } }
  });

  function rebuildAssetOptions(labId, selectedValue) {
    assetSelect.empty().append('<option value="">- Pilih Aset -</option>');
    assetsData.forEach(function (row) {
      if (row.value === '') return;
      if (labId !== '' && row.labId !== String(labId)) return;
      var opt = $('<option></option>').val(row.value).text(row.text).attr('data-lab-id', row.labId);
      if (selectedValue !== '' && row.value === String(selectedValue)) {
        opt.prop('selected', true);
      }
      assetSelect.append(opt);
    });
    if (selectedValue !== '' && assetSelect.val() !== String(selectedValue)) {
      assetSelect.val('');
    }
    assetSelect.trigger('change.select2');
  }

  function rebuildItemOptions(labId, assetId, selectedValue) {
    itemSelect.empty().append('<option value="">- Pilih Item (opsional) -</option>');
    itemsData.forEach(function (row) {
      if (row.value === '') return;
      if (labId !== '' && row.labId !== String(labId)) return;
      if (assetId !== '' && row.assetId !== String(assetId)) return;
      var opt = $('<option></option>')
        .val(row.value)
        .text(row.text)
        .attr('data-lab-id', row.labId)
        .attr('data-asset-id', row.assetId);
      if (selectedValue !== '' && row.value === String(selectedValue)) {
        opt.prop('selected', true);
      }
      itemSelect.append(opt);
    });
    if (selectedValue !== '' && itemSelect.val() !== String(selectedValue)) {
      itemSelect.val('');
    }
    itemSelect.trigger('change.select2');
  }

  labSelect.val(initialLab).trigger('change.select2');
  rebuildAssetOptions(String(initialLab || ''), String(initialAsset || ''));
  rebuildItemOptions(String(initialLab || ''), String(assetSelect.val() || ''), String(initialItem || ''));

  labSelect.on('change', function () {
    var labId = String(labSelect.val() || '');
    rebuildAssetOptions(labId, '');
    rebuildItemOptions(labId, '', '');
  });

  assetSelect.on('change', function () {
    var labId   = String(labSelect.val() || '');
    var assetId = String(assetSelect.val() || '');
    rebuildItemOptions(labId, assetId, '');
  });
});
</script>
<?= $this->endSection() ?>
