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
    <div class="alert alert-success">
      <p><b><?= lang('Auth.checkYourEmail') ?></b></p>
      <p class="mb-0"><?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?></p>
    </div>
  </div>
</div>
<div class="mt-5 text-muted text-center">
  <a href="<?= url_to('login') ?>"><?= lang('Auth.backToLogin') ?></a>
</div>
<?= $this->endSection() ?>
