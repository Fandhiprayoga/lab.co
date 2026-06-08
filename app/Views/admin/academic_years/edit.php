<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Edit Tahun Akademik</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/academic-years/update/' . (int) ($academicYear->id ?? 0)) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="kode_ta">Kode Tahun Akademik</label>
            <input type="text" id="kode_ta" name="kode_ta" class="form-control" value="<?= old('kode_ta', $academicYear->kode_ta ?? '') ?>" required placeholder="Contoh: 20261 (2026 Ganjil)">
            <small class="form-text text-muted">Format: 4 digit tahun + 1 digit semester (1=Ganjil, 2=Genap, 3=Pendek). Contoh: 20261, 20262, 20263.</small>
          </div>

          <div class="form-group">
            <label for="nama_ta">Nama Tahun Akademik</label>
            <input type="text" id="nama_ta" name="nama_ta" class="form-control" value="<?= old('nama_ta', $academicYear->nama_ta ?? '') ?>" required placeholder="Contoh: 2026/2027 - Ganjil">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="tanggal_mulai">Tanggal Mulai</label>
              <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" value="<?= old('tanggal_mulai', $academicYear->tanggal_mulai ?? '') ?>" required>
            </div>
            <div class="form-group col-md-6">
              <label for="tanggal_selesai">Tanggal Selesai</label>
              <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" value="<?= old('tanggal_selesai', $academicYear->tanggal_selesai ?? '') ?>" required>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/academic-years') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
