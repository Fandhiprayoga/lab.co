<div class="card">
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/consumables/categories') ?>" id="categoryForm">
      <?= csrf_field() ?>
      
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label>Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" 
                   id="name"
                   name="name" 
                   class="form-control" 
                   value="<?= old('name') ?>" 
                   placeholder="Contoh: Bahan Kimia, Alat Tulis, dll"
                   required 
                   maxlength="100"
                   autofocus>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" 
                   id="sort_order"
                   name="sort_order" 
                   class="form-control" 
                   value="<?= old('sort_order', '0') ?>" 
                   min="0"
                   placeholder="0">
            <small class="form-text text-muted">0 = paling atas</small>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Deskripsi</label>
        <textarea id="description"
                  name="description" 
                  class="form-control" 
                  rows="3"
                  placeholder="Jelaskan kategori ini (opsional)"
                  maxlength="1000"><?= old('description') ?></textarea>
      </div>

      <div class="form-group">
        <div class="custom-control custom-switch">
          <input type="checkbox" 
                 class="custom-control-input" 
                 id="isActive" 
                 name="is_active" 
                 value="1" 
                 <?= old('is_active', '1') ? 'checked' : '' ?>>
          <label class="custom-control-label" for="isActive">Aktif</label>
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <a href="<?= site_url('admin/consumables/categories') ?>" class="btn btn-light">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>
