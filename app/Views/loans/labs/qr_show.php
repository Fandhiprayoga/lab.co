<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code – <?= esc($lab['name']) ?></title>
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

    .qr-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 32px rgba(0,0,0,.10);
      padding: 2.5rem 2rem 2rem;
      max-width: 440px;
      width: 100%;
      text-align: center;
    }

    .qr-card__title {
      font-size: 1.35rem;
      font-weight: 700;
      color: #1a202c;
      margin-bottom: .25rem;
    }

    .qr-card__meta {
      font-size: .875rem;
      color: #718096;
      margin-bottom: .15rem;
    }

    .qr-card__divider {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 1.25rem 0;
    }

    .qr-card__image {
      display: block;
      margin: 0 auto 1rem;
      max-width: 300px;
      width: 100%;
      height: auto;
    }

    .qr-card__url {
      font-size: .72rem;
      color: #a0aec0;
      word-break: break-all;
      margin-bottom: 1.5rem;
    }

    .qr-card__actions {
      display: flex;
      gap: .75rem;
      justify-content: center;
      flex-wrap: wrap;
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
    .btn-outline  { background: #fff; color: #4f46e5; border-color: #4f46e5; }
    .btn-light    { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    @media print {
      body { background: #fff; padding: 0; }
      .qr-card { box-shadow: none; border-radius: 0; max-width: 100%; padding: 1rem; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

<div class="qr-card">

  <div class="qr-card__title"><?= esc($lab['name']) ?></div>

  <?php if (! empty($lab['code'])): ?>
    <div class="qr-card__meta">Kode: <?= esc($lab['code']) ?></div>
  <?php endif; ?>

  <?php if (! empty($lab['location'])): ?>
    <div class="qr-card__meta">Lokasi: <?= esc($lab['location']) ?></div>
  <?php endif; ?>

  <hr class="qr-card__divider">

  <img
    class="qr-card__image"
    src="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/qr/image') ?>"
    alt="QR Code <?= esc($lab['name']) ?>"
  >

  <div class="qr-card__url"><?= esc($scanUrl) ?></div>

  <div class="qr-card__actions no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zm0 1h6a1 1 0 0 1 1 1v2H4V3a1 1 0 0 1 1-1zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
      </svg>
      Cetak
    </button>
    <a
      href="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/qr/image') ?>"
      class="btn btn-outline"
      download="qr-lab-<?= (int) $lab['id'] ?>.png"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.1a.5.5 0 0 1 1 0v2.1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.1a.5.5 0 0 1 .5-.5z"/>
        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
      </svg>
      Download PNG
    </a>
    <a href="<?= base_url('admin/loans/labs/qr') ?>" class="btn btn-light">&#8592; Kembali</a>
  </div>

</div>

</body>
</html>
