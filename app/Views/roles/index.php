<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Role</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Role</th>
                <th>Deskripsi</th>
                <th>Permissions</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($groups as $key => $group): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td>
                  <?php
                    $badgeClass = match($key) {
                      'superadmin' => 'badge-danger',
                      'laboran'    => 'badge-warning',
                      'asisten'    => 'badge-info',
                      'kepala_lab' => 'badge-success',
                      'dosen'      => 'badge-primary',
                      default      => 'badge-secondary',
                    };
                  ?>
                  <span class="badge <?= $badgeClass ?>"><?= esc($group['title']) ?></span>
                </td>
                <td><?= esc($group['description']) ?></td>
                <td>
                  <?php if (isset($matrix[$key])): ?>
                    <?php foreach ($matrix[$key] as $perm): ?>
                      <span class="badge badge-light mr-1 mb-1"><?= esc($perm) ?></span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="text-muted">Tidak ada permission</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
