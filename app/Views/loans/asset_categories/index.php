<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Kategori Alat</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/asset-categories/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kategori
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-asset-categories" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Urutan</th>
                <th>Dipakai Alat</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                  <td><?= esc($category['name']) ?></td>
                  <td><?= esc($category['description'] ?? '-') ?></td>
                  <td><?= (int) $category['sort_order'] ?></td>
                  <td><?= (int) ($category['usage_total'] ?? 0) ?></td>
                  <td>
                    <?php if ((int) $category['is_active'] === 1): ?>
                      <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Nonaktif</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= base_url('admin/loans/asset-categories/edit/' . $category['id']) ?>" class="btn btn-sm btn-info" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="<?= base_url('admin/loans/asset-categories/delete/' . $category['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus kategori?"
                          data-swal-text="Kategori '<?= esc($category['name']) ?>' akan dihapus permanen."
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
    $('#table-asset-categories').DataTable({
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
        emptyTable: 'Belum ada data kategori alat.',
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
