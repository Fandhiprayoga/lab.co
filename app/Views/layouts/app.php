<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title><?= $title ?? 'Dashboard' ?> &mdash; <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?></title>

  <!-- Favicon -->
  <?php
    $favicon = setting('App.siteFavicon');
    $faviconUrl = ! empty($favicon) ? base_url($favicon) : base_url('assets/img/stisla-fill.svg');
  ?>
  <link rel="shortcut icon" href="<?= $faviconUrl ?>">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- CSS Libraries -->
  <?= $this->renderSection('css') ?>

  <!-- Template CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">

  <!-- Dynamic Theme Colors -->
  <?php
    $navbarBg      = setting('App.navbarBg') ?: '#6777ef';
    $sidebarActive = setting('App.sidebarActive') ?: '#6777ef';
    // Sanitize: only allow valid hex color format
    $navbarBg      = preg_match('/^#[0-9A-Fa-f]{6}$/', $navbarBg) ? $navbarBg : '#6777ef';
    $sidebarActive = preg_match('/^#[0-9A-Fa-f]{6}$/', $sidebarActive) ? $sidebarActive : '#6777ef';
  ?>
  <style>
    .navbar-bg { background-color: <?= $navbarBg ?> !important; }
    body:not(.sidebar-mini) .sidebar-style-1 .sidebar-menu li.active > a {
      background-color: <?= $sidebarActive ?> !important;
    }
    body:not(.sidebar-mini) .sidebar-style-1 .sidebar-menu li.active ul.dropdown-menu li a:hover {
      background-color: <?= $sidebarActive ?> !important;
    }
    body.sidebar-mini .main-sidebar .sidebar-menu > li.active > a {
      background-color: <?= $sidebarActive ?> !important;
    }
    body:not(.sidebar-mini) .sidebar-style-2 .sidebar-menu > li.active > a:before {
      background-color: <?= $sidebarActive ?> !important;
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper">
      <div class="navbar-bg"></div>

      <!-- Navbar -->
      <?= $this->include('partials/navbar') ?>

      <!-- Sidebar -->
      <?= $this->include('partials/sidebar') ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1><?= $page_title ?? 'Page' ?></h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="<?= base_url('dashboard') ?>">Dashboard</a></div>
              <?php if (isset($page_title) && $page_title !== 'Dashboard'): ?>
              <div class="breadcrumb-item"><?= $page_title ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="section-body">
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

            <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible show fade">
              <div class="alert-body">
                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                <ul class="mb-0">
                  <?php foreach (session()->getFlashdata('errors') as $err): ?>
                  <li><?= esc($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
          </div>
        </section>
      </div>

      <!-- Footer -->
      <?= $this->include('partials/footer') ?>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= base_url('assets/js/stisla.js') ?>"></script>

  <script>
    document.addEventListener('submit', function (event) {
      var form = event.target.closest('.js-swal-delete-form, .js-swal-confirm-form');
      if (!form) return;

      event.preventDefault();

      var isDelete    = form.classList.contains('js-swal-delete-form');
      var title       = form.getAttribute('data-swal-title')         || (isDelete ? 'Hapus data?' : 'Konfirmasi?');
      var text        = form.getAttribute('data-swal-text')          || (isDelete ? 'Data akan dihapus permanen.' : '');
      var icon        = form.getAttribute('data-swal-icon')          || 'warning';
      var confirmText = form.getAttribute('data-swal-confirm')       || (isDelete ? 'Ya, hapus' : 'Ya');
      var cancelText  = form.getAttribute('data-swal-cancel')        || 'Batal';
      var confirmColor= form.getAttribute('data-swal-confirm-color') || (isDelete ? '#e3342f' : '#3085d6');

      Swal.fire({
        title            : title,
        text             : text,
        icon             : icon,
        showCancelButton : true,
        confirmButtonText: confirmText,
        cancelButtonText : cancelText,
        confirmButtonColor: confirmColor,
        reverseButtons   : true,
      }).then(function (result) {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  </script>

  <!-- JS Libraries -->
  <?= $this->renderSection('js') ?>

  <!-- Template JS File -->
  <script src="<?= base_url('assets/js/scripts.js') ?>"></script>
  <script src="<?= base_url('assets/js/custom.js') ?>"></script>

  <!-- Page Specific JS File -->
  <?= $this->renderSection('page_js') ?>
</body>
</html>
