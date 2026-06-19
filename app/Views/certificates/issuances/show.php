<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($title) ?></h4>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <tr>
            <th width="150">Kode Sertifikat</th>
            <td><code><?= esc($issuance->cert_code) ?></code></td>
          </tr>
          <tr>
            <th>Template</th>
            <td><?= esc($template->name ?? '—') ?></td>
          </tr>
          <tr>
            <th>Penerima</th>
            <td><?= esc($issuance->recipient_name) ?></td>
          </tr>
          <tr>
            <th>Role</th>
            <td><?= $issuance->recipient_role ? esc($issuance->recipient_role) : '—' ?></td>
          </tr>
          <tr>
            <th>Tanggal Terbit</th>
            <td><?= date('d F Y, H:i', strtotime($issuance->issued_at)) ?></td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <?php if ($issuance->is_revoked): ?>
                <span class="badge badge-danger">Dicabut</span>
                <?php if ($issuance->revoke_reason): ?>
                  <br><small class="text-muted">Alasan: <?= esc($issuance->revoke_reason) ?></small>
                <?php endif; ?>
              <?php else: ?>
                <span class="badge badge-success">Aktif</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ($issuance->notes): ?>
          <tr>
            <th>Catatan</th>
            <td><?= esc($issuance->notes) ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
      <div class="card-footer">
        <a href="<?= base_url('certificates/render/' . $issuance->cert_code) ?>" class="btn btn-warning" target="_blank">
          <i class="fas fa-print"></i> Render & Cetak
        </a>
        <a href="<?= base_url('verify/certificate/' . $issuance->cert_code) ?>" class="btn btn-info" target="_blank">
          <i class="fas fa-link"></i> Verifikasi Publik
        </a>
        <?php if (activeGroupCan('certificate.revoke') && ! $issuance->is_revoked): ?>
          <button class="btn btn-danger" data-toggle="modal" data-target="#revokeModal">
            <i class="fas fa-ban"></i> Cabut Sertifikat
          </button>
        <?php endif; ?>
        <a href="<?= base_url('certificates/issuances') ?>" class="btn btn-secondary">Kembali</a>
      </div>
    </div>
  </div>
</div>

<?php if (activeGroupCan('certificate.revoke') && ! $issuance->is_revoked): ?>
<!-- Revoke Modal -->
<div class="modal fade" id="revokeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="<?= base_url('certificates/issuances/' . $issuance->public_id . '/revoke') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title">Cabut Sertifikat</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p>Anda akan mencabut sertifikat <code><?= esc($issuance->cert_code) ?></code> atas nama <strong><?= esc($issuance->recipient_name) ?></strong>.</p>
          <div class="form-group">
            <label>Alasan Pencabutan</label>
            <textarea name="revoke_reason" class="form-control" rows="2" placeholder="Masukkan alasan pencabutan..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Ya, Cabut</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
