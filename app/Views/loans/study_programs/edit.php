<?php $studyProgram = $studyProgram ?? []; ?>
<?php $faculties = $faculties ?? []; ?>
<?php $logoUrl = ! empty($studyProgram['logo']) ? base_url($studyProgram['logo']) : base_url('assets/img/stisla-fill.svg'); ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4>Form Edit Program Studi</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/study-programs/update/' . (int) ($studyProgram['id'] ?? 0)) ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group text-center">
            <img src="<?= $logoUrl ?>" alt="logo program studi" class="img-thumbnail mb-2" style="width: 120px; height: 120px; object-fit: contain;">
            <small class="d-block text-muted">Jika belum ada logo, sistem menampilkan placeholder default.</small>
          </div>

          <div class="form-group">
            <label for="faculty_id">Fakultas</label>
            <select id="faculty_id" name="faculty_id" class="form-control" required>
              <option value="">- Pilih Fakultas -</option>
              <?php foreach ($faculties as $faculty): ?>
                <?php $selectedFacultyId = old('faculty_id', (string) ($studyProgram['faculty_id'] ?? '')); ?>
                <option value="<?= (int) $faculty['id'] ?>" <?= $selectedFacultyId === (string) $faculty['id'] ? 'selected' : '' ?>>
                  <?= esc($faculty['name']) ?><?= ! empty($faculty['code']) ? ' (' . esc($faculty['code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="name">Nama Program Studi</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= old('name', $studyProgram['name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="code">Kode Program Studi</label>
            <input type="text" id="code" name="code" class="form-control" value="<?= old('code', $studyProgram['code'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= old('description', $studyProgram['description'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label for="study_program_logo">Ganti Foto/Logo Program Studi</label>
            <input type="file" id="study_program_logo" name="study_program_logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <small class="form-text text-muted">Opsional. Format: PNG/JPG/WEBP/SVG, maksimal 2 MB. Untuk PNG/JPG/WEBP sistem akan crop/resize otomatis ke rasio 1:1.</small>
          </div>

          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= old('is_active', (string) ($studyProgram['is_active'] ?? '1')) === '1' ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_active">Program Studi Aktif</label>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= base_url('admin/loans/study-programs') ?>" class="btn btn-light">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
