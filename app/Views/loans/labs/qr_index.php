<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $labs = $labs ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>QR Code Ruangan/Lab</h4>
      </div>
      <div class="card-body">
        <p class="text-muted">QR Code dapat dipasang di pintu lab untuk memudahkan akses informasi lab dan proses check-in/out.</p>
        <div class="table-responsive">
          <table id="table-qr" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Nama Lab</th>
                <th>Kode</th>
                <th>Lokasi</th>
                <th>QR Token</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($labs as $lab): ?>
                <tr>
                  <td><?= esc($lab['name']) ?></td>
                  <td><?= esc($lab['code'] ?? '-') ?></td>
                  <td><?= esc($lab['location'] ?? '-') ?></td>
                  <td><code><?= esc(substr((string) ($lab['qr_token'] ?? ''), 0, 12)) ?>...</code></td>
                  <td>
                    <a href="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/qr') ?>" class="btn btn-sm btn-primary" title="Lihat QR"><i class="fas fa-qrcode"></i> Lihat &amp; Cetak</a>
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
    $('#table-qr').DataTable({
      pageLength: 10,
      order: [[0, 'asc']],
      columnDefs: [{ targets: [4], orderable: false, searchable: false }],
      language: {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        emptyTable: 'Belum ada data lab.', zeroRecords: 'Data tidak ditemukan',
        paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
      }
    });
  });
</script>
<?= $this->endSection() ?>
