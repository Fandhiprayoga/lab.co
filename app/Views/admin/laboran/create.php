<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<?= $this->endSection() ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-plus mr-1"></i> Tambah Laboran Baru</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/laboran/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="user_id">Pilih User <span class="text-danger">*</span></label>
            <select name="user_id" id="user_id" class="form-control select2" required>
              <option value="">-- Ketik nama/email untuk mencari --</option>
              <?php if (! empty($users)): ?>
                <?php foreach ($users as $user): ?>
                  <option value="<?= (int) $user['id'] ?>"><?= esc($user['username']) ?> (<?= esc($user['email']) ?>)</option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <small class="form-text text-muted">
              Hanya menampilkan user aktif yang <strong>belum</strong> menjadi laboran.
            </small>
          </div>

          <div class="form-group">
            <label>Penugasan Lab <span class="text-danger">*</span></label>
            <div class="row">
              <?php if (! empty($labs)): ?>
                <?php foreach ($labs as $lab): ?>
                <div class="col-md-6 col-lg-4 mb-2">
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="lab_<?= (int) $lab['id'] ?>"
                           name="lab_ids[]" value="<?= (int) $lab['id'] ?>"
                           <?= in_array((int) $lab['id'], (array) old('lab_ids', [])) ? 'checked' : '' ?>>
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
                  <p class="text-muted mb-0">Tidak ada lab aktif. Buat lab terlebih dahulu.</p>
                </div>
              <?php endif; ?>
            </div>
            <small class="form-text text-muted">Pilih minimal satu lab tempat laboran bertugas.</small>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/laboran') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary" <?= empty($labs) ? 'disabled' : '' ?>>
              <i class="fas fa-save mr-1"></i> Simpan Laboran
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>"></script>
<script>
  $(function () {
    console.log('Init Select2 — using query transport');

    // $('#user_id').select2({
    //   placeholder: '-- Ketik nama/email untuk mencari --',
    //   minimumInputLength: 2,
    //   allowClear: true,
    //   width: '100%',
      // query: function (query) {
      //   console.log('Select2 query called:', query.term);
      //   var term = query.term || '';
      //   $.ajax({
      //     url: '<?= base_url('admin/laboran/search-users') ?>',
      //     data: { q: term },
      //     dataType: 'json',
      //     success: function (data) {
      //       console.log('Select2 query response:', data);
      //       query.callback({ results: data.results || [] });
      //     },
      //     error: function (xhr, status, err) {
      //       console.error('Select2 query error:', status, err);
      //       query.callback({ results: [] });
      //     }
      //   });
      // }
    // });
    console.log('Select2 init done');
  });
</script>
<?= $this->endSection() ?>
