<?php $items = $items ?? []; ?>
<?php $assets = $assets ?? []; ?>
<?php $filterAssetId = (int) ($filterAssetId ?? 0); ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Manajemen Item Alat</h4>
        <a href="<?= base_url('admin/loans/asset-items/create' . ($filterAssetId > 0 ? '?asset_id=' . $filterAssetId : '')) ?>" class="btn btn-primary">
          <i class="fas fa-plus"></i> Tambah Item
        </a>
      </div>
      <div class="card-body">
        <form method="get" action="<?= base_url('admin/loans/asset-items') ?>" class="mb-3">
          <div class="form-row align-items-end">
            <div class="col-md-6">
              <label for="asset_id">Filter Master Alat</label>
              <select name="asset_id" id="asset_id" class="form-control">
                <option value="">Semua Master Alat</option>
                <?php foreach ($assets as $asset): ?>
                  <option value="<?= (int) $asset['id'] ?>" <?= $filterAssetId === (int) $asset['id'] ? 'selected' : '' ?>>
                    <?= esc(($asset['asset_code'] ?? '-') . ' - ' . ($asset['name'] ?? '')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 d-flex" style="gap:.5rem;">
              <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-filter"></i> Terapkan
              </button>
              <a href="<?= base_url('admin/loans/asset-items') ?>" class="btn btn-light">Reset</a>
            </div>
          </div>
        </form>

        <form method="post" action="<?= base_url('admin/loans/asset-items/bulk-generate') ?>" class="mb-4">
          <?= csrf_field() ?>
          <div class="form-row align-items-end">
            <div class="col-md-5">
              <label for="bulk_asset_id">Generate Item dari Master</label>
              <select name="asset_id" id="bulk_asset_id" class="form-control" required>
                <option value="">Pilih Master Alat</option>
                <?php foreach ($assets as $asset): ?>
                  <option value="<?= (int) $asset['id'] ?>" <?= $filterAssetId === (int) $asset['id'] ? 'selected' : '' ?>>
                    <?= esc(($asset['asset_code'] ?? '-') . ' - ' . ($asset['name'] ?? '')) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="qty">Jumlah</label>
              <input type="number" name="qty" id="qty" class="form-control" min="1" max="500" value="1" required>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-outline-success">
                <i class="fas fa-magic"></i> Generate Bulk
              </button>
            </div>
          </div>
          <small class="text-muted">Maksimum 500 item per generate.</small>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Item Code</th>
                <th>Master Alat</th>
                <th>Serial</th>
                <th>Lab</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Loanable</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted">Belum ada item alat.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><code><?= esc($item['item_code'] ?? '-') ?></code></td>
                  <td>
                    <?= esc($item['asset_name'] ?? '-') ?>
                    <div><small class="text-muted"><?= esc($item['asset_code_master'] ?? '-') ?></small></div>
                  </td>
                  <td><?= esc($item['serial_number'] ?? '-') ?></td>
                  <td><?= esc($item['lab_name'] ?? '-') ?></td>
                  <td><?= esc(str_replace('_', ' ', (string) ($item['condition_status'] ?? '-'))) ?></td>
                  <td><?= esc(str_replace('_', ' ', (string) ($item['inventory_status'] ?? '-'))) ?></td>
                  <td>
                    <?php if ((int) ($item['is_loanable'] ?? 0) === 1): ?>
                      <span class="badge badge-success">Ya</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Tidak</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/asset-items/edit/' . $item['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/asset-items/delete/' . $item['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus item?"
                          data-swal-text="Item <?= esc($item['item_code'] ?? '-') ?> akan dihapus permanen."
                          data-swal-confirm="Ya, hapus"
                          data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>
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
  </div>
</div>
