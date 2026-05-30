<?php
$item = $item ?? [];
?>

<div class="card">
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-6">
        <dl class="mb-0">
          <dt>Bahan</dt><dd><?= esc($item['name'] ?? '—') ?></dd>
          <dt>Lab</dt><dd><?= esc($item['lab_name'] ?? '—') ?></dd>
          <dt>Kategori</dt><dd><?= esc($item['category_name'] ?? '—') ?></dd>
        </dl>
      </div>
      <div class="col-md-6">
        <dl class="mb-0">
          <dt>Stok Total</dt><dd><?= number_format((float)($item['stock_total'] ?? 0), 2) ?> <?= esc($item['unit_symbol'] ?? '') ?></dd>
          <dt>Stok Tersedia</dt><dd><?= number_format((float)($item['stock_available'] ?? 0), 2) ?> <?= esc($item['unit_symbol'] ?? '') ?></dd>
          <dt>Min. Stok</dt><dd><?= number_format((float)($item['min_stock'] ?? 0), 2) ?></dd>
        </dl>
      </div>
    </div>
    <hr>
    <form method="post" action="<?= site_url('consumables/adjustments/' . (int)($item['id'] ?? 0)) ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Jenis Penyesuaian <span class="text-danger">*</span></label>
            <select name="adjustment_type" class="form-control" required>
              <option value="">— Pilih —</option>
              <option value="susut">Susut (berkurang alami)</option>
              <option value="rusak">Rusak</option>
              <option value="tumpah">Tumpah / Tercecer</option>
              <option value="koreksi">Koreksi Stok</option>
              <option value="masuk">Masuk (penambahan stok)</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Jumlah <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
              <div class="input-group-append">
                <span class="input-group-text"><?= esc($item['unit_symbol'] ?? 'unit') ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <textarea name="reason" class="form-control" rows="3" placeholder="Jelaskan alasan penyesuaian (opsional)"></textarea>
      </div>
      <div class="d-flex justify-content-between">
        <a href="<?= site_url('consumables') ?>" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-warning">
          <i class="fas fa-sliders-h mr-1"></i> Simpan Penyesuaian
        </button>
      </div>
    </form>
  </div>
</div>
