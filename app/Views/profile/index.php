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

        <?php if (! empty($profile['prodi']) || ! empty($profile['nim_nik']) || ! empty($profile['phone'])): ?>
          <hr>
          <div class="text-left small">
            <?php if (! empty($profile['prodi'])): ?>
              <p class="mb-1"><i class="fas fa-university mr-1 text-muted"></i> <?= esc($profile['prodi']) ?></p>
            <?php endif; ?>
            <?php if (! empty($profile['nim_nik'])): ?>
              <p class="mb-1"><i class="fas fa-id-card mr-1 text-muted"></i> NIM/NIK: <?= esc($profile['nim_nik']) ?></p>
            <?php endif; ?>
            <?php if (! empty($profile['phone'])): ?>
              <p class="mb-1"><i class="fas fa-phone mr-1 text-muted"></i> <?= esc($profile['phone']) ?></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
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

          <hr>
          <h6 class="text-muted mb-3">Data Tambahan</h6>

          <div class="form-group">
            <label for="prodi">Program Studi (Prodi)</label>
            <select class="form-control" id="prodi" name="prodi">
              <option value="">-- Pilih Program Studi --</option>
              <?php
                $selectedProdi = old('prodi', $profile['prodi'] ?? '');
                foreach ($studyPrograms as $sp):
              ?>
                <option value="<?= esc($sp['name']) ?>"
                  <?= $selectedProdi === $sp['name'] ? 'selected' : '' ?>>
                  <?= esc($sp['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="nim_nik">NIM / NIK <small class="text-muted">(Nomor Induk Mahasiswa / Nomor Induk Karyawan)</small></label>
            <input type="text" class="form-control" id="nim_nik" name="nim_nik"
                   value="<?= old('nim_nik', esc($profile['nim_nik'] ?? '')) ?>"
                   placeholder="Masukkan NIM atau NIK Anda">
          </div>

          <div class="form-group">
            <label for="phone">No. HP / WhatsApp</label>
            <input type="text" class="form-control" id="phone" name="phone"
                   value="<?= old('phone', esc($profile['phone'] ?? '')) ?>"
                   placeholder="Contoh: 08123456789">
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
