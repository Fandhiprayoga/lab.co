<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $application->campaign_id ?? '') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Pendaftar:</strong> <?= esc($application->username) ?>
            (<?= esc($application->nim_nik ?? '-') ?>)
          </div>
          <div class="col-md-6">
            <strong>Status:</strong>
            <span class="badge badge-info"><?= esc($application->application_status) ?></span>
          </div>
        </div>

        <?php
        $form = json_decode($application->form_payload ?? '{}');
        ?>
        <div class="row mb-3">
          <div class="col-md-4"><strong>Nama:</strong> <?= esc($form->full_name ?? '-') ?></div>
          <div class="col-md-4"><strong>NIM:</strong> <?= esc($form->nim ?? '-') ?></div>
          <div class="col-md-4"><strong>Prodi:</strong> <?= esc($form->prodi ?? '-') ?></div>
          <div class="col-md-4"><strong>Semester:</strong> <?= esc($form->semester ?? '-') ?></div>
          <div class="col-md-4"><strong>IPK:</strong> <?= esc($form->ipk ?? '-') ?></div>
          <div class="col-md-4"><strong>Telepon:</strong> <?= esc($form->phone ?? '-') ?></div>
        </div>

        <?php if (! empty($form->motivation)): ?>
          <p><strong>Motivasi:</strong> <?= nl2br(esc($form->motivation)) ?></p>
        <?php endif; ?>

        <hr>
        <h5>Dokumen Pendaftaran</h5>

        <?php if (empty($documents)): ?>
          <p class="text-muted">Belum ada dokumen yang diunggah.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Dokumen</th>
                <th>File</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $doc): ?>
                <?php if (in_array($doc->document_type, ['signature', 'passbook_front'])) continue; ?>
              <tr>
                <td><strong><?= esc(ucfirst(str_replace('_', ' ', $doc->document_type))) ?></strong></td>
                <td>
                  <a href="<?= base_url($doc->file_path) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file"></i> <?= esc($doc->file_name) ?>
                  </a>
                </td>
                <td>
                  <?php if ($doc->is_verified): ?>
                    <span class="badge badge-success">Terverifikasi</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Belum Diverifikasi</span>
                  <?php endif; ?>
                  <?php if ($doc->verification_note): ?>
                    <br><small class="text-muted"><?= esc($doc->verification_note) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-success js-verify-btn"
                    data-id="<?= $doc->id ?>" data-type="<?= esc($doc->document_type) ?>" data-action="approve">
                    <i class="fas fa-check"></i> Setujui
                  </button>
                  <button type="button" class="btn btn-sm btn-warning js-verify-btn"
                    data-id="<?= $doc->id ?>" data-type="<?= esc($doc->document_type) ?>" data-action="revision">
                    <i class="fas fa-redo"></i> Revisi
                  </button>
                  <button type="button" class="btn btn-sm btn-danger js-verify-btn"
                    data-id="<?= $doc->id ?>" data-type="<?= esc($doc->document_type) ?>" data-action="reject">
                    <i class="fas fa-times"></i> Tolak
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script>
$(function() {
  var actionMeta = {
    approve:  { title: 'Setujui',  icon: 'success', confirmColor: '#28a745', confirmText: 'Setujui' },
    revision: { title: 'Revisi',   icon: 'warning', confirmColor: '#ffc107', confirmText: 'Minta Revisi' },
    reject:   { title: 'Tolak',    icon: 'error',   confirmColor: '#dc3545', confirmText: 'Tolak' },
  };

  $('.js-verify-btn').click(function() {
    var id     = $(this).data('id');
    var type   = $(this).data('type');
    var action = $(this).data('action');
    var meta   = actionMeta[action];

    Swal.fire({
      title: meta.title + ' Dokumen ' + type.toUpperCase() + '?',
      input: 'textarea',
      inputLabel: 'Catatan (opsional)',
      inputPlaceholder: 'Tulis catatan verifikasi...',
      showCancelButton: true,
      confirmButtonText: meta.confirmText,
      cancelButtonText: 'Batal',
      confirmButtonColor: meta.confirmColor,
      reverseButtons: true,
    }).then(function(result) {
      if (!result.isConfirmed) return;

      $.post('<?= base_url('oprek/verify/document') ?>/' + id, {
        action: action,
        verification_note: result.value || '',
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      })
      .done(function() {
        Swal.fire({ title: 'Tersimpan!', text: 'Verifikasi berhasil disimpan.', icon: 'success', timer: 1500, showConfirmButton: false })
          .then(function() { location.reload(); });
      })
      .fail(function() {
        Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan. Coba lagi.', icon: 'error' });
      });
    });
  });
});
</script>
<?= $this->endSection() ?>
