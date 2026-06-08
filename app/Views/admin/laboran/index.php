<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card card-primary">
      <div class="card-header">
        <h4><i class="fas fa-info-circle mr-1"></i> Tentang Menu Ini</h4>
      </div>
      <div class="card-body">
        <p class="mb-0">
          Menu <strong>Manajemen Laboran</strong> digunakan oleh Super Admin untuk mengelola user
          yang memiliki role <strong>Laboran</strong> dan menentukan lab tempat mereka bekerja.
          Setiap laboran bisa ditugaskan ke <strong>lebih dari satu lab</strong>.
        </p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-users mr-1"></i> Daftar Laboran</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/laboran/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Laboran
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-laboran" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Lab Tugas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script>
  $(function () {
    $('#table-laboran').DataTable({
      serverSide : true,
      processing : true,
      pageLength : 10,
      order      : [[0, 'asc']],
      ajax: {
        url : '<?= base_url('admin/laboran/datatable') ?>'
      },
      columnDefs: [
        { targets: [2, 3, 4], orderable: false, searchable: false }
      ],
      language: {
        search      : 'Cari:',
        lengthMenu  : 'Tampilkan _MENU_ data',
        info        : 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
        infoEmpty   : 'Tidak ada data',
        emptyTable  : 'Belum ada data laboran.',
        zeroRecords : 'Data tidak ditemukan.',
        processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
        paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
      }
    });
  });
</script>
<?= $this->endSection() ?>
