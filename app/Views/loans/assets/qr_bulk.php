<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cetak QR Code Alat (Massal)</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f4f6fb;
      padding: 1.5rem;
    }

    /* ---- action bar (no-print) ---- */
    .action-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: .75rem;
      margin-bottom: 1.5rem;
      padding: 1rem 1.25rem;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,.08);
    }

    .action-bar h1 {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1a202c;
    }

    .action-bar .meta {
      font-size: .875rem;
      color: #718096;
    }

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
    .btn-light    { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    /* ---- QR grid ---- */
    .qr-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
    }

    .qr-item {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,.08);
      padding: 1.25rem 1rem;
      text-align: center;
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .qr-item__name {
      font-size: .875rem;
      font-weight: 700;
      color: #1a202c;
      margin-bottom: .2rem;
      line-height: 1.3;
    }

    .qr-item__code {
      display: inline-block;
      padding: .15rem .55rem;
      background: #eef2ff;
      border-radius: 999px;
      font-size: .72rem;
      font-weight: 600;
      color: #4f46e5;
      letter-spacing: .04em;
      margin-bottom: .35rem;
    }

    .qr-item__meta {
      font-size: .72rem;
      color: #718096;
      margin-bottom: .5rem;
    }

    .qr-item__image {
      display: block;
      margin: .5rem auto;
      max-width: 160px;
      width: 100%;
      height: auto;
    }

    /* ---- print styles ---- */
    @media print {
      body { background: #fff; padding: .5cm; }

      .action-bar { display: none !important; }

      .qr-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: .5cm;
      }

      .qr-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: none;
        padding: .6cm .5cm;
      }
    }

    @page {
      size: A4;
      margin: 1.5cm;
    }
  </style>
</head>
<body>

<!-- Action bar (hidden when printing) -->
<div class="action-bar no-print">
  <div>
    <h1>Cetak QR Code Alat (Massal)</h1>
    <p class="meta"><?= count($assets) ?> alat dipilih</p>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
    <button onclick="window.print()" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
      </svg>
      Cetak Semua
    </button>
    <a href="<?= base_url('admin/loans/assets/qr') ?>" class="btn btn-light">
      ← Kembali
    </a>
  </div>
</div>

<!-- QR grid -->
<div class="qr-grid">
  <?php foreach ($assets as $asset): ?>
    <div class="qr-item">
      <p class="qr-item__name"><?= esc($asset['name']) ?></p>

      <?php if (! empty($asset['asset_code'])): ?>
        <span class="qr-item__code"><?= esc($asset['asset_code']) ?></span>
      <?php endif; ?>

      <?php $brandModel = trim(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? '')); ?>
      <?php if ($brandModel !== ''): ?>
        <p class="qr-item__meta"><?= esc($brandModel) ?></p>
      <?php endif; ?>

      <?php if (! empty($asset['lab_name'])): ?>
        <p class="qr-item__meta"><?= esc($asset['lab_name']) ?></p>
      <?php endif; ?>

      <img
        src="<?= base_url('admin/loans/assets/' . (int) $asset['id'] . '/qr/image') ?>"
        alt="QR <?= esc($asset['name']) ?>"
        class="qr-item__image"
      >
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
