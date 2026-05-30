<?php
$categories = $categories ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <small class="text-muted"><?= count($categories) ?> kategori</small>
  <a href="<?= site_url('admin/consumables/categories/create') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus mr-1"></i> Tambah Kategori
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Deskripsi</th>
          <th class="text-center">Jumlah Bahan</th>
          <th class="text-center">Urutan</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($categories)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
        <?php else: ?>
          <?php foreach ($categories as $i => $cat): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= esc($cat['name']) ?></strong></td>
            <td><?= esc(mb_strimwidth($cat['description'] ?? '', 0, 80, '…')) ?: '<span class="text-muted">—</span>' ?></td>
            <td class="text-center"><?= (int)$cat['item_total'] ?></td>
            <td class="text-center"><?= (int)$cat['sort_order'] ?></td>
            <td class="text-center">
              <?php if ($cat['is_active']): ?>
                <span class="badge badge-success">Aktif</span>
              <?php else: ?>
                <span class="badge badge-secondary">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td class="text-right">
              <a href="<?= site_url('admin/consumables/categories/' . $cat['id'] . '/edit') ?>" class="btn btn-xs btn-light" title="Edit">
                <i class="fas fa-pencil-alt"></i>
              </a>
              <?php if ((int)$cat['item_total'] === 0): ?>
              <form method="post" action="<?= site_url('admin/consumables/categories/' . $cat['id'] . '/delete') ?>" class="d-inline"
                    onsubmit="return confirm('Hapus kategori ini?')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
