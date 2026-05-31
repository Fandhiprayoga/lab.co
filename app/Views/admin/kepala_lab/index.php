<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">

<div class="row">
  <div class="col-12">
    <div class="card card-primary">
      <div class="card-header">
        <h4><i class="fas fa-info-circle mr-1"></i> Tentang Menu Ini</h4>
      </div>
      <div class="card-body">
        <p class="mb-2">
          Menu <strong>Kepala Lab</strong> digunakan oleh Super Admin untuk menetapkan
          <strong>satu</strong> orang Kepala Lab yang bertanggung jawab atas
          <strong>seluruh laboratorium</strong>. Kepala Lab berwenang melakukan
          <em>approval level 2 (L2)</em> pada peminjaman, persetujuan Bahan Habis Pakai,
          serta melihat analitik pemanfaatan lab.
        </p>
        <h6 class="font-weight-bold mb-2">Alur Penetapan</h6>
        <ol class="mb-0 pl-3">
          <li>Super Admin memilih kandidat dari daftar user aktif (Dosen, Laboran, Asisten, atau Super Admin).</li>
          <li>Sistem mencabut jabatan Kepala Lab dari pemegang sebelumnya (jika ada) &mdash; role lainnya tetap dipertahankan.</li>
          <li>Role <strong>Kepala Lab</strong> ditambahkan ke user terpilih sebagai role tambahan.</li>
          <li>User yang baru ditetapkan menerima notifikasi dan langsung memperoleh kewenangan Kepala Lab.</li>
          <li>Setiap perubahan dicatat pada <strong>Riwayat Perubahan</strong> di bawah.</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 col-md-5">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-tie mr-1"></i> Kepala Lab Saat Ini</h4>
      </div>
      <div class="card-body">
        <?php if ($current): ?>
          <div class="d-flex align-items-center">
            <img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mr-3" width="55">
            <div>
              <div class="font-weight-bold h5 mb-1"><?= esc($current['username']) ?></div>
              <div class="text-muted"><?= esc($current['email'] ?? '-') ?></div>
              <span class="badge badge-success mt-1">Kepala Lab</span>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-user-slash fa-2x mb-2"></i>
            <p class="mb-0">Belum ada Kepala Lab yang ditetapkan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-7">
    <div class="card">
      <div class="card-header">
        <h4>Tetapkan Kepala Lab</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-1"></i>
          Hanya boleh ada <strong>satu</strong> Kepala Lab yang mengepalai seluruh lab.
          Menetapkan Kepala Lab baru akan otomatis mencabut jabatan dari pemegang sebelumnya
          (role lainnya tetap dipertahankan).
        </div>

        <form action="<?= base_url('admin/kepala-lab/assign') ?>" method="post"
              class="js-swal-confirm-form"
              data-swal-title="Tetapkan Kepala Lab?"
              data-swal-text="Pemegang Kepala Lab sebelumnya (jika ada) akan otomatis dicabut."
              data-swal-icon="question"
              data-swal-confirm="Ya, tetapkan"
              data-swal-cancel="Batal"
              data-swal-confirm-color="#28a745">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="user_id">Pilih User <span class="text-danger">*</span></label>
            <select name="user_id" id="user_id" class="form-control select2" required>
              <option value="">-- Pilih kandidat --</option>
              <?php foreach ($candidates as $c): ?>
                <option value="<?= (int) $c['id'] ?>"
                  <?= ($current && (int) $current['id'] === (int) $c['id']) ? 'disabled' : '' ?>>
                  <?= esc($c['username']) ?> (<?= esc($c['email'] ?? '-') ?>)
                  <?= ($current && (int) $current['id'] === (int) $c['id']) ? ' — Kepala Lab saat ini' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">
              Kandidat: user aktif dengan role Dosen, Laboran, Asisten, atau Super Admin.
              Mahasiswa &amp; Alumni tidak dapat ditetapkan.
            </small>
          </div>

          <button type="submit" class="btn btn-success" <?= empty($candidates) ? 'disabled' : '' ?>>
            <i class="fas fa-user-check mr-1"></i> Tetapkan sebagai Kepala Lab
          </button>
        </form>

        <?php if (empty($candidates)): ?>
          <p class="text-muted mt-3 mb-0">Tidak ada kandidat yang memenuhi syarat.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-history mr-1"></i> Riwayat Perubahan Kepala Lab</h4>
      </div>
      <div class="card-body">
        <?php if (empty($history)): ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-clock fa-2x mb-2"></i>
            <p class="mb-0">Belum ada riwayat perubahan.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped table-sm mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Waktu</th>
                  <th>Aksi</th>
                  <th>User</th>
                  <th>Oleh</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($history as $h): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc(date('d M Y H:i', strtotime($h['created_at']))) ?></td>
                    <td>
                      <?php if ($h['action'] === 'assigned'): ?>
                        <span class="badge badge-success"><i class="fas fa-user-check mr-1"></i>Ditetapkan</span>
                      <?php else: ?>
                        <span class="badge badge-secondary"><i class="fas fa-user-minus mr-1"></i>Dicabut</span>
                      <?php endif; ?>
                    </td>
                    <td><?= esc($h['target_username'] ?? '—') ?></td>
                    <td><?= esc($h['actor_username'] ?? 'Sistem') ?></td>
                    <td><?= esc($h['note'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var s = document.createElement('script');
  s.src = '<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>';
  s.onload = function () {
    $('#user_id').select2({
      placeholder: '— Pilih kandidat —',
      allowClear: true,
      width: '100%',
    });
  };
  document.body.appendChild(s);
});
</script>

