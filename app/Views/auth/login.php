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
  <div class="card-header"><h4>Login</h4></div>

  <div class="card-body">
    <?php if (session('error') !== null) : ?>
      <!-- <div class="alert alert-danger"><?= session('error') ?></div> -->
    <?php endif ?>

    <?php if (session('errors') !== null) : ?>
      <div class="alert alert-danger">
        <?php foreach (session('errors') as $error) : ?>
          <p><?= $error ?></p>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <?php if (session('message') !== null) : ?>
      <div class="alert alert-success"><?= session('message') ?></div>
    <?php endif ?>

    <form method="POST" action="<?= url_to('login') ?>">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" class="form-control" name="email" value="<?= old('email') ?>" tabindex="1" required autofocus>
      </div>

      <div class="form-group">
        <div class="d-block">
          <label for="password" class="control-label">Password</label>
          <div class="float-right">
            <a href="<?= url_to('magic-link') ?>" class="text-small">Lupa Password?</a>
          </div>
        </div>
        <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember-me" <?php if (old('remember')): ?> checked<?php endif ?>>
          <label class="custom-control-label" for="remember-me">Ingat Saya</label>
        </div>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
          Login
        </button>
      </div>
    </form>
  </div>
</div>
<?php if (setting('Auth.allowRegistration')): ?>
<div class="mt-5 text-muted text-center">
  Belum punya akun? <a href="<?= url_to('register') ?>">Daftar</a>
</div>
<?php endif ?>
<?= $this->endSection() ?>
