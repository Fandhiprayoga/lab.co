<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs" id="issueTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="individual-tab" data-toggle="tab" href="#individual" role="tab">Penerima Individu</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="csv-tab" data-toggle="tab" href="#csv" role="tab">Bulk CSV</a>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <!-- Individual Tab -->
          <div class="tab-pane fade show active" id="individual" role="tabpanel">
            <form action="<?= base_url('certificates/issuances/store') ?>" method="post">
              <?= csrf_field() ?>
              <div class="form-group">
                <label>Template Sertifikat <span class="text-danger">*</span></label>
                <select name="template_id" class="form-control" required>
                  <option value="">— Pilih Template —</option>
                  <?php foreach ($templates as $t): ?>
                  <option value="<?= $t->id ?>" <?= old('template_id') == $t->id ? 'selected' : '' ?>><?= esc($t->name) ?> (<?= $t->page_orientation ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Pilih Penerima <span class="text-danger">*</span></label>
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                  <?php if (empty($users)): ?>
                    <p class="text-muted">Tidak ada user dengan role mahasiswa/asisten.</p>
                  <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <div class="custom-control custom-checkbox mb-2">
                      <input type="checkbox" class="custom-control-input" name="recipient_user_id[]"
                             id="user_<?= $u->id ?>" value="<?= $u->id ?>"
                             <?= in_array($u->id, old('recipient_user_id') ?: []) ? 'checked' : '' ?>>
                      <label class="custom-control-label" for="user_<?= $u->id ?>">
                        <?= esc($u->username) ?>
                        <?php if (! empty($u->nim_nik)): ?>
                          <small class="text-muted">(<?= esc($u->nim_nik) ?>)</small>
                        <?php endif; ?>
                      </label>
                      <input type="hidden" name="recipient_name[]" value="<?= esc($u->username) ?>">
                      <input type="hidden" name="recipient_role[]" value="mahasiswa">
                    </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan penerbitan..."><?= old('notes') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary"><i class="fas fa-certificate"></i> Terbitkan Sertifikat</button>
              <a href="<?= base_url('certificates/issuances') ?>" class="btn btn-secondary">Batal</a>
            </form>
          </div>

          <!-- CSV Tab -->
          <div class="tab-pane fade" id="csv" role="tabpanel">
            <form action="<?= base_url('certificates/issuances/bulk-csv') ?>" method="post" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <div class="form-group">
                <label>Template Sertifikat <span class="text-danger">*</span></label>
                <select name="template_id" class="form-control" required>
                  <option value="">— Pilih Template —</option>
                  <?php foreach ($templates as $t): ?>
                  <option value="<?= $t->id ?>"><?= esc($t->name) ?> (<?= $t->page_orientation ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Upload File CSV <span class="text-danger">*</span></label>
                <input type="file" name="csv_file" class="form-control-file" accept=".csv" required>
                <small class="text-muted mt-1 d-block">
                  Format CSV: <code>recipient_name,recipient_role,notes</code><br>
                  Kolom <code>recipient_role</code> dan <code>notes</code> opsional. Baris pertama = header.<br>
                  Contoh: <code>Ahmad Fauzi,mahasiswa,Peserta PKL 2026</code>
                </small>
              </div>

              <div class="form-group">
                <label>Catatan Umum (opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk semua sertifikat..."><?= old('notes') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Proses CSV</button>
              <a href="<?= base_url('certificates/issuances') ?>" class="btn btn-secondary">Batal</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script>
// Keep tab state on validation error
$(function() {
  var hash = window.location.hash;
  if (hash) {
    $('.nav-tabs a[href="' + hash + '"]').tab('show');
  }
  $('.nav-tabs a').on('click', function() {
    window.location.hash = $(this).attr('href');
  });
});
</script>
<?= $this->endSection() ?>
