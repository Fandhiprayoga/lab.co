<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan Aset – <?= esc($asset['name']) ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f4f6fb;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 32px rgba(0,0,0,.10);
      padding: 2.5rem 2rem 2rem;
      max-width: 480px;
      width: 100%;
      text-align: center;
    }
    .icon {
      width: 64px;
      height: 64px;
      margin: 0 auto 1rem;
      background: #eef2ff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .icon svg { width: 32px; height: 32px; color: #4f46e5; }
    .title { font-size: 1.35rem; font-weight: 700; color: #1a202c; margin-bottom: .25rem; }
    .code {
      display: inline-block;
      padding: .25rem .75rem;
      background: #eef2ff;
      border-radius: 999px;
      font-size: .8rem;
      font-weight: 600;
      color: #4f46e5;
      letter-spacing: .05em;
      margin-bottom: .75rem;
    }
    .meta { font-size: .875rem; color: #718096; margin-bottom: .25rem; }
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 1.25rem 0; }
    .desc { font-size: .875rem; color: #475569; line-height: 1.6; margin-bottom: .5rem; }
    .info-row {
      display: flex;
      justify-content: space-between;
      padding: .5rem 0;
      font-size: .875rem;
      border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #718096; }
    .info-value { color: #1a202c; font-weight: 500; }
    .status-badge {
      display: inline-block;
      padding: .15rem .6rem;
      border-radius: 999px;
      font-size: .75rem;
      font-weight: 600;
    }
    .status-aktif { background: #dcfce7; color: #166534; }
    .status-dipinjam { background: #fef3c7; color: #92400e; }
    .status-dalam_perbaikan { background: #fce7f3; color: #9d174d; }
    .status-dihapuskan { background: #fee2e2; color: #991b1b; }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .55rem 1.25rem;
      border-radius: 8px;
      font-size: .875rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      border: 2px solid transparent;
      transition: opacity .15s;
    }
    .btn:hover { opacity: .85; }
    .btn-primary { background: #4f46e5; color: #fff; border-color: #4f46e5; }
    .btn-outline { background: #fff; color: #4f46e5; border-color: #4f46e5; }
    .btn-light { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
  </style>
</head>
<body>
<div class="card">
  <div class="icon">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </div>

  <p class="meta">Aset Ditemukan</p>
  <h1 class="title"><?= esc($asset['name']) ?></h1>

  <?php if (! empty($asset['asset_code'])): ?>
    <span class="code"><?= esc($asset['asset_code']) ?></span>
  <?php endif; ?>

  <?php $brandModel = trim(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? '')); ?>
  <?php if ($brandModel !== ''): ?>
    <p class="meta"><?= esc($brandModel) ?></p>
  <?php endif; ?>

  <hr class="divider">

  <div class="info-row">
    <span class="info-label">Lokasi</span>
    <span class="info-value"><?= esc($location ?: '-') ?></span>
  </div>
  <div class="info-row">
    <span class="info-label">Kategori</span>
    <span class="info-value"><?= esc($asset['category'] ?? '-') ?></span>
  </div>
  <div class="info-row">
    <span class="info-label">Stok Tersedia</span>
    <span class="info-value"><?= (int) ($asset['stock_available'] ?? 0) ?> / <?= (int) ($asset['stock_total'] ?? 0) ?></span>
  </div>
  <div class="info-row">
    <span class="info-label">Status</span>
    <span class="info-value">
      <span class="status-badge status-<?= esc($asset['inventory_status'] ?? 'aktif') ?>">
        <?= esc(str_replace('_', ' ', $asset['inventory_status'] ?? 'aktif')) ?>
      </span>
    </span>
  </div>

  <div class="actions">
    <a href="<?= base_url('admin/loans/create') ?>" class="btn btn-primary">
      <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
      </svg>
      Buat Peminjaman
    </a>
    <a href="<?= base_url('admin/loans/assets/' . (int) $asset['id'] . '/edit') ?>" class="btn btn-outline">
      Lihat Detail
    </a>
  </div>

  <hr class="divider">

  <p style="font-size:.75rem;color:#a0aec0;">
    Kode Aset: <?= esc($asset['asset_code'] ?? '-') ?> &bull; ID: <?= (int) $asset['id'] ?>
  </p>
</div>
</body>
</html>
