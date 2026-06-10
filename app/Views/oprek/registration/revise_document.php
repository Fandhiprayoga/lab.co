<div class="row">
  <div class="col-lg-6 offset-lg-3">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/my-applications/' . $application->public_id) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          Laboran meminta Anda untuk mengunggah ulang dokumen <strong><?= esc(ucfirst($documentType)) ?></strong>.
        </div>

        <?php if ($document->verification_note): ?>
          <div class="alert alert-light">
            <strong>Catatan Laboran:</strong> <?= esc($document->verification_note) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('oprek/my-applications/' . $application->public_id . '/revise/' . $documentType . '/store') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Upload <?= esc(ucfirst($documentType)) ?> Baru <span class="text-danger">*</span></label>
            <input type="file" name="document_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png" required>
            <small class="text-muted">Format: PDF, JPG, PNG | Maks 5MB</small>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Unggah Ulang
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
