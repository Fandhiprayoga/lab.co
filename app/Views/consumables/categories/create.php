<div class="card">
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/consumables/categories') ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required maxlength="100">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="sort_order" class="form-control" value="<?= old('sort_order', '0') ?>" min="0">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
      </div>
      <div class="form-group">
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
          <label class="custom-control-label" for="isActive">Aktif</label>
        </div>
      </div>
      <div class="d-flex justify-content-between">
        <a href="<?= site_url('admin/consumables/categories') ?>" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
