<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Satuan</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/units/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Satuan
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-units" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Simbol</th>
                <th>Urutan</th>
                <th>Dipakai Alat</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($units)): ?>
                <?php foreach ($units as $unit): ?>
                <tr>
                  <td><?= esc($unit['name']) ?></td>
                  <td><?= esc($unit['symbol']) ?></td>
                  <td><?= (int) $unit['sort_order'] ?></td>
                  <td><?= (int) ($unit['usage_total'] ?? 0) ?></td>
                  <td>
                    <?php if ((int) $unit['is_active'] === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/units/edit/' . $unit['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/units/delete/' . $unit['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus satuan?"
                          data-swal-text="Satuan '<?= esc($unit['name']) ?>' akan dihapus permanen."
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
    $('#table-units').DataTable({
      pageLength: 10,
      order: [[2, 'asc'], [0, 'asc']],
      columnDefs: [
        { targets: [5], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data satuan.',
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
