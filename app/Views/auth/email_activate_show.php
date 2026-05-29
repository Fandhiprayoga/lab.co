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
  <div class="card-header">
    <h4><i class="fas fa-envelope-open-text mr-2"></i>Aktivasi Akun</h4>
  </div>

  <div class="card-body">
    <?php if (session('error')) : ?>
      <div class="alert alert-danger">
        <?= esc(session('error')) ?>
      </div>
    <?php endif ?>

    <div class="alert alert-info">
      <i class="fas fa-info-circle mr-1"></i>
      Kode aktivasi 6 digit telah dikirim ke alamat email Anda.
      Masukkan kode tersebut di bawah ini untuk mengaktifkan akun.
    </div>

    <form action="<?= url_to('auth-action-verify') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="token">Kode Aktivasi</label>
        <input
          type="text"
          class="form-control text-center"
          id="token"
          name="token"
          placeholder="000000"
          inputmode="numeric"
          pattern="[0-9]*"
          autocomplete="one-time-code"
          value="<?= old('token') ?>"
          maxlength="6"
          style="font-size: 2rem; letter-spacing: 0.5rem;"
          required
          autofocus
        >
        <small class="text-muted">Periksa folder inbox atau spam di email Anda.</small>
      </div>

      <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary btn-lg btn-block">
          <i class="fas fa-check-circle mr-1"></i> Aktifkan Akun
        </button>
      </div>
    </form>
  </div>

  <div class="card-footer text-center">
    <a href="<?= url_to('login') ?>" class="text-muted">
      <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
    </a>
  </div>
</div>
<?= $this->endSection() ?>
