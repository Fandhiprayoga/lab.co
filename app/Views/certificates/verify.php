<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Verifikasi Sertifikat — <?= esc($issuance->cert_code) ?></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
  <style>
    body { background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .verify-card { max-width: 480px; width: 100%; }
    .verify-icon { font-size: 64px; }
  </style>
</head>
<body>
<div class="container">
  <div class="verify-card mx-auto">
    <div class="card shadow">
      <div class="card-body text-center py-5">
        <?php if ($issuance->is_revoked): ?>
          <div class="text-danger verify-icon mb-3">
            <i class="fas fa-times-circle"></i>
          </div>
          <h3 class="text-danger">Sertifikat Dicabut</h3>
          <p class="text-muted">Sertifikat ini sudah tidak berlaku.</p>
          <?php if ($issuance->revoke_reason): ?>
            <div class="alert alert-warning mt-2">
              <small>Alasan: <?= esc($issuance->revoke_reason) ?></small>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-success verify-icon mb-3">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3 class="text-success">Sertifikat Valid</h3>
          <p class="text-muted">Sertifikat ini sah dan masih berlaku.</p>
        <?php endif; ?>

        <hr>

        <table class="table table-sm text-left mt-3">
          <tr>
            <th width="130">Kode Sertifikat</th>
            <td><code><?= esc($issuance->cert_code) ?></code></td>
          </tr>
          <tr>
            <th>Template</th>
            <td><?= esc($issuance->template_name) ?></td>
          </tr>
          <tr>
            <th>Penerima</th>
            <td><?= esc($issuance->recipient_name) ?></td>
          </tr>
          <?php if ($issuance->recipient_role): ?>
          <tr>
            <th>Role</th>
            <td><?= esc($issuance->recipient_role) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <th>Tanggal Terbit</th>
            <td><?= date('d F Y', strtotime($issuance->issued_at)) ?></td>
          </tr>
          <tr>
            <th>Diterbitkan Oleh</th>
            <td><?= esc($issuer_name) ?></td>
          </tr>
        </table>
      </div>
      <div class="card-footer text-center">
        <a href="<?= base_url() ?>" class="btn btn-sm btn-secondary">Kembali</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
