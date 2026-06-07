<?php
$currentUser = auth()->user();
$groups = $currentUser->getGroups();
$groupLabel = activeGroupTitle();
?>

<h2 class="section-title">Selamat Datang, <?= esc($currentUser->username) ?>!</h2>
<p class="section-lead">Anda login sebagai <strong><?= $groupLabel ?></strong>.
<?php if (count($groups) > 1): ?>
  <small class="text-muted">(Memiliki <?= count($groups) ?> role. Gunakan switcher di navbar untuk beralih.)</small>
<?php endif; ?>
</p>

<?php if (activeGroupCan('admin.access')): ?>
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary">
        <i class="far fa-user"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Users</h4>
        </div>
        <div class="card-body">
          <?php
            $userModel = new \CodeIgniter\Shield\Models\UserModel();
            echo $userModel->countAllResults();
          ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Roles</h4>
        </div>
        <div class="card-body">
          <?= count(config('AuthGroups')->groups) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning">
        <i class="fas fa-key"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Total Permissions</h4>
        </div>
        <div class="card-body">
          <?= count(config('AuthGroups')->permissions) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>Status</h4>
        </div>
        <div class="card-body">
          Active
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row">
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">
        <h4>Informasi Akun</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <tr>
              <th>Username</th>
              <td><?= esc($currentUser->username) ?></td>
            </tr>
            <tr>
              <th>Email</th>
              <td><?= esc($currentUser->email) ?></td>
            </tr>
            <tr>
              <th>Role</th>
              <td>
                <?php foreach ($groups as $group): ?>
                  <?php if ($group === activeGroup()): ?>
                    <span class="badge badge-success" title="Role aktif"><?= ucfirst($group) ?> ✓</span>
                  <?php else: ?>
                    <span class="badge badge-secondary"><?= ucfirst($group) ?></span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
            </tr>
            <tr>
              <th>Status</th>
              <td><span class="badge badge-success">Aktif</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header">
        <h4>Akses Cepat</h4>
      </div>
      <div class="card-body">
        <div class="row">

          <?php if (activeGroupCan('lending.access')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('loans/beranda') ?>" class="btn btn-primary btn-block">
              <i class="fas fa-home"></i><br>Beranda Peminjaman
            </a>
          </div>
          <?php if (activeGroupCan('lending.request.create')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('loans/create') ?>" class="btn btn-success btn-block">
              <i class="fas fa-file-alt"></i><br>Buat Proposal
            </a>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <?php if (activeGroupCan('clearance.access')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('clearance/beranda') ?>" class="btn btn-info btn-block">
              <i class="fas fa-home"></i><br>Beranda Clearance
            </a>
          </div>
          <?php if (activeGroupCan('clearance.request.create')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('clearance/create') ?>" class="btn btn-primary btn-block">
              <i class="fas fa-file-signature"></i><br>Ajukan Surat Bebas
            </a>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <?php if (activeGroupCan('bhp.access')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('consumables/beranda') ?>" class="btn btn-warning btn-block">
              <i class="fas fa-home"></i><br>Beranda BHP
            </a>
          </div>
          <?php endif; ?>

          <?php if (activeGroupCan('visits.list')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/visits') ?>" class="btn btn-secondary btn-block">
              <i class="fas fa-book-open"></i><br>Buku Kunjungan
            </a>
          </div>
          <?php endif; ?>

          <?php if (activeGroupCan('users.list')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/users') ?>" class="btn btn-primary btn-block">
              <i class="fas fa-users"></i><br>Manajemen User
            </a>
          </div>
          <?php endif; ?>

          <?php if (activeGroupIs('superadmin')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/roles') ?>" class="btn btn-danger btn-block">
              <i class="fas fa-user-shield"></i><br>Role & Permission
            </a>
          </div>
          <?php endif; ?>

          <div class="col-6 mb-3">
            <a href="<?= base_url('profile') ?>" class="btn btn-info btn-block">
              <i class="far fa-user"></i><br>Profil Saya
            </a>
          </div>

          <?php if (activeGroupCan('admin.settings')): ?>
          <div class="col-6 mb-3">
            <a href="<?= base_url('admin/settings') ?>" class="btn btn-warning btn-block">
              <i class="fas fa-cog"></i><br>Pengaturan
            </a>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>
