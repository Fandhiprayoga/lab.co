<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Fakultas</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/faculties/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Fakultas
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-faculties" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($faculties)): ?>
                <?php foreach ($faculties as $faculty): ?>
                <tr>
                  <td>
                    <img src="<?= ! empty($faculty['logo']) ? base_url($faculty['logo']) : base_url('assets/img/stisla-fill.svg') ?>" alt="logo fakultas" class="img-thumbnail" style="width: 48px; height: 48px; object-fit: contain;">
                  </td>
                  <td><?= esc($faculty['name']) ?></td>
                  <td><?= esc($faculty['code'] ?? '-') ?></td>
                  <td><?= esc($faculty['description'] ?? '-') ?></td>
                  <td>
                    <?php if ((int) $faculty['is_active'] === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/faculties/edit/' . $faculty['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/faculties/delete/' . $faculty['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus data fakultas?"
                          data-swal-text="Data '<?= esc($faculty['name']) ?>' akan dihapus permanen."
                          data-swal-confirm="Ya, hapus"
                          data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
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
    $('#table-faculties').DataTable({
      pageLength: 10,
      order: [[1, 'asc']],
      columnDefs: [
        { targets: [0, 5], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data fakultas.',
        zeroRecords: 'Data tidak ditemukan',
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: 'Berikutnya',
          previous: 'Sebelumnya'
        }
      }
    });
  });
</script>
<?= $this->endSection() ?>
