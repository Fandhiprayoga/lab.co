<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar User</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('users.create')): ?>
          <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped" id="table-users">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)): ?>
                <?php $no = 1; foreach ($users as $user): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td>
                    <img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mr-1" width="35">
                    <?= esc($user->username) ?>
                  </td>
                  <td><?= esc($user->email) ?></td>
                  <td>
                    <?php if (!empty($user->groups)): ?>
                      <?php foreach ($user->groups as $group): ?>
                        <?php
                          $badgeClass = match($group) {
                            'superadmin' => 'badge-danger',
                            'admin'      => 'badge-warning',
                            'manager'    => 'badge-info',
                            default      => 'badge-primary',
                          };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($group) ?></span>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span class="badge badge-secondary">No Role</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($user->active): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (activeGroupCan('users.edit')): ?>
                    <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>

                    <?php if (activeGroupCan('users.delete') && $user->id !== auth()->id()): ?>
                      <form action="<?= base_url('admin/users/delete/' . $user->id) ?>" method="post" class="d-inline js-swal-delete-form"
                        data-swal-title="Hapus user?"
                        data-swal-text="User '<?= esc($user->username) ?>' akan dihapus permanen."
                        data-swal-confirm="Ya, hapus"
                        data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center">Belum ada data user.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
