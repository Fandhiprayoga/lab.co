<?php $labs = $labs ?? []; ?>
<?php $categories = $categories ?? []; ?>
<?php $units = $units ?? []; ?>
<?php $acquisitionSources = $acquisitionSources ?? ['pembelian', 'hibah', 'pinjaman', 'produksi']; ?>
<?php $inventoryStatuses  = $inventoryStatuses ?? ['aktif', 'dipinjam', 'dalam_perbaikan', 'dihapuskan', 'hilang']; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Tambah Master Alat</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/assets/store') ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group text-center">
            <img src="<?= base_url('assets/img/stisla-fill.svg') ?>" alt="placeholder foto alat" class="img-thumbnail mb-2" style="width: 120px; height: 120px; object-fit: contain;">
            <small class="d-block text-muted">Placeholder default akan digunakan jika foto belum diupload.</small>
          </div>

          <div class="form-group">
            <label for="name">Nama Alat</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name') ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="asset_code">Kode Inventaris</label>
              <input type="text" id="asset_code" name="asset_code" class="form-control" value="<?= old('asset_code') ?>" maxlength="50" placeholder="Kosongkan untuk auto-generate">
              <small class="form-text text-muted">Format auto: <code>LAB{lab}-{KAT}-{YY}-{seq}</code>. Boleh override manual.</small>
            </div>
            <div class="form-group col-md-6">
              <label>Item Fisik</label>
              <div class="form-control bg-light text-muted" style="height:auto;">
                Serial number, kondisi, status inventaris, dan stok dikelola di menu Item Alat.
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="lab_id">Ditempatkan di Lab</label>
            <select id="lab_id" name="lab_id" class="form-control" required>
              <option value="">- Pilih Lab -</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= (int) $lab['id'] ?>" <?= old('lab_id') == (string) $lab['id'] ? 'selected' : '' ?>>
                  <?= esc($lab['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="category">Kategori</label>
            <select id="category" name="category" class="form-control" required>
              <option value="">- Pilih Kategori -</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['name']) ?>" <?= old('category') === (string) $category['name'] ? 'selected' : '' ?>>
                  <?= esc($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">
              Kategori dikelola di <a href="<?= base_url('admin/loans/asset-categories') ?>">Master Kategori Alat</a>.
            </small>
          </div>

          <div class="form-group">
            <label for="specifications">Spesifikasi</label>
            <textarea id="specifications" name="specifications" class="form-control" rows="4"><?= old('specifications') ?></textarea>
          </div>

          <div class="form-group">
            <label for="asset_photo">Foto Alat</label>
            <input type="file" id="asset_photo" name="asset_photo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="form-text text-muted">Opsional. Format: PNG/JPG/WEBP/SVG, maksimal 2 MB. Untuk PNG/JPG/WEBP sistem akan crop/resize otomatis ke rasio 1:1.</small>
          </div>

          <div class="form-group">
            <label for="max_loan_hours">Maksimal Jam Peminjaman</label>
            <input type="number" id="max_loan_hours" name="max_loan_hours" min="0" value="<?= old('max_loan_hours', '24') ?>" class="form-control" required>
            <small class="form-text text-muted">Isi 0 untuk tanpa batas waktu peminjaman (unlimited).</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Alat Aktif</label>
            </div>
          </div>

          <hr>
          <h6 class="text-uppercase text-muted mb-3">Data Inventaris</h6>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="brand">Merk</label>
              <input type="text" id="brand" name="brand" class="form-control" value="<?= old('brand') ?>" maxlength="80">
            </div>
            <div class="form-group col-md-4">
              <label for="model">Model/Tipe</label>
              <input type="text" id="model" name="model" class="form-control" value="<?= old('model') ?>" maxlength="80">
            </div>
            <div class="form-group col-md-4">
              <label for="unit_id">Satuan</label>
              <select id="unit_id" name="unit_id" class="form-control">
                <option value="">- Pilih Satuan -</option>
                <?php foreach ($units as $u): ?>
                  <option value="<?= (int) $u['id'] ?>" <?= old('unit_id') == (string) $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?> (<?= esc($u['symbol']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="acquisition_date">Tanggal Pengadaan</label>
              <input type="date" id="acquisition_date" name="acquisition_date" class="form-control" value="<?= old('acquisition_date') ?>">
            </div>
            <div class="form-group col-md-4">
              <label for="acquisition_source">Sumber Pengadaan</label>
              <select id="acquisition_source" name="acquisition_source" class="form-control">
                <?php $selSrc = old('acquisition_source', 'pembelian'); ?>
                <?php foreach ($acquisitionSources as $src): ?>
                  <option value="<?= esc($src) ?>" <?= $selSrc === $src ? 'selected' : '' ?>><?= esc(ucfirst($src)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="purchase_price">Harga Beli per Unit</label>
              <input type="number" step="0.01" min="0" id="purchase_price" name="purchase_price" class="form-control" value="<?= old('purchase_price') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="supplier">Pemasok/Vendor</label>
              <input type="text" id="supplier" name="supplier" class="form-control" value="<?= old('supplier') ?>" maxlength="150">
            </div>
            <div class="form-group col-md-6">
              <label for="funding_source">Sumber Dana</label>
              <input type="text" id="funding_source" name="funding_source" class="form-control" value="<?= old('funding_source') ?>" maxlength="100" placeholder="BOPTN, hibah, dll">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="warranty_until">Garansi Sampai</label>
              <input type="date" id="warranty_until" name="warranty_until" class="form-control" value="<?= old('warranty_until') ?>">
            </div>
            <div class="form-group col-md-8">
              <label for="minimum_stock">Stok Minimum</label>
              <input type="number" min="0" id="minimum_stock" name="minimum_stock" class="form-control" value="<?= old('minimum_stock', '0') ?>">
              <small class="form-text text-muted">Ambang batas peringatan stok rendah.</small>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Catatan</label>
            <textarea id="notes" name="notes" class="form-control" rows="3" maxlength="2000"><?= old('notes') ?></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/assets') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Alat</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
