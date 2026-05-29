<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Program Studi</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/study-programs/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Program Studi
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-study-programs" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Logo</th>
                <th>Fakultas</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($studyPrograms)): ?>
                <?php foreach ($studyPrograms as $studyProgram): ?>
                <tr>
                  <td>
                    <img src="<?= ! empty($studyProgram['logo']) ? base_url($studyProgram['logo']) : base_url('assets/img/stisla-fill.svg') ?>" alt="logo program studi" class="img-thumbnail" style="width: 48px; height: 48px; object-fit: contain;">
                  </td>
                  <td><?= esc($studyProgram['faculty_name'] ?? '-') ?></td>
                  <td><?= esc($studyProgram['name']) ?></td>
                  <td><?= esc($studyProgram['code'] ?? '-') ?></td>
                  <td><?= esc($studyProgram['description'] ?? '-') ?></td>
                  <td>
                    <?php if ((int) $studyProgram['is_active'] === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/study-programs/edit/' . $studyProgram['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/study-programs/delete/' . $studyProgram['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus data program studi?"
                          data-swal-text="Data '<?= esc($studyProgram['name']) ?>' akan dihapus permanen."
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
    $('#table-study-programs').DataTable({
      pageLength: 10,
      order: [[2, 'asc']],
      columnDefs: [
        { targets: [0, 6], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data program studi.',
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
