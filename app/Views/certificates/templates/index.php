<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('certificates/templates/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Template Baru
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($templates)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-certificate fa-3x mb-3"></i>
            <p>Belum ada template sertifikat. Klik tombol di atas untuk membuat template baru.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped" id="table-templates">
              <thead>
                <tr>
                  <th>Nama Template</th>
                  <th>Orientasi</th>
                  <th>Background</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($templates as $t): ?>
                <tr>
                  <td><?= esc($t->name) ?></td>
                  <td>
                    <?= $t->page_orientation === 'landscape' ? '<span class="badge badge-info">Landscape</span>' : '<span class="badge badge-secondary">Portrait</span>' ?>
                  </td>
                  <td>
                    <?php if ($t->background_path): ?>
                      <span class="text-success"><i class="fas fa-image"></i> Ada</span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= $t->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>' ?>
                  </td>
                  <td>
                    <a href="<?= base_url('certificates/templates/' . $t->public_id . '/components') ?>" class="btn btn-sm btn-info" title="Komponen">
                      <i class="fas fa-puzzle-piece"></i>
                    </a>
                    <a href="<?= base_url('certificates/templates/' . $t->public_id . '/preview') ?>" class="btn btn-sm btn-warning" target="_blank" title="Preview">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="<?= base_url('certificates/templates/' . $t->public_id . '/edit') ?>" class="btn btn-sm btn-primary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($t->is_active): ?>
                    <form action="<?= base_url('certificates/templates/' . $t->public_id . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Nonaktifkan template ini?')">
                      <button class="btn btn-sm btn-danger" title="Nonaktifkan"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
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
  $('#table-templates').DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
    order: [[0, 'asc']],
  });
});
</script>
<?= $this->endSection() ?>
