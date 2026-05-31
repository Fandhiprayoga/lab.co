<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $labs = $labs ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Galeri Foto Lab</h4>
      </div>
      <div class="card-body">
        <p class="text-muted">Kelola foto untuk setiap ruangan/lab. Foto utama akan ditampilkan sebagai representasi lab.</p>
        <div class="table-responsive">
          <table id="table-photos" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Foto Utama</th>
                <th>Nama Lab</th>
                <th>Kode</th>
                <th>Lokasi</th>
                <th class="text-center">Jumlah Foto</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($labs as $lab): ?>
                <tr>
                  <td style="width:80px">
                    <?php if (! empty($lab['primary_photo'])): ?>
                      <img src="<?= base_url($lab['primary_photo']['file_path']) ?>"
                           alt="foto utama"
                           style="width:64px;height:48px;object-fit:cover;border-radius:4px;">
                    <?php else: ?>
                      <div style="width:64px;height:48px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-image text-muted"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?= esc($lab['name']) ?></td>
                  <td><?= esc($lab['code'] ?? '-') ?></td>
                  <td><?= esc($lab['location'] ?? '-') ?></td>
                  <td class="text-center">
                    <?php if ($lab['photo_total'] > 0): ?>
                      <span class="badge badge-primary"><?= $lab['photo_total'] ?></span>
                    <?php else: ?>
                      <span class="badge badge-light text-muted">0</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/photos') ?>"
                       class="btn btn-sm btn-primary"
                       title="Kelola Foto">
                      <i class="fas fa-images"></i> Kelola Foto
                    </a>
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
    $('#table-photos').DataTable({
      pageLength: 25,
      order: [[1, 'asc']],
      columnDefs: [
        { targets: [0, 5], orderable: false, searchable: false }
      ],
      language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 data',
        emptyTable: 'Belum ada data lab.',
        zeroRecords: 'Data tidak ditemukan',
        paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
      }
    });
  });
</script>
<?= $this->endSection() ?>
