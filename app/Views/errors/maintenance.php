<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="login-brand">
  <?php
    $logo = setting('App.siteLogo');
    $logoUrl = ! empty($logo) ? base_url($logo) : base_url('assets/img/stisla-fill.svg');
  ?>
  <img src="<?= $logoUrl ?>" alt="logo" width="100" class="shadow-light rounded-circle">
</div>

<div class="card card-primary">
  <div class="card-header"><h4><i class="fas fa-tools mr-2"></i>Pemeliharaan</h4></div>

  <div class="card-body text-center">
    <div class="mb-4" style="font-size: 4rem; color: #fc544b;">
      <i class="fas fa-tools"></i>
    </div>

    <h5 class="mb-3 text-dark font-weight-bold">Sedang Dalam Pemeliharaan</h5>

    <p class="text-muted mb-4">
      <?= esc(setting('App.maintenanceMsg') ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.') ?>
    </p>

    <hr>

    <div class="text-muted small mb-3">
      <i class="fas fa-clock mr-1"></i> Kami akan segera kembali. Terima kasih atas kesabaran Anda.
    </div>

    <?php if (auth()->loggedIn()): ?>
      <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-lg btn-block">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    <?php else: ?>
      <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg btn-block">
        <i class="fas fa-sign-in-alt"></i> Login sebagai Admin
      </a>
    <?php endif; ?>
  </div>
</div>
<div class="mt-5 text-muted text-center">
  <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?> — v<?= esc(setting('App.siteVersion') ?? '1.0.0') ?>
</div>
<?= $this->endSection() ?>
