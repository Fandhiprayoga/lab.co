<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
      </div>
      <div class="card-body">
        <?php if (empty($certificates)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-certificate fa-3x mb-3"></i>
            <p>Anda belum menerima sertifikat.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped" id="table-my-certs">
              <thead>
                <tr>
                  <th>Kode Sertifikat</th>
                  <th>Template</th>
                  <th>Tanggal Terbit</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($certificates as $c): ?>
                <tr>
                  <td><code><?= esc($c->cert_code) ?></code></td>
                  <td><?= esc($c->template_name) ?></td>
                  <td><?= date('d F Y H:i', strtotime($c->issued_at)) ?></td>
                  <td>
                    <?= $c->is_revoked ? '<span class="badge badge-danger">Dicabut</span>' : '<span class="badge badge-success">Aktif</span>' ?>
                  </td>
                  <td>
                    <a href="<?= base_url('certificates/render/' . $c->cert_code) ?>" class="btn btn-sm btn-warning" target="_blank" title="Cetak">
                      <i class="fas fa-print"></i> Cetak
                    </a>
                    <a href="<?= base_url('verify/certificate/' . $c->cert_code) ?>" class="btn btn-sm btn-info" target="_blank" title="Verifikasi">
                      <i class="fas fa-link"></i> Verifikasi
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

<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function () {
  $('#table-my-certs').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
    order: [[2, 'desc']],
  });
});
</script>
<?= $this->endSection() ?>
