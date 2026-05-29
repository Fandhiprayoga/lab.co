<?php $category = $category ?? []; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Edit Kategori Alat</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/asset-categories/update/' . (int) ($category['id'] ?? 0)) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name', $category['name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= old('description', $category['description'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="sort_order">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="<?= old('sort_order', (string) ($category['sort_order'] ?? '0')) ?>" class="form-control">
            <small class="form-text text-muted">Semakin kecil nilainya, semakin atas pada dropdown kategori alat.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', (string) ($category['is_active'] ?? '1')) === '1' ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Kategori Aktif</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/asset-categories') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
