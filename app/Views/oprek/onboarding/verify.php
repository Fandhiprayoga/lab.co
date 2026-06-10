<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $application->campaign_id) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Asisten:</strong> <?= esc($application->username) ?> (<?= esc($application->nim_nik ?? '-') ?>)
          </div>
          <div class="col-md-6">
            <strong>Status:</strong>
            <span class="badge badge-<?= ($profile->onboarding_status ?? '') === 'verified' ? 'success' : 'warning' ?>">
              <?= esc($profile->onboarding_status ?? 'pending') ?>
            </span>
          </div>
        </div>

        <h5>Data Rekening</h5>
        <table class="table table-sm table-bordered">
          <tr><th width="150">Bank</th><td><?= esc($profile->bank_name ?? '-') ?></td></tr>
          <tr><th>No. Rekening</th><td><strong><?= esc($profile->bank_account_number ?? '-') ?></strong></td></tr>
          <tr><th>Nama Pemilik</th><td><?= esc($profile->bank_account_name ?? '-') ?></td></tr>
        </table>

        <h5 class="mt-4">Dokumen</h5>
        <table class="table table-sm">
          <thead><tr><th>Jenis</th><th>File</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($documents as $doc): ?>
              <?php if (! in_array($doc->document_type, ['signature', 'passbook_front'])) continue; ?>
            <tr>
              <td><?= $doc->document_type === 'signature' ? 'Tanda Tangan Digital' : 'Buku Tabungan' ?></td>
              <td>
                <a href="<?= base_url($doc->file_path) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-file"></i> <?= esc($doc->file_name) ?>
                </a>
              </td>
              <td>
                <?= $doc->is_verified ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-secondary">-</span>' ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if (($profile->onboarding_status ?? '') === 'submitted' || ($profile->onboarding_status ?? '') === 'pending'): ?>
        <hr>
        <div>
          <form method="post" action="<?= base_url('oprek/onboarding/' . $application->public_id . '/verify/store') ?>" class="form-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="verified">
            <button type="submit" class="btn btn-success mr-2" onclick="return confirm('Verifikasi data onboarding?')">
              <i class="fas fa-check"></i> Verifikasi
            </button>
          </form>
          <form method="post" action="<?= base_url('oprek/onboarding/' . $application->public_id . '/verify/store') ?>" class="form-inline mt-2">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="revision">
            <div class="form-group mr-2">
              <textarea name="note" class="form-control" rows="2" placeholder="Catatan revisi (opsional)" style="width:300px"></textarea>
            </div>
            <button type="submit" class="btn btn-warning" onclick="return confirm('Minta revisi data onboarding?')">
              <i class="fas fa-redo"></i> Minta Revisi
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
