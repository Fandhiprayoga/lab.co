<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

<?php
$lab = $lab ?? ['id' => 0, 'name' => '-', 'code' => null, 'location' => null, 'pic_user_id' => null];
$pic = $pic ?? null;
$candidates = $candidates ?? [];
$history = $history ?? [];
$assignedCount = count(array_filter($history, static fn ($h) => ($h['action'] ?? '') === 'assigned'));
$revokedCount = count(array_filter($history, static fn ($h) => ($h['action'] ?? '') === 'revoked'));
$historyCount = count($history);
?>

<style>
  .lab-pic-hero {
    border: 0;
    background: linear-gradient(140deg, #0f5c88 0%, #1f8bbf 55%, #88c5df 100%);
    color: #fff;
    border-radius: 14px;
    box-shadow: 0 12px 28px rgba(15, 92, 136, 0.25);
  }
  .lab-pic-hero .badge {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.28);
    font-weight: 600;
  }
  .lab-pic-kpi {
    border-radius: 12px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.14);
  }
  .lab-pic-panel {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e8edf3;
  }
  .lab-pic-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(145deg, #f7c948, #f28b3b);
    color: #1d232f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 700;
  }
  .lab-pic-empty-state {
    border: 1px dashed #d4dce5;
    border-radius: 12px;
    background: #f8fafc;
  }
  .lab-pic-table-wrap .dataTables_wrapper .row:first-child {
    margin-bottom: .5rem;
  }
  @media (max-width: 767.98px) {
    .lab-pic-hero .text-right {
      text-align: left !important;
      margin-top: .75rem;
    }
  }
</style>

