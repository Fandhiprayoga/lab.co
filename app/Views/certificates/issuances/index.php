<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('certificates/issuances/create') ?>" class="btn btn-primary">
            <i class="fas fa-certificate"></i> Terbitkan Sertifikat
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <form method="get" class="form-inline mb-3">
          <select name="template_id" class="form-control form-control-sm mr-2">
            <option value="">Semua Template</option>
            <?php foreach ($templates as $t): ?>
            <option value="<?= $t->id ?>" <?= $filterTemplateId == $t->id ? 'selected' : '' ?>><?= esc($t->name) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="status" class="form-control form-control-sm mr-2">
            <option value="">Semua Status</option>
            <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="revoked" <?= $filterStatus === 'revoked' ? 'selected' : '' ?>>Dicabut</option>
          </select>
          <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-filter"></i> Filter</button>
          <a href="<?= base_url('certificates/issuances') ?>" class="btn btn-sm btn-light ml-1">Reset</a>
        </form>

        <?php if (empty($issuances)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-certificate fa-3x mb-3"></i>
            <p>Belum ada sertifikat yang diterbitkan.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped" id="table-issuances">
              <thead>
                <tr>
                  <th>Kode Sertifikat</th>
                  <th>Template</th>
                  <th>Penerima</th>
                  <th>Role</th>
                  <th>Tanggal Terbit</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($issuances as $i): ?>
                <tr>
                  <td><code><?= esc($i->cert_code) ?></code></td>
                  <td><?= esc($i->template_name) ?></td>
                  <td><?= esc($i->recipient_name) ?></td>
                  <td><?= $i->recipient_role ? '<span class="badge badge-light">' . esc($i->recipient_role) . '</span>' : '—' ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($i->issued_at)) ?></td>
                  <td>
                    <?= $i->is_revoked ? '<span class="badge badge-danger">Dicabut</span>' : '<span class="badge badge-success">Aktif</span>' ?>
                  </td>
                  <td>
                    <a href="<?= base_url('certificates/issuances/' . $i->public_id) ?>" class="btn btn-sm btn-info" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?= base_url('certificates/render/' . $i->cert_code) ?>" class="btn btn-sm btn-warning" target="_blank" title="Render">
                      <i class="fas fa-print"></i>
                    </a>
                    <a href="<?= base_url('verify/certificate/' . $i->cert_code) ?>" class="btn btn-sm btn-secondary" target="_blank" title="Verifikasi Publik">
                      <i class="fas fa-link"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  $('#table-issuances').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
    order: [[4, 'desc']],
  });
});
</script>
<?= $this->endSection() ?>
