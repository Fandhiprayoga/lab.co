<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<?= $this->endSection() ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-edit mr-1"></i> Edit Penugasan Lab — <?= esc($user->username) ?></h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-1"></i>
          Ubah penugasan lab untuk laboran <strong><?= esc($user->username) ?></strong>.
          Perubahan hanya memengaruhi lab tempat laboran bertugas, tidak menghapus role laboran.
        </div>

        <form action="<?= base_url('admin/laboran/update/' . (int) $user->id) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Penugasan Lab</label>
            <div class="row">
              <?php if (! empty($labs)): ?>
                <?php foreach ($labs as $lab): ?>
                <div class="col-md-6 col-lg-4 mb-2">
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="lab_<?= (int) $lab['id'] ?>"
                           name="lab_ids[]" value="<?= (int) $lab['id'] ?>"
                           <?= in_array((int) $lab['id'], $assignedLabIds) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="lab_<?= (int) $lab['id'] ?>">
                      <?= esc($lab['name']) ?>
                      <?php if (! empty($lab['code'])): ?>
                        <small class="text-muted">(<?= esc($lab['code']) ?>)</small>
                      <?php endif; ?>
                    </label>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-12">
                  <p class="text-muted mb-0">Tidak ada lab aktif.</p>
                </div>
              <?php endif; ?>
            </div>
            <small class="form-text text-muted">Kosongkan semua jika laboran tidak ditugaskan ke lab mana pun.</small>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/laboran') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
