<?php $assets = $assets ?? []; ?>
<?php $labs = $labs ?? []; ?>
<?php $selectedAssetId = (int) ($selectedAssetId ?? 0); ?>
<?php $inventoryStatuses = $inventoryStatuses ?? ['aktif', 'dipinjam', 'dalam_perbaikan', 'dihapuskan', 'hilang']; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Tambah Item Alat</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/asset-items/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="asset_id">Master Alat</label>
            <select id="asset_id" name="asset_id" class="form-control" required>
              <option value="">- Pilih Master Alat -</option>
              <?php foreach ($assets as $asset): ?>
                <?php $assetId = (int) ($asset['id'] ?? 0); ?>
                <option value="<?= $assetId ?>" <?= (int) old('asset_id', $selectedAssetId) === $assetId ? 'selected' : '' ?>>
                  <?= esc(($asset['asset_code'] ?? '-') . ' - ' . ($asset['name'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="item_code">Item Code</label>
              <input type="text" id="item_code" name="item_code" class="form-control" maxlength="80" value="<?= old('item_code') ?>" placeholder="Contoh: AST-001-0001">
              <small class="form-text text-muted">Kosongkan untuk auto-generate berdasarkan master alat.</small>
            </div>
            <div class="form-group col-md-6">
              <label for="serial_number">Serial Number</label>
              <input type="text" id="serial_number" name="serial_number" class="form-control" maxlength="100" value="<?= old('serial_number') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="lab_id">Lokasi Lab</label>
              <select id="lab_id" name="lab_id" class="form-control">
                <option value="">- Ikuti master alat -</option>
                <?php foreach ($labs as $lab): ?>
                  <option value="<?= (int) $lab['id'] ?>" <?= old('lab_id') == (string) $lab['id'] ? 'selected' : '' ?>>
                    <?= esc($lab['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="location_detail">Detail Lokasi</label>
              <input type="text" id="location_detail" name="location_detail" class="form-control" maxlength="150" value="<?= old('location_detail') ?>" placeholder="Rak A1, Lemari 2, dll">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="condition_status">Kondisi</label>
              <?php $selectedCondition = old('condition_status', 'baik'); ?>
              <select id="condition_status" name="condition_status" class="form-control" required>
                <option value="baik" <?= $selectedCondition === 'baik' ? 'selected' : '' ?>>Baik</option>
                <option value="perlu_perbaikan" <?= $selectedCondition === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                <option value="rusak" <?= $selectedCondition === 'rusak' ? 'selected' : '' ?>>Rusak</option>
                <option value="rusak_ringan" <?= $selectedCondition === 'rusak_ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                <option value="rusak_berat" <?= $selectedCondition === 'rusak_berat' ? 'selected' : '' ?>>Rusak Berat</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="inventory_status">Status Inventaris</label>
              <?php $selectedInv = old('inventory_status', 'aktif'); ?>
              <select id="inventory_status" name="inventory_status" class="form-control" required>
                <?php foreach ($inventoryStatuses as $st): ?>
                  <option value="<?= esc($st) ?>" <?= $selectedInv === $st ? 'selected' : '' ?>>
                    <?= esc(str_replace('_', ' ', ucfirst($st))) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_loanable" name="is_loanable" value="1" <?= old('is_loanable', '1') ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_loanable">Boleh dipinjam</label>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="acquisition_date">Tanggal Pengadaan</label>
              <input type="date" id="acquisition_date" name="acquisition_date" class="form-control" value="<?= old('acquisition_date') ?>">
            </div>
            <div class="form-group col-md-6">
              <label for="warranty_until">Garansi Sampai</label>
              <input type="date" id="warranty_until" name="warranty_until" class="form-control" value="<?= old('warranty_until') ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="responsible_user_id">ID Penanggung Jawab (Opsional)</label>
            <input type="number" min="1" id="responsible_user_id" name="responsible_user_id" class="form-control" value="<?= old('responsible_user_id') ?>">
          </div>

          <div class="form-group">
            <label for="notes">Catatan</label>
            <textarea id="notes" name="notes" class="form-control" rows="3" maxlength="2000"><?= old('notes') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/asset-items' . ($selectedAssetId > 0 ? '?asset_id=' . $selectedAssetId : '')) ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
