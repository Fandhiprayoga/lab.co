<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<?php if (! setting('Auth.allowRegistration')): ?>
<div class="text-center mt-5">
  <h4 class="text-danger">Registrasi Ditutup</h4>
  <p class="text-muted">Registrasi user baru saat ini tidak diizinkan.</p>
  <a href="<?= url_to('login') ?>" class="btn btn-primary">Kembali ke Login</a>
</div>
<?php $this->endSection(); return; endif; ?>
<div class="login-brand">
  <?php
    $logo = setting('App.siteLogo');
    $logoUrl = ! empty($logo) ? base_url($logo) : base_url('assets/img/stisla-fill.svg');
  ?>
  <img src="<?= $logoUrl ?>" alt="logo" width="100" class="shadow-light rounded-circle">
</div>

<div class="card card-primary">
  <div class="card-header"><h4>Daftar Akun Baru</h4></div>

  <div class="card-body">
    <?php
      $allErrors = [];
      if (session('error') !== null) {
          $allErrors[] = session('error');
      }
      if (session('errors') !== null) {
          foreach ((array) session('errors') as $e) {
              $allErrors[] = $e;
          }
      }
    ?>
    <?php if (! empty($allErrors)) : ?>
      <div class="alert alert-danger">
        <?php foreach ($allErrors as $e) : ?>
          <p class="mb-0"><?= esc($e) ?></p>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <form method="POST" action="<?= url_to('register') ?>">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" type="text" class="form-control" name="username" value="<?= old('username') ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" class="form-control" name="email" value="<?= old('email') ?>" required>
        <?php
          $allowedDomains = setting('App.allowedEmailDomains');
          if (! empty($allowedDomains)):
            $domainList = implode(', @', array_map('trim', explode(',', $allowedDomains)));
        ?>
        <small class="form-text text-muted">
          <i class="fas fa-info-circle"></i> Gunakan email institusi dengan domain: <strong>@<?= esc($domainList) ?></strong>
        </small>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" class="form-control" name="password" required>
      </div>

      <div class="form-group">
        <label for="password_confirm">Konfirmasi Password</label>
        <input id="password_confirm" type="password" class="form-control" name="password_confirm" required>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-lg btn-block">
          Daftar
        </button>
      </div>
    </form>
  </div>
</div>
<div class="mt-5 text-muted text-center">
  Sudah punya akun? <a href="<?= url_to('login') ?>">Login</a>
</div>
<?= $this->endSection() ?>
