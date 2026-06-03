<?php $assets = $assets ?? []; ?>
<?php $items = $items ?? []; ?>
<?php $labs = $labs ?? []; ?>
<?php $types = $types ?? []; ?>
<?php $assetId = (int) ($assetId ?? 0); ?>
<?php $prefillLabId = (int) ($prefillLabId ?? 0); ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<?= $this->endSection() ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Catat Mutasi Item Alat</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/movements/store') ?>" method="post">
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
            <?php $selAsset = (string) old('asset_id', (string) $assetId); ?>
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
            <?php $selItem = (string) old('asset_item_id', ''); ?>
            <select id="asset_item_id" name="asset_item_id" class="form-control select2" required>
              <option value="">- Pilih Item -</option>
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
            <div class="form-group col-md-4">
              <label for="movement_type">Tipe Mutasi</label>
              <?php $selType = (string) old('movement_type', 'in'); ?>
              <select id="movement_type" name="movement_type" class="form-control" required>
                <?php foreach ($types as $t): ?>
                  <option value="<?= esc($t) ?>" <?= $selType === $t ? 'selected' : '' ?>><?= esc(ucfirst($t)) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">In/return/adjustment menambah stok; out/borrow/disposal mengurangi.</small>
            </div>
            <div class="form-group col-md-4">
              <label for="quantity">Jumlah</label>
              <input type="number" id="quantity" name="quantity" class="form-control" value="1" readonly>
            </div>
            <div class="form-group col-md-4">
              <label for="movement_date">Tanggal Mutasi</label>
              <input type="datetime-local" id="movement_date" name="movement_date" class="form-control" value="<?= old('movement_date', date('Y-m-d\TH:i')) ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="from_lab_id">Dari Lab</label>
              <?php $selFrom = (string) old('from_lab_id', ''); ?>
              <select id="from_lab_id" name="from_lab_id" class="form-control">
                <option value="">-</option>
                <?php foreach ($labs as $lab): ?>
                  <option value="<?= (int) $lab['id'] ?>" <?= $selFrom === (string) $lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="to_lab_id">Ke Lab</label>
              <?php $selTo = (string) old('to_lab_id', ''); ?>
              <select id="to_lab_id" name="to_lab_id" class="form-control">
                <option value="">-</option>
                <?php foreach ($labs as $lab): ?>
                  <option value="<?= (int) $lab['id'] ?>" <?= $selTo === (string) $lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="reference_type">Tipe Referensi</label>
              <input type="text" id="reference_type" name="reference_type" class="form-control" value="<?= old('reference_type', 'manual') ?>" maxlength="50">
            </div>
            <div class="form-group col-md-6">
              <label for="reference_id">ID Referensi</label>
              <input type="number" min="1" id="reference_id" name="reference_id" class="form-control" value="<?= old('reference_id', '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Catatan</label>
            <textarea id="notes" name="notes" class="form-control" rows="3" maxlength="2000"><?= old('notes', '') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/movements' . ($assetId ? '?asset_id=' . $assetId : '')) ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
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
    if (!$.fn.select2) {
      return;
    }

    var labSelect = $('#lab_filter_id');
    var assetSelect = $('#asset_id');
    var itemSelect = $('#asset_item_id');
    var initialLab = <?= json_encode((string) old('lab_filter_id', (string) $prefillLabId)) ?>;
    var initialAsset = <?= json_encode((string) old('asset_id', (string) $assetId)) ?>;
    var initialItem = <?= json_encode((string) old('asset_item_id', '')) ?>;

    var assetsData = assetSelect.find('option').map(function () {
      var opt = $(this);
      return {
        value: String(opt.val() || ''),
        text: opt.text(),
        labId: String(opt.data('lab-id') || ''),
      };
    }).get();

    var itemsData = itemSelect.find('option').map(function () {
      var opt = $(this);
      return {
        value: String(opt.val() || ''),
        text: opt.text(),
        labId: String(opt.data('lab-id') || ''),
        assetId: String(opt.data('asset-id') || ''),
      };
    }).get();

    function initSelect2() {
      labSelect.select2({
        placeholder: '- Pilih Lab -',
        allowClear: true,
        width: '100%'
      });
      assetSelect.select2({
        placeholder: '- Pilih Aset -',
        allowClear: true,
        width: '100%',
        language: {
          noResults: function () { return 'Data aset tidak ditemukan'; }
        }
      });
      itemSelect.select2({
        placeholder: '- Pilih Item -',
        allowClear: true,
        width: '100%',
        language: {
          noResults: function () { return 'Data item tidak ditemukan'; }
        }
      });
    }

    function rebuildAssetOptions(labId, selectedValue) {
      assetSelect.empty().append('<option value="">- Pilih Aset -</option>');
      assetsData.forEach(function (row) {
        if (row.value === '') {
          return;
        }
        if (labId !== '' && row.labId !== String(labId)) {
          return;
        }
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
      itemSelect.empty().append('<option value="">- Pilih Item -</option>');
      itemsData.forEach(function (row) {
        if (row.value === '') {
          return;
        }
        if (labId !== '' && row.labId !== String(labId)) {
          return;
        }
        if (assetId !== '' && row.assetId !== String(assetId)) {
          return;
        }
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

    initSelect2();

    labSelect.val(initialLab).trigger('change.select2');
    rebuildAssetOptions(String(initialLab || ''), String(initialAsset || ''));
    rebuildItemOptions(String(initialLab || ''), String(assetSelect.val() || ''), String(initialItem || ''));

    labSelect.on('change', function () {
      var labId = String(labSelect.val() || '');
      rebuildAssetOptions(labId, '');
      rebuildItemOptions(labId, '', '');
    });

    assetSelect.on('change', function () {
      var labId = String(labSelect.val() || '');
      var assetId = String(assetSelect.val() || '');
      rebuildItemOptions(labId, assetId, '');
    });
  });
</script>
<?= $this->endSection() ?>
