<?php
$items      = $items ?? [];
$labs       = $labs ?? [];
$categories = $categories ?? [];
$filter     = $filter ?? ['labId' => 0, 'categoryId' => 0, 'lowStock' => false];
?>

<!-- Filter -->
<div class="card mb-3">
  <div class="card-body">
    <form method="get" action="<?= site_url('consumables') ?>" class="form-inline flex-wrap gap-2">
      <select name="lab_id" class="form-control form-control-sm mr-2 mb-2">
        <option value="">— Semua Lab —</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= $lab['id'] ?>" <?= (int)$filter['labId'] === (int)$lab['id'] ? 'selected' : '' ?>>
            <?= esc($lab['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="category_id" class="form-control form-control-sm mr-2 mb-2">
        <option value="">— Semua Kategori —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= (int)$filter['categoryId'] === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= esc($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-check mr-2 mb-2">
        <input type="checkbox" class="form-check-input" id="low_stock" name="low_stock" value="1" <?= $filter['lowStock'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="low_stock">Stok Rendah</label>
      </div>
      <button type="submit" class="btn btn-sm btn-primary mb-2">Filter</button>
      <a href="<?= site_url('consumables') ?>" class="btn btn-sm btn-light mb-2">Reset</a>
    </form>
  </div>
</div>

<!-- Tombol aksi -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <small class="text-muted"><?= count($items) ?> bahan ditemukan</small>
  <div>
    <?php if (activeGroupCan('bhp.request.create')): ?>
      <a href="<?= site_url('consumables/requests/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Buat Permintaan
      </a>
    <?php endif; ?>
    <?php if (activeGroupCan('bhp.request.track')): ?>
      <a href="<?= site_url('consumables/requests') ?>" class="btn btn-light btn-sm ml-1">
        <i class="fas fa-clipboard-list mr-1"></i> Permintaan Saya
      </a>
    <?php endif; ?>
  </div>
</div>

<!-- Tabel katalog -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th>Lab</th>
            <th class="text-right">Stok Tersedia</th>
            <th class="text-right">Stok Total</th>
            <th class="text-right">Min. Stok</th>
            <th>Lokasi</th>
            <th>Kedaluwarsa</th>
            <th class="text-center">Status</th>
            <?php if (activeGroupCan('bhp.stock.adjust')): ?><th></th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data bahan.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $item):
              $isLow    = (float)$item['stock_available'] <= (float)$item['min_stock'];
              $isExpired = ! empty($item['expiry_date']) && strtotime($item['expiry_date']) < strtotime('today');
            ?>
            <tr class="<?= $isLow ? 'table-warning' : '' ?>">
              <td>
                <strong><?= esc($item['name']) ?></strong>
                <?php if ($item['requires_approval']): ?>
                  <span class="badge badge-info badge-sm ml-1" title="Memerlukan persetujuan Ka. Lab">Perlu Approval</span>
                <?php endif; ?>
              </td>
              <td><?= esc($item['category_name'] ?? '—') ?></td>
              <td><?= esc($item['lab_name'] ?? '—') ?></td>
              <td class="text-right <?= $isLow ? 'text-danger font-weight-bold' : '' ?>">
                <?= number_format((float)$item['stock_available'], 2) ?> <?= esc($item['unit_symbol'] ?? '') ?>
              </td>
              <td class="text-right"><?= number_format((float)$item['stock_total'], 2) ?> <?= esc($item['unit_symbol'] ?? '') ?></td>
              <td class="text-right"><?= number_format((float)$item['min_stock'], 2) ?></td>
              <td><?= esc($item['location'] ?? '—') ?></td>
              <td>
                <?php if (empty($item['expiry_date'])): ?>
                  <span class="text-muted">—</span>
                <?php elseif ($isExpired): ?>
                  <span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i><?= esc($item['expiry_date']) ?></span>
                <?php else: ?>
                  <?= esc($item['expiry_date']) ?>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if ($isLow): ?>
                  <span class="badge badge-warning">Stok Rendah</span>
                <?php elseif ($isExpired): ?>
                  <span class="badge badge-danger">Kedaluwarsa</span>
                <?php else: ?>
                  <span class="badge badge-success">Tersedia</span>
                <?php endif; ?>
              </td>
              <?php if (activeGroupCan('bhp.stock.adjust')): ?>
              <td class="text-right">
                <a href="<?= site_url('consumables/adjustments/' . $item['id'] . '/create') ?>" class="btn btn-xs btn-light" title="Penyesuaian Stok">
                  <i class="fas fa-sliders-h"></i>
                </a>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
