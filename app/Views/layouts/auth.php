<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title><?= $title ?? 'Login' ?> &mdash; <?= setting('App.siteName') ?? 'CI4 Shield RBAC' ?></title>

  <!-- Favicon -->
  <?php
    $favicon = setting('App.siteFavicon');
    $faviconUrl = ! empty($favicon) ? base_url($favicon) : base_url('assets/img/stisla-fill.svg');
  ?>
  <link rel="shortcut icon" href="<?= $faviconUrl ?>">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- Template CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>

<body>
  <div aria-hidden="true" class="auth-bg-layer">
    <span class="auth-bg-blob auth-bg-blob-brand"></span>
    <span class="auth-bg-blob auth-bg-blob-blue"></span>
  </div>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <?= session()->getFlashdata('success') ?>
              </div>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <?= session()->getFlashdata('error') ?>
              </div>
            </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>

            <div class="simple-footer">
              Copyright &copy; <?= date('Y') ?> <?= setting('App.siteFooter') ?? 'CI4 Shield RBAC' ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script src="<?= base_url('assets/js/stisla.js') ?>"></script>
  <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
  <script src="<?= base_url('assets/js/custom.js') ?>"></script>
  <style>
    body {
      min-height: 100vh;
      background:
        radial-gradient(circle at top right, rgba(251, 231, 231, 0.9) 0, rgba(251, 231, 231, 0.9) 18%, rgba(251, 231, 231, 0) 55%),
        radial-gradient(circle at left center, rgba(219, 234, 254, 0.9) 0, rgba(219, 234, 254, 0.9) 16%, rgba(219, 234, 254, 0) 50%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 52%, #fffaf9 100%);
      background-attachment: fixed;
      position: relative;
      overflow-x: hidden;
    }
    .auth-bg-layer {
      position: fixed;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
    }
    .auth-bg-blob {
      position: absolute;
      border-radius: 9999px;
      filter: blur(48px);
      opacity: 0.7;
      transform: translateZ(0);
    }
    .auth-bg-blob-brand {
      top: -12%;
      right: -8%;
      width: 42vw;
      height: 42vw;
      min-width: 280px;
      min-height: 280px;
      background: rgba(251, 231, 231, 0.85);
    }
    .auth-bg-blob-blue {
      left: -10%;
      top: 38%;
      width: 34vw;
      height: 34vw;
      min-width: 220px;
      min-height: 220px;
      background: rgba(219, 234, 254, 0.8);
    }
    #app {
      position: relative;
      z-index: 1;
    }
  </style>
</body>
</html>
