<div class="row">
  <?php if (! empty($assets)): ?>
    <?php foreach ($assets as $asset): ?>
      <div class="col-md-4">
        <div class="card card-primary">
          <div class="card-header">
            <h4><?= esc($asset['name']) ?></h4>
          </div>
          <div class="card-body">
            <p class="mb-1"><strong>Tipe:</strong> <?= esc($asset['asset_type']) ?></p>
            <p class="mb-1"><strong>Kategori:</strong> <?= esc($asset['category'] ?? '-') ?></p>
            <p class="mb-1"><strong>Lab:</strong> <?= esc($asset['lab_name'] ?? '-') ?></p>
            <p class="mb-1"><strong>Lokasi Lab:</strong> <?= esc($asset['lab_location'] ?? '-') ?></p>
            <p class="mb-1"><strong>Stok Tersedia:</strong> <?= (int) $asset['stock_available'] ?>/<?= (int) $asset['stock_total'] ?></p>
            <p class="mb-0"><strong>Maks Pinjam:</strong> <?= (int) $asset['max_loan_hours'] === 0 ? 'Unlimited' : (int) $asset['max_loan_hours'] . ' jam' ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="alert alert-info mb-0">Belum ada data aset aktif.</div>
    </div>
  <?php endif; ?>
</div>
