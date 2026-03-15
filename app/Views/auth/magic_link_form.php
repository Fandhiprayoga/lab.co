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
  <div class="card-header"><h4><?= lang('Auth.useMagicLink') ?></h4></div>

  <div class="card-body">
    <?php if (session('error') !== null) : ?>
      <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif ?>

    <?php if (session('errors') !== null) : ?>
      <div class="alert alert-danger">
        <?php if (is_array(session('errors'))) : ?>
          <?php foreach (session('errors') as $error) : ?>
            <p><?= esc($error) ?></p>
          <?php endforeach ?>
        <?php else : ?>
          <p><?= esc(session('errors')) ?></p>
        <?php endif ?>
      </div>
    <?php endif ?>

    <?php if (session('message') !== null) : ?>
      <div class="alert alert-success"><?= session('message') ?></div>
    <?php endif ?>

    <p class="text-muted">Masukkan email Anda untuk menerima link login.</p>

    <form method="POST" action="<?= url_to('magic-link') ?>">
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" class="form-control" name="email" value="<?= old('email', auth()->user()->email ?? null) ?>" tabindex="1" required autofocus>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="2">
          <?= lang('Auth.send') ?>
        </button>
      </div>
    </form>
  </div>
</div>
<div class="mt-5 text-muted text-center">
  <a href="<?= url_to('login') ?>"><?= lang('Auth.backToLogin') ?></a>
</div>
<?= $this->endSection() ?>
