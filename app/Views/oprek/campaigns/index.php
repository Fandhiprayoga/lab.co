<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?php
$activeLab = $activeLab ?? null;
?>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buka Oprek Baru
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (isset($activeLab) && $activeLab): ?>
        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
          <div>
            <i class="fas fa-filter mr-1"></i>
            Menampilkan data oprek untuk lab:
            <strong><?= esc($activeLab['name']) ?></strong>
          </div>
          <form action="<?= site_url('switch-lab') ?>" method="post" style="margin:0;">
            <input type="hidden" name="lab_id" value="0">
            <!-- <button type="submit" class="btn btn-sm btn-light">
              <i class="fas fa-times mr-1"></i> Reset Filter
            </button> -->
          </form>
        </div>
        <?php endif; ?>

        <?php if (empty($campaigns)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>Belum ada oprek. Klik tombol di atas untuk membuka rekrutmen baru.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped" id="table-campaigns">
              <thead>
                <tr>
                  <th>Periode</th>
                  <th>Lab</th>
                  <th>Tahun Akademik</th>
                  <th>Pendaftaran</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($campaigns as $c): ?>
                <tr>
                  <td>
                    <a href="<?= base_url('oprek/' . $c->id) ?>"><?= esc($c->period_name) ?></a>
                  </td>
                  <td><?= esc($c->lab_name) ?></td>
                  <td><?= esc($c->nama_ta) ?></td>
                  <td>
                    <?php if ($c->registration_start_at && $c->registration_end_at): ?>
                      <?= date('d/m/Y', strtotime($c->registration_start_at)) ?> - <?= date('d/m/Y', strtotime($c->registration_end_at)) ?>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                    $statusBadge = match ($c->status) {
                      'draft'    => 'badge-secondary',
                      'published' => 'badge-success',
                      'closed'   => 'badge-warning',
                      'archived' => 'badge-dark',
                      default    => 'badge-light',
                    };
                    ?>
                    <span class="badge <?= $statusBadge ?>"><?= esc($c->status) ?></span>
                  </td>
                  <td>
                    <a href="<?= base_url('oprek/' . $c->id) ?>" class="btn btn-sm btn-primary" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <?php if ($c->status === 'draft'): ?>
                      <a href="<?= base_url('oprek/' . $c->id . '/edit') ?>" class="btn btn-sm btn-info" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button class="btn btn-sm btn-success js-publish-btn" data-id="<?= $c->id ?>" title="Publikasikan">
                        <i class="fas fa-paper-plane"></i>
                      </button>
                    <?php endif; ?>
                    <?php if ($c->status === 'published'): ?>
                      <button class="btn btn-sm btn-warning js-close-btn" data-id="<?= $c->id ?>" title="Tutup">
                        <i class="fas fa-lock"></i>
                      </button>
                    <?php endif; ?>
                    <?php if ($c->status === 'closed'): ?>
                      <button class="btn btn-sm btn-dark js-archive-btn" data-id="<?= $c->id ?>" title="Arsipkan">
                        <i class="fas fa-archive"></i>
                      </button>
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

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
  $('#table-campaigns').DataTable({ pageLength: 25, language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' } });

  $('.js-publish-btn').click(function() {
    if (confirm('Publikasikan oprek ini?')) {
      $.post('<?= base_url('oprek') ?>/' + $(this).data('id') + '/publish', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'})
        .done(function() { location.reload(); });
    }
  });
  $('.js-close-btn').click(function() {
    if (confirm('Tutup oprek ini? Pendaftaran baru tidak akan diterima.')) {
      $.post('<?= base_url('oprek') ?>/' + $(this).data('id') + '/close', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'})
        .done(function() { location.reload(); });
    }
  });
  $('.js-archive-btn').click(function() {
    if (confirm('Arsipkan oprek ini?')) {
      $.post('<?= base_url('oprek') ?>/' + $(this).data('id') + '/archive', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'})
        .done(function() { location.reload(); });
    }
  });
});
</script>
<?= $this->endSection() ?>
