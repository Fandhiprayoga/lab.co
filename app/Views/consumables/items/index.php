<?php
$items      = $items ?? [];
$labs       = $labs ?? [];
$categories = $categories ?? [];
$filter     = $filter ?? ['labId' => 0, 'categoryId' => 0];
?>

<!-- Filter -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= site_url('admin/consumables/items') ?>" class="form-inline flex-wrap gap-2">
      <select name="lab_id" class="form-control form-control-sm mr-2 mb-2">
        <option value="">— Semua Lab —</option>
        <?php foreach ($labs as $lab): ?>
          <option value="<?= $lab['id'] ?>" <?= (int)$filter['labId'] === (int)$lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="category_id" class="form-control form-control-sm mr-2 mb-2">
        <option value="">— Semua Kategori —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= (int)$filter['categoryId'] === (int)$cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm btn-primary mb-2">Filter</button>
      <a href="<?= site_url('admin/consumables/items') ?>" class="btn btn-sm btn-light mb-2">Reset</a>
    </form>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <small class="text-muted"><?= count($items) ?> bahan</small>
  <a href="<?= site_url('admin/consumables/items/create') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus mr-1"></i> Tambah Bahan
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th>Lab</th>
            <th class="text-right">Stok Total</th>
            <th class="text-right">Tersedia</th>
            <th class="text-right">Min. Stok</th>
            <th>Satuan</th>
            <th class="text-center">Approval</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data bahan.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><strong><?= esc($item['name']) ?></strong></td>
              <td><?= esc($item['category_name'] ?? '—') ?></td>
              <td><?= esc($item['lab_name'] ?? '—') ?></td>
              <td class="text-right"><?= number_format((float)$item['stock_total'], 2) ?></td>
              <td class="text-right"><?= number_format((float)$item['stock_available'], 2) ?></td>
              <td class="text-right"><?= number_format((float)$item['min_stock'], 2) ?></td>
              <td><?= esc($item['unit_symbol'] ?? '—') ?></td>
              <td class="text-center">
                <?= $item['requires_approval'] ? '<span class="badge badge-info">Ya</span>' : '<span class="text-muted">—</span>' ?>
              </td>
              <td class="text-center">
                <?= $item['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' ?>
              </td>
              <td class="text-right" style="white-space:nowrap">
                <a href="<?= site_url('admin/consumables/items/' . $item['id'] . '/edit') ?>" class="btn btn-xs btn-light" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                <form method="post" action="<?= site_url('admin/consumables/items/' . $item['id'] . '/toggle') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-<?= $item['is_active'] ? 'warning' : 'success' ?>" title="<?= $item['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                    <i class="fas fa-<?= $item['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                  </button>
                </form>
                <form method="post" action="<?= site_url('admin/consumables/items/' . $item['id'] . '/delete') ?>" class="d-inline"
                      onsubmit="return confirm('Hapus bahan ini? Data tidak dapat dipulihkan.')">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
