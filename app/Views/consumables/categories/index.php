<?php
$categories = $categories ?? [];
?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Kategori Bahan Habis Pakai</h4>
    <div class="card-header-action">
      <a href="<?= site_url('admin/consumables/categories/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Tambah Kategori
      </a>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="categoriesTable" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="50">#</th>
            <th>Nama Kategori</th>
            <th>Deskripsi</th>
            <th width="100" class="text-center">Jumlah Bahan</th>
            <th width="80" class="text-center">Urutan</th>
            <th width="100" class="text-center">Status</th>
            <th width="120"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
          <?php else: ?>
            <?php foreach ($categories as $i => $cat): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <strong><?= esc($cat['name']) ?></strong>
              </td>
              <td>
                <?php if (!empty($cat['description'])): ?>
                  <span class="d-block text-truncate" style="max-width: 350px;" title="<?= esc($cat['description']) ?>">
                    <?= esc($cat['description']) ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge badge-light"><?= (int)$cat['item_total'] ?></span>
              </td>
              <td class="text-center"><?= (int)$cat['sort_order'] ?></td>
              <td class="text-center">
                <?php if ($cat['is_active']): ?>
                  <span class="badge badge-success">Aktif</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td class="text-right" style="white-space:nowrap">
                <a href="<?= site_url('admin/consumables/categories/' . $cat['id'] . '/edit') ?>" 
                   class="btn btn-xs btn-light" 
                   title="Edit">
                  <i class="fas fa-pencil-alt"></i>
                </a>
                <?php if ((int)$cat['item_total'] === 0): ?>
                <button type="button" 
                        class="btn btn-xs btn-danger btn-delete" 
                        data-id="<?= $cat['id'] ?>"
                        data-name="<?= esc($cat['name']) ?>"
                        title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>
                <?php else: ?>
                <button type="button" 
                        class="btn btn-xs btn-secondary" 
                        title="Tidak bisa dihapus (masih digunakan)"
                        disabled>
                  <i class="fas fa-lock"></i>
                </button>
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

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize DataTables
  const table = $('#categoriesTable').DataTable({
    pageLength: 25,
    language: {
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data per halaman',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ kategori',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(difilter dari _MAX_ total data)',
      paginate: {
        first: 'Pertama',
        last: 'Terakhir',
        next: 'Selanjutnya',
        previous: 'Sebelumnya'
      },
      zeroRecords: 'Tidak ada data yang ditemukan'
    },
    order: [[4, 'asc'], [1, 'asc']], // Sort by sort_order then name
    columnDefs: [
      { orderable: false, targets: [6] } // Disable sorting on action column
    ]
  });

  // Delete confirmation with SweetAlert
  $('#categoriesTable').on('click', '.btn-delete', function() {
    const categoryId = $(this).data('id');
    const categoryName = $(this).data('name');
    
    Swal.fire({
      title: 'Hapus Kategori?',
      html: 'Yakin ingin menghapus kategori <strong>' + categoryName + '</strong>?<br><small class="text-muted">Tindakan ini tidak bisa dibatalkan.</small>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        const form = $('<form>', {
          method: 'POST',
          action: '<?= site_url('admin/consumables/categories') ?>/' + categoryId + '/delete'
        });
        form.append($('<input>', {
          type: 'hidden',
          name: '<?= csrf_token() ?>',
          value: '<?= csrf_hash() ?>'
        }));
        $('body').append(form);
        form.submit();
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
