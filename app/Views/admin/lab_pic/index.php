<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

<?php $candidates = $candidates ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card card-primary">
      <div class="card-header">
        <h4><i class="fas fa-info-circle mr-1"></i> Tentang Menu Ini</h4>
      </div>
      <div class="card-body">
        <p class="mb-0">
          Menu <strong>PIC Laboran</strong> digunakan oleh Kepala Lab atau Super Admin untuk menetapkan
          <strong>satu Laboran penanggung jawab</strong> (PIC) pada setiap laboratorium.
          Kandidat hanya user aktif dengan role <strong>Laboran</strong>. Setiap perubahan
          dicatat pada riwayat masing-masing lab yang bisa dibuka via tombol <em>Riwayat</em>.
        </p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-cog mr-1"></i> Daftar Lab &amp; PIC</h4>
      </div>
      <div class="card-body">
        <?php if (empty($labs)): ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-flask fa-2x mb-2"></i>
            <p class="mb-0">Belum ada data lab.</p>
          </div>
        <?php else: ?>
          <?php if (empty($candidates)): ?>
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Tidak ada user aktif ber-role <strong>Laboran</strong>. Tambahkan laboran terlebih dahulu.
            </div>
          <?php endif; ?>
          <div class="table-responsive">
            <table id="table-lab-pic" class="table table-striped table-vcenter mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Lab</th>
                  <th>Kode</th>
                  <th>PIC Saat Ini</th>
                  <th style="min-width:260px;">Tetapkan PIC</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($labs as $lab): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td class="font-weight-bold"><?= esc($lab['name']) ?></td>
                    <td><?= esc($lab['code'] ?? '-') ?></td>
                    <td>
                      <?php if (! empty($lab['pic_username'])): ?>
                        <span class="badge badge-info"><i class="fas fa-user mr-1"></i><?= esc($lab['pic_username']) ?></span>
                      <?php else: ?>
                        <span class="text-muted">Belum ada</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form action="<?= base_url('admin/lab-pic/' . (int) $lab['id'] . '/assign') ?>" method="post"
                            class="js-swal-confirm-form d-flex"
                            data-swal-title="Tetapkan PIC?"
                            data-swal-text="PIC sebelumnya (jika ada) akan dicabut."
                            data-swal-icon="question"
                            data-swal-confirm="Ya, tetapkan"
                            data-swal-cancel="Batal"
                            data-swal-confirm-color="#3abaf4">
                        <?= csrf_field() ?>
                        <select name="user_id" class="form-control form-control-sm select2-pic mr-2" required <?= empty($candidates) ? 'disabled' : '' ?>>
                          <option value="">-- Pilih laboran --</option>
                          <?php foreach ($candidates as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"
                              <?= ((int) ($lab['pic_user_id'] ?? 0) === (int) $c['id']) ? 'disabled' : '' ?>>
                              <?= esc($c['username']) ?> (<?= esc($c['email'] ?? '-') ?>)
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary" <?= empty($candidates) ? 'disabled' : '' ?>>
                          <i class="fas fa-user-check"></i>
                        </button>
                      </form>
                    </td>
                    <td class="text-right">
                      <a href="<?= base_url('admin/lab-pic/' . (int) $lab['id']) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-history mr-1"></i> Riwayat
                      </a>
                      <?php if (! empty($lab['pic_user_id'])): ?>
                        <form action="<?= base_url('admin/lab-pic/' . (int) $lab['id'] . '/unassign') ?>" method="post"
                              class="d-inline js-swal-confirm-form"
                              data-swal-title="Cabut PIC?"
                              data-swal-text="PIC lab ini akan dikosongkan."
                              data-swal-icon="warning"
                              data-swal-confirm="Ya, cabut"
                              data-swal-cancel="Batal"
                              data-swal-confirm-color="#fc544b">
                          <?= csrf_field() ?>
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-user-minus"></i>
                          </button>
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

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#table-lab-pic').DataTable({
    pageLength: 10,
    order: [[1, 'asc']],
    columnDefs: [
      { targets: [0, 4, 5], orderable: false, searchable: false }
    ],
    language: {
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
      infoEmpty: 'Tidak ada data',
      emptyTable: 'Belum ada data lab.',
      zeroRecords: 'Data tidak ditemukan.',
      paginate: { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  var s = document.createElement('script');
  s.src = '<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>';
  s.onload = function () {
    $('.select2-pic').select2({
      placeholder: '— Pilih laboran —',
      width: '100%',
    });
  };
  document.body.appendChild(s);
});
</script>
