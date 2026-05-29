<?php $lab = $lab ?? []; ?>
<?php $photos = $photos ?? []; ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Galeri Foto - <?= esc($lab['name']) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/labs') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/photos/upload') ?>" method="post" enctype="multipart/form-data" class="mb-4">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="photos">Upload Foto (bisa pilih banyak sekaligus)</label>
            <input type="file" id="photos" name="photos[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple required>
            <small class="form-text text-muted">Format: PNG/JPG/WEBP, maksimal 5 MB per file. Otomatis di-resize maks 1600px.</small>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Unggah</button>
        </form>

        <?php if (empty($photos)): ?>
          <div class="alert alert-info mb-0">Belum ada foto. Unggah foto pertama menggunakan form di atas.</div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($photos as $photo): ?>
              <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                  <img src="<?= base_url($photo['file_path']) ?>" alt="foto lab" class="card-img-top" style="height: 180px; object-fit: cover;">
                  <div class="card-body p-2">
                    <?php if ((int) $photo['is_primary'] === 1): ?>
                      <span class="badge badge-success mb-2"><i class="fas fa-star"></i> Foto Utama</span>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                      <?php if ((int) $photo['is_primary'] !== 1): ?>
                        <form action="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/photos/' . (int) $photo['id'] . '/primary') ?>" method="post" class="d-inline">
                          <?= csrf_field() ?>
                          <button type="submit" class="btn btn-sm btn-outline-success" title="Jadikan utama"><i class="fas fa-star"></i></button>
                        </form>
                      <?php else: ?>
                        <span></span>
                      <?php endif; ?>
                      <form action="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/photos/' . (int) $photo['id'] . '/delete') ?>" method="post" class="d-inline js-swal-delete-form"
                            data-swal-title="Hapus foto?"
                            data-swal-text="Foto akan dihapus permanen."
                            data-swal-confirm="Ya, hapus"
                            data-swal-cancel="Batal">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
