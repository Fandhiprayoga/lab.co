<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $labs = $labs ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Arsip Ruangan/Lab</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/labs') ?>" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Aktif
          </a>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted">Data lab yang telah diarsipkan. Pulihkan untuk mengembalikan ke daftar aktif, atau hapus permanen jika sudah tidak diperlukan.</p>
        <div class="table-responsive">
          <table id="table-labs-archive" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Lokasi</th>
                <th>Status Kondisi</th>
                <th>Diarsipkan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($labs as $lab): ?>
              <tr>
                <td>
                  <img src="<?= ! empty($lab['logo']) ? base_url($lab['logo']) : base_url('assets/img/stisla-fill.svg') ?>" alt="logo lab" class="img-thumbnail" style="width: 48px; height: 48px; object-fit: contain;">
                </td>
                <td><?= esc($lab['name']) ?></td>
                <td><?= esc($lab['code'] ?? '-') ?></td>
                <td><?= esc($lab['location'] ?? '-') ?></td>
                <td>
                  <?php $condition = (string) ($lab['condition_status'] ?? 'baik'); ?>
                  <?php if ($condition === 'baik'): ?>
                    <span class="badge badge-success">Baik</span>
                  <?php elseif ($condition === 'perlu_perbaikan'): ?>
                    <span class="badge badge-warning">Perlu Perbaikan</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Rusak</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($lab['deleted_at'] ?? '-') ?></td>
                <td>
                  <form action="<?= base_url('admin/loans/labs/restore/' . $lab['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-success" title="Pulihkan"><i class="fas fa-undo"></i> Pulihkan</button>
                  </form>
                  <form action="<?= base_url('admin/loans/labs/force-delete/' . $lab['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                        data-swal-title="Hapus permanen?"
                        data-swal-text="Data '<?= esc($lab['name']) ?>' beserta file logo akan dihapus permanen dan tidak bisa dipulihkan."
                        data-swal-confirm="Ya, hapus permanen"
                        data-swal-cancel="Batal">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Permanen"><i class="fas fa-trash"></i> Hapus Permanen</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    $('#table-labs-archive').DataTable({
      pageLength: 10,
      order: [[5, 'desc']],
      columnDefs: [
        { targets: [0, 6], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data lab di arsip.',
        zeroRecords: 'Data tidak ditemukan',
        paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
      }
    });
  });
</script>
<?= $this->endSection() ?>