<div class="card lab-pic-hero mb-4">
  <div class="card-body p-4">
    <div class="row align-items-start">
      <div class="col-12 col-md-8">
        <a href="<?= base_url('admin/lab-pic') ?>" class="btn btn-light btn-sm mb-3">
          <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Lab
        </a>
        <h3 class="mb-1"><i class="fas fa-flask mr-2"></i><?= esc($lab['name']) ?></h3>
        <p class="mb-3">
          Kelola penanggung jawab laboran untuk lab ini, termasuk penetapan baru,
          pencabutan PIC, dan audit riwayat perubahan.
        </p>
        <span class="badge mr-2"><i class="fas fa-barcode mr-1"></i>Kode: <?= esc($lab['code'] ?? '-') ?></span>
        <span class="badge"><i class="fas fa-map-marker-alt mr-1"></i>Lokasi: <?= esc($lab['location'] ?? '-') ?></span>
      </div>
      <div class="col-12 col-md-4 text-right">
        <div class="lab-pic-kpi mb-2">
          <div class="small text-uppercase">Total Riwayat</div>
          <div class="h4 mb-0"><?= $historyCount ?></div>
        </div>
        <div class="d-flex justify-content-end" style="gap:.5rem;">
          <div class="lab-pic-kpi" style="min-width:120px;">
            <div class="small text-uppercase">Assigned</div>
            <div class="h5 mb-0"><?= $assignedCount ?></div>
          </div>
          <div class="lab-pic-kpi" style="min-width:120px;">
            <div class="small text-uppercase">Revoked</div>
            <div class="h5 mb-0"><?= $revokedCount ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 col-lg-4 mb-4">
    <div class="card lab-pic-panel h-100">
      <div class="card-header bg-white border-bottom-0 pb-0">
        <h4 class="mb-0"><i class="fas fa-user-cog mr-1 text-primary"></i> PIC Saat Ini</h4>
      </div>
      <div class="card-body">
        <?php if ($pic): ?>
          <div class="d-flex align-items-center mb-3">
            <div class="lab-pic-avatar mr-3">
              <?= esc(strtoupper(substr((string) $pic->username, 0, 1))) ?>
            </div>
            <div>
              <div class="font-weight-bold"><?= esc($pic->username) ?></div>
              <div class="text-muted small">PIC Laboran aktif untuk lab ini</div>
              <span class="badge badge-info mt-1">Aktif</span>
            </div>
          </div>
        <?php else: ?>
          <div class="lab-pic-empty-state p-3 mb-3">
            <div class="font-weight-bold mb-1"><i class="fas fa-user-slash mr-1 text-muted"></i> Belum ada PIC</div>
            <div class="text-muted small mb-0">Silakan tetapkan laboran agar tanggung jawab operasional lab lebih jelas.</div>
          </div>
        <?php endif; ?>

        <?php if (empty($candidates)): ?>
          <div class="alert alert-warning mb-3">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Tidak ada user aktif ber-role <strong>Laboran</strong>.
          </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/lab-pic/' . (int) $lab['id'] . '/assign') ?>" method="post"
              class="js-swal-confirm-form"
              data-swal-title="Tetapkan PIC?"
              data-swal-text="PIC sebelumnya (jika ada) akan dicabut."
              data-swal-icon="question"
              data-swal-confirm="Ya, tetapkan"
              data-swal-cancel="Batal"
              data-swal-confirm-color="#3abaf4">
          <?= csrf_field() ?>
          <div class="form-group mb-3">
            <label class="font-weight-bold">Pilih Laboran <span class="text-danger">*</span></label>
            <select name="user_id" id="pic_user_id" class="form-control" required <?= empty($candidates) ? 'disabled' : '' ?>>
              <option value="">-- Pilih laboran --</option>
              <?php foreach ($candidates as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= ((int) ($lab['pic_user_id'] ?? 0) === (int) $c['id']) ? 'disabled' : '' ?>>
                  <?= esc($c['username']) ?> (<?= esc($c['email'] ?? '-') ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Hanya user dengan role laboran yang bisa dipilih.</small>
          </div>
          <button type="submit" class="btn btn-primary btn-block" <?= empty($candidates) ? 'disabled' : '' ?>>
            <i class="fas fa-user-check mr-1"></i> Tetapkan PIC
          </button>
        </form>

        <?php if (! empty($lab['pic_user_id'])): ?>
          <form action="<?= base_url('admin/lab-pic/' . (int) $lab['id'] . '/unassign') ?>" method="post"
                class="js-swal-confirm-form mt-2"
                data-swal-title="Cabut PIC?"
                data-swal-text="PIC lab ini akan dikosongkan."
                data-swal-icon="warning"
                data-swal-confirm="Ya, cabut"
                data-swal-cancel="Batal"
                data-swal-confirm-color="#fc544b">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-block">
              <i class="fas fa-user-minus mr-1"></i> Cabut PIC
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8 mb-4">
    <div class="card lab-pic-panel h-100">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h4 class="mb-0"><i class="fas fa-history mr-1 text-primary"></i> Riwayat PIC Lab</h4>
        <span class="badge badge-light border">Total: <?= $historyCount ?></span>
      </div>
      <div class="card-body lab-pic-table-wrap">
        <?php if (empty($history)): ?>
          <div class="text-center text-muted py-5">
            <i class="fas fa-clock fa-2x mb-3"></i>
            <p class="mb-0">Belum ada riwayat penetapan PIC untuk lab ini.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table id="table-lab-pic-history" class="table table-striped table-bordered table-sm mb-0">
              <thead>
                <tr>
                  <th width="45">#</th>
                  <th>Waktu</th>
                  <th>Aksi</th>
                  <th>Laboran</th>
                  <th>Oleh</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($history as $h): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <?php if (! empty($h['created_at']) && strtotime((string) $h['created_at']) !== false): ?>
                        <?= esc(date('d M Y H:i', strtotime((string) $h['created_at']))) ?>
                      <?php else: ?>
                        -
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (($h['action'] ?? '') === 'assigned'): ?>
                        <span class="badge badge-success"><i class="fas fa-user-check mr-1"></i>Ditetapkan</span>
                      <?php else: ?>
                        <span class="badge badge-secondary"><i class="fas fa-user-minus mr-1"></i>Dicabut</span>
                      <?php endif; ?>
                    </td>
                    <td><?= esc($h['target_username'] ?? '-') ?></td>
                    <td><?= esc($h['actor_username'] ?? 'Sistem') ?></td>
                    <td><?= esc($h['note'] ?? '') ?></td>
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

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#pic_user_id').select2({
    placeholder: 'Pilih laboran',
    width: '100%'
  });

  if ($('#table-lab-pic-history').length) {
    $('#table-lab-pic-history').DataTable({
      pageLength: 10,
      order: [[1, 'desc']],
      columnDefs: [
        { targets: [0, 2], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        emptyTable: 'Belum ada riwayat.',
        zeroRecords: 'Data tidak ditemukan.',
        paginate: { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
      }
    });
  }
});
</script>
