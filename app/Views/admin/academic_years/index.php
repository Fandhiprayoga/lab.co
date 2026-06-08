<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Tahun Akademik</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/academic-years/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Tahun Akademik
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-academic-years" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($academicYears)): ?>
                <?php foreach ($academicYears as $ta): ?>
                <tr>
                  <td><?= esc($ta->kode_ta) ?></td>
                  <td><?= esc($ta->nama_ta) ?></td>
                  <td><?= esc($ta->tanggal_mulai) ?></td>
                  <td><?= esc($ta->tanggal_selesai) ?></td>
                  <td>
                    <?php if ((int) $ta->is_active === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Tidak Aktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/academic-years/edit/' . $ta->id) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ((int) $ta->is_active !== 1): ?>
                      <button type="button" class="btn btn-sm btn-success js-activate-btn"
                        data-id="<?= $ta->id ?>"
                        data-nama="<?= esc($ta->nama_ta) ?>"
                        title="Aktifkan">
                        <i class="fas fa-check-circle"></i>
                      </button>
                      <form action="<?= base_url('admin/academic-years/delete/' . $ta->id) ?>" method="post" class="d-inline js-swal-delete-form"
                            data-swal-title="Hapus tahun akademik?"
                            data-swal-text="Data '<?= esc($ta->nama_ta) ?>' akan dihapus permanen."
                            data-swal-confirm="Ya, hapus"
                            data-swal-cancel="Batal">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                      </form>
                    <?php endif; ?>
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

<form id="activate-form" method="post" style="display:none;">
  <?= csrf_field() ?>
</form>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(function () {
    $('#table-academic-years').DataTable({
      pageLength: 10,
      order: [[0, 'desc']],
      columnDefs: [
        { targets: [5], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data tahun akademik.',
        zeroRecords: 'Data tidak ditemukan',
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: 'Berikutnya',
          previous: 'Sebelumnya'
        }
      }
    });

    $('.js-activate-btn').on('click', function () {
      const id = $(this).data('id');
      const nama = $(this).data('nama');

      Swal.fire({
        title: 'PERHATIAN!',
        html: `Anda mau mengaktifkan <strong>${nama}</strong>.<br><br>
               Tahun akademik yang aktif saat ini akan otomatis dinonaktifkan.<br>
               Semua data akan beralih ke tahun akademik baru.<br><br>
               Yakin?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Aktifkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = $('#activate-form');
          form.attr('action', `<?= base_url('admin/academic-years/activate') ?>/${id}`);
          form.submit();
        }
      });
    });
  });
</script>
<?= $this->endSection() ?>
