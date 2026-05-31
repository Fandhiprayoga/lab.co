<?php
$currentUser = auth()->user();
$currentUrl  = uri_string();

/**
 * Helper untuk cek apakah menu aktif
 */
function isMenuActive(string $path): string {
    $currentUrl = uri_string();
    return (strpos($currentUrl, $path) !== false) ? 'active' : '';
}

function isDropdownActive(array $paths): string {
    $currentUrl = uri_string();
    foreach ($paths as $path) {
        if (strpos($currentUrl, $path) !== false) {
            return 'active';
        }
    }
    return '';
}
?>
<div class="main-sidebar sidebar-style-1">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="<?= base_url('dashboard') ?>"><?= esc(setting('App.siteName') ?? 'CI4 RBAC') ?></a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="<?= base_url('dashboard') ?>"><?= esc(setting('App.siteNameShort') ?? 'C4') ?></a>
    </div>
    <ul class="sidebar-menu">

      <!-- Dashboard -->
      <li class="menu-header">Dashboard</li>
      <li class="<?= isMenuActive('dashboard') && !str_contains($currentUrl, 'admin') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fas fa-fire"></i> <span>Dashboard</span></a>
      </li>

      <!-- Lending Module -->
      <?php if (activeGroupCan('lending.access')): ?>
      <li class="menu-header">Peminjaman Lab</li>

      <?php if (activeGroupCan('lending.request.create')): ?>
      <li class="<?= isMenuActive('loans/create') ?>">
        <a class="nav-link" href="<?= base_url('loans/create') ?>"><i class="fas fa-file-alt"></i> <span>Buat Proposal</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.request.track')): ?>
      <li class="<?= (strpos($currentUrl, 'loans') === 0) && !isMenuActive('loans/create') && !isMenuActive('loans/analytics') ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('loans') ?>"><i class="fas fa-clipboard-list"></i> <span>Permohonan</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.analytics.view')): ?>
      <li class="<?= isMenuActive('loans/analytics') ?>">
        <a class="nav-link" href="<?= base_url('loans/analytics') ?>"><i class="fas fa-chart-line"></i> <span>Analitik Lab</span></a>
      </li>
      <?php endif; ?>
      <?php endif; ?>

      <!-- BHP Module -->
      <?php if (activeGroupCan('bhp.access')): ?>
      <li class="menu-header">Bahan Habis Pakai</li>

      <li class="<?= isMenuActive('consumables/beranda') ?>">
        <a class="nav-link" href="<?= base_url('consumables/beranda') ?>"><i class="fas fa-home"></i> <span>Beranda BHP</span></a>
      </li>

      <?php if (activeGroupCan('bhp.catalog.view')): ?>
      <li class="<?= rtrim(uri_string(), '/') === 'consumables' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('consumables') ?>"><i class="fas fa-flask"></i> <span>Katalog Bahan</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('bhp.request.track')): ?>
      <li class="<?= isMenuActive('consumables/requests') ?>">
        <a class="nav-link" href="<?= base_url('consumables/requests') ?>"><i class="fas fa-clipboard-list"></i> <span>Permintaan BHP</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('bhp.analytics.view')): ?>
      <li class="<?= isMenuActive('consumables/analytics') ?>">
        <a class="nav-link" href="<?= base_url('consumables/analytics') ?>"><i class="fas fa-chart-bar"></i> <span>Analitik Konsumsi</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('bhp.stock.adjust')): ?>
      <li class="<?= rtrim(uri_string(), '/') === 'consumables/adjustments' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('consumables/adjustments') ?>"><i class="fas fa-history"></i> <span>Riwayat Penyesuaian</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('bhp.master.manage')): ?>
      <li class="<?= isMenuActive('admin/consumables/categories') ?>">
        <a class="nav-link" href="<?= base_url('admin/consumables/categories') ?>"><i class="fas fa-tags"></i> <span>Kategori BHP</span></a>
      </li>
      <li class="<?= isMenuActive('admin/consumables/items') ?>">
        <a class="nav-link" href="<?= base_url('admin/consumables/items') ?>"><i class="fas fa-vials"></i> <span>Master Bahan</span></a>
      </li>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Admin Menu (hanya untuk active group yang punya akses admin) -->
      <?php if (activeGroupCan('admin.access')): ?>
      <li class="menu-header">Administrasi</li>

      <!-- User Management -->
      <?php if (activeGroupCan('users.list')): ?>
      <li class="<?= isMenuActive('admin/users') ?>">
        <a class="nav-link" href="<?= base_url('admin/users') ?>"><i class="fas fa-users"></i> <span>Manajemen User</span></a>
      </li>
      <?php endif; ?>

      <!-- Role Management (superadmin only) -->
      <?php if (activeGroupIs('superadmin')): ?>
      <li class="nav-item dropdown <?= isDropdownActive(['admin/roles']) ?>">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-shield"></i> <span>Role & Permission</span></a>
        <ul class="dropdown-menu">
          <li class="<?= isMenuActive('admin/roles') && !str_contains($currentUrl, 'permissions') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/roles') ?>">Daftar Role</a>
          </li>
          <li class="<?= isMenuActive('admin/roles/permissions') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/roles/permissions') ?>">Permission Matrix</a>
          </li>
        </ul>
      </li>
      <?php endif; ?>

      <!-- Settings -->
      <?php if (activeGroupCan('admin.settings')): ?>
      <li class="<?= isMenuActive('admin/settings') ?>">
        <a class="nav-link" href="<?= base_url('admin/settings') ?>"><i class="fas fa-cog"></i> <span>Pengaturan</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.manage') || activeGroupCan('lending.master.faculties.manage') || activeGroupCan('lending.master.study_programs.manage') || activeGroupCan('lending.master.units.manage')): ?>
      <li class="menu-header">Master Data</li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.faculties.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/faculties') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/faculties') ?>"><i class="fas fa-university"></i> <span>Master Fakultas</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.study_programs.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/study-programs') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/study-programs') ?>"><i class="fas fa-graduation-cap"></i> <span>Master Program Studi</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.units.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/units') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/units') ?>"><i class="fas fa-ruler"></i> <span>Master Satuan</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.labs.manage')): ?>
      <li class="menu-header">Ruangan &amp; Lab</li>
      <li class="<?= (strpos(uri_string(), 'admin/loans/labs') === 0 && ! preg_match('#admin/loans/labs/(archive|qr|condition-history|\d+/photos|\d+/qr)#', uri_string())) ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/labs') ?>"><i class="fas fa-door-open"></i> <span>Daftar Lab Aktif</span></a>
      </li>
      <li class="<?= isMenuActive('admin/loans/labs/archive') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/labs/archive') ?>"><i class="fas fa-archive"></i> <span>Arsip Lab</span></a>
      </li>
      <li class="<?= preg_match('#admin/loans/labs/\d+/photos#', uri_string()) ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/labs') ?>"><i class="fas fa-images"></i> <span>Galeri Foto</span></a>
      </li>
      <li class="<?= isMenuActive('admin/loans/labs/qr') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/labs/qr') ?>"><i class="fas fa-qrcode"></i> <span>QR Codes</span></a>
      </li>
      <li class="<?= isMenuActive('admin/loans/labs/condition-history') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/labs/condition-history') ?>"><i class="fas fa-history"></i> <span>Riwayat Kondisi</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.manage') || activeGroupCan('lending.master.movements.manage') || activeGroupCan('lending.master.maintenances.manage') || activeGroupCan('lending.master.documents.manage')): ?>
      <li class="menu-header">Manajemen Aset</li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/asset-categories') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/asset-categories') ?>"><i class="fas fa-tags"></i> <span>Kategori Alat</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.manage')): ?>
      <li class="<?= rtrim(uri_string(), '/') === 'admin/loans/assets' ? 'active' : '' ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/assets') ?>"><i class="fas fa-tools"></i> <span>Daftar Alat</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.movements.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/movements') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/movements') ?>"><i class="fas fa-exchange-alt"></i> <span>Mutasi Aset</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.maintenances.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/maintenances') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/maintenances') ?>"><i class="fas fa-wrench"></i> <span>Perawatan Aset</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.documents.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/documents') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/documents') ?>"><i class="fas fa-folder-open"></i> <span>Dokumen Aset</span></a>
      </li>
      <?php endif; ?>

      <?php if (activeGroupCan('lending.master.manage')): ?>
      <li class="<?= isMenuActive('admin/loans/assets/qr') ?>">
        <a class="nav-link" href="<?= base_url('admin/loans/assets/qr') ?>"><i class="fas fa-qrcode"></i> <span>QR Code Alat</span></a>
      </li>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Visitor Log -->
      <?php if (activeGroupCan('visits.list')): ?>
      <li class="menu-header">Kunjungan</li>
      <li class="<?= isMenuActive('admin/visits') ?>">
        <a class="nav-link" href="<?= base_url('admin/visits') ?>"><i class="fas fa-book-open"></i> <span>Buku Kunjungan</span></a>
      </li>
      <?php endif; ?>

      <!-- Profil -->
      <li class="menu-header">Akun</li>
      <li class="<?= isMenuActive('profile') ?>">
        <a class="nav-link" href="<?= base_url('profile') ?>"><i class="far fa-user"></i> <span>Profil Saya</span></a>
      </li>
      <li>
        <a class="nav-link text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
      </li>

    </ul>
  </aside>
</div>
