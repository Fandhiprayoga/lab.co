<?php $assets = $assets ?? []; ?>
<?php $labs = $labs ?? []; ?>
<?php $types = $types ?? []; ?>
<?php $assetId = (int) ($assetId ?? 0); ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Catat Mutasi Aset</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/movements/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="asset_id">Aset</label>
            <?php $selAsset = (string) old('asset_id', (string) $assetId); ?>
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
              <input type="number" id="quantity" name="quantity" class="form-control" value="<?= old('quantity', '1') ?>" required>
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
