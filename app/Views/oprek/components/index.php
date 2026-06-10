<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $campaign->id) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">

        <div class="alert alert-info">
          Total Bobot: <strong><?= $totalWeight ?>%</strong>
          <?php if ($totalWeight < 100): ?>
            <span class="text-danger">(kurang <?= 100 - $totalWeight ?>% dari 100%)</span>
          <?php elseif ($totalWeight > 100): ?>
            <span class="text-danger">(melebihi 100%)</span>
          <?php else: ?>
            <span class="text-success">(lengkap)</span>
          <?php endif; ?>
        </div>

        <!-- Existing Components -->
        <?php if (! empty($components)): ?>
        <table class="table table-sm table-bordered">
          <thead class="thead-light">
            <tr>
              <th>#</th><th>Komponen</th><th>Key</th><th>Bobot (%)</th><th>Nilai Maks</th><th>Wajib</th><th>Aktif</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($components as $i => $comp): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($comp->component_name) ?></td>
              <td><code><?= esc($comp->component_key) ?></code></td>
              <td><?= $comp->weight_percentage ?>%</td>
              <td><?= $comp->max_score ?></td>
              <td><?= $comp->is_required ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>' ?></td>
              <td><?= $comp->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' ?></td>
              <td>
                <button class="btn btn-xs btn-info js-edit-btn" data-toggle="modal" data-target="#editModal"
                  data-id="<?= $comp->id ?>"
                  data-name="<?= esc($comp->component_name) ?>"
                  data-weight="<?= $comp->weight_percentage ?>"
                  data-max="<?= $comp->max_score ?>"
                  data-required="<?= $comp->is_required ?>"
                  data-active="<?= $comp->is_active ?>">
                  <i class="fas fa-edit"></i>
                </button>
                <a href="<?= base_url('oprek/' . $campaign->id . '/components/' . $comp->id . '/toggle') ?>" class="btn btn-xs btn-<?= $comp->is_active ? 'warning' : 'success' ?>">
                  <?= $comp->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>
                </a>
                <a href="<?= base_url('oprek/' . $campaign->id . '/components/' . $comp->id . '/assessors') ?>" class="btn btn-xs btn-primary">
                  <i class="fas fa-users"></i> Penilai
                </a>
                <a href="<?= base_url('oprek/' . $campaign->id . '/components/' . $comp->id . '/delete') ?>"
                  class="btn btn-xs btn-danger" onclick="return confirm('Hapus komponen ini?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p class="text-muted">Belum ada komponen seleksi.</p>
        <?php endif; ?>

        <!-- Add New Component -->
        <hr>
        <h5>Tambah Komponen Baru</h5>
        <form method="post" action="<?= base_url('oprek/' . $campaign->id . '/components/store') ?>" class="form-inline">
          <?= csrf_field() ?>
          <div class="form-group mr-2 mb-2">
            <input type="text" name="component_name" class="form-control" placeholder="Nama Komponen" required>
          </div>
          <div class="form-group mr-2 mb-2">
            <input type="text" name="component_key" class="form-control" placeholder="Key (contoh: test_tertulis)" required>
          </div>
          <div class="form-group mr-2 mb-2">
            <input type="number" name="weight_percentage" class="form-control" placeholder="Bobot %" step="0.01" min="0" max="100" required style="width:100px">
          </div>
          <div class="form-group mr-2 mb-2">
            <input type="number" name="max_score" class="form-control" placeholder="Nilai Maks" step="0.01" min="0" required style="width:100px" value="100">
          </div>
          <div class="form-check mr-2 mb-2">
            <input type="checkbox" name="is_required" value="1" class="form-check-input" id="newRequired" checked>
            <label class="form-check-label" for="newRequired">Wajib</label>
          </div>
          <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-plus"></i> Tambah</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" id="editForm" action="">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Komponen</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Komponen</label>
            <input type="text" name="component_name" id="editName" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Bobot (%)</label>
            <input type="number" name="weight_percentage" id="editWeight" class="form-control" step="0.01" min="0" max="100" required>
          </div>
          <div class="form-group">
            <label>Nilai Maks</label>
            <input type="number" name="max_score" id="editMax" class="form-control" step="0.01" min="0" required>
          </div>
          <div class="form-check mb-2">
            <input type="checkbox" name="is_required" value="1" id="editRequired" class="form-check-input">
            <label class="form-check-label" for="editRequired">Wajib</label>
          </div>
          <div class="form-check">
            <input type="checkbox" name="is_active" value="1" id="editActive" class="form-check-input">
            <label class="form-check-label" for="editActive">Aktif</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->section('js') ?>
<script>
$(function() {
  $('#editModal').on('show.bs.modal', function(e) {
    var btn = $(e.relatedTarget);
    var id = btn.data('id');
    $('#editForm').attr('action', '<?= base_url('oprek/' . $campaign->id . '/components') ?>/' + id + '/update');
    $('#editName').val(btn.data('name'));
    $('#editWeight').val(btn.data('weight'));
    $('#editMax').val(btn.data('max'));
    $('#editRequired').prop('checked', btn.data('required') == 1);
    $('#editActive').prop('checked', btn.data('active') == 1);
  });
});
</script>
<?= $this->endSection() ?>
