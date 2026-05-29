<?php $currentUser = auth()->user(); ?>

<div class="row">
  <div class="col-12 col-md-4">
    <div class="card card-primary">
      <div class="card-header">
        <h4>Info Profil</h4>
      </div>
      <div class="card-body text-center">
        <img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mb-3" width="100">
        <h5><?= esc($currentUser->username) ?></h5>
        <p class="text-muted"><?= esc($currentUser->email) ?></p>
        <?php foreach ($userGroups as $group): ?>
          <?php
            $badgeClass = match($group) {
              'superadmin' => 'badge-danger',
              'laboran'    => 'badge-warning',
              'asisten'    => 'badge-info',
              'kepala_lab' => 'badge-success',
              'dosen'      => 'badge-primary',
              default      => 'badge-secondary',
            };
          ?>
          <span class="badge <?= $badgeClass ?>"><?= ucfirst($group) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-8">
    <div class="card">
      <div class="card-header">
        <h4>Edit Profil</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('profile/update') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= old('username', $currentUser->username) ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" value="<?= esc($currentUser->email) ?>" disabled>
            <small class="form-text text-muted">Email tidak dapat diubah.</small>
          </div>

          <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password">
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
          </div>

          <div class="form-group text-right">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
