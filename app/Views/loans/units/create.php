<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Tambah Satuan</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/units/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="name">Nama Satuan</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name') ?>" required>
            <small class="form-text text-muted">Contoh: Buah, Set, Box, Meter, Liter, Kilogram.</small>
          </div>

          <div class="form-group">
            <label for="symbol">Simbol</label>
            <input type="text" id="symbol" name="symbol" class="form-control" value="<?= old('symbol') ?>" required>
            <small class="form-text text-muted">Contoh: pcs, set, box, m, L, kg.</small>
          </div>

          <div class="form-group">
            <label for="sort_order">Urutan Tampil</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="<?= old('sort_order', '0') ?>" class="form-control">
            <small class="form-text text-muted">Semakin kecil nilainya, semakin atas pada dropdown satuan.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Satuan Aktif</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/units') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Satuan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
