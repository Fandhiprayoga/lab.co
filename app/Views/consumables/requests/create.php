<?php
$labs       = $labs ?? [];
$categories = $categories ?? [];
?>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= site_url('consumables/requests') ?>" id="formCreate">
      <?= csrf_field() ?>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Laboratorium <span class="text-danger">*</span></label>
            <select name="lab_id" class="form-control" required id="labSelect">
              <option value="">— Pilih Lab —</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab['id'] ?>" <?= old('lab_id') == $lab['id'] ? 'selected' : '' ?>>
                  <?= esc($lab['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Tanggal Rencana Penggunaan</label>
            <input type="date" name="scheduled_date" class="form-control" value="<?= old('scheduled_date') ?>">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Tujuan Penggunaan <span class="text-danger">*</span></label>
        <textarea name="purpose" class="form-control" rows="3" required placeholder="Contoh: Praktikum Kimia Dasar semester ganjil…"><?= old('purpose') ?></textarea>
      </div>

      <hr>
      <h6 class="mb-3"><i class="fas fa-flask mr-1 text-primary"></i> Daftar Bahan yang Diminta</h6>

      <!-- Filter bahan per lab -->
      <div class="alert alert-info py-2 px-3 mb-3" id="labHint">
        <i class="fas fa-info-circle mr-1"></i> Pilih laboratorium terlebih dahulu untuk melihat daftar bahan tersedia.
      </div>

      <div id="itemSection" class="d-none">
        <div class="form-group">
          <label>Tambah Bahan</label>
          <div class="input-group">
            <select id="consumableSelect" class="form-control">
              <option value="">— Pilih Bahan —</option>
            </select>
            <div class="input-group-append">
              <button type="button" class="btn btn-secondary" id="addItemBtn">
                <i class="fas fa-plus"></i> Tambah
              </button>
            </div>
          </div>
        </div>

        <table class="table table-sm table-bordered" id="itemTable">
          <thead class="thead-light">
            <tr>
              <th>Bahan</th>
              <th style="width:130px">Jumlah</th>
              <th>Satuan</th>
              <th>Catatan</th>
              <th style="width:40px"></th>
            </tr>
          </thead>
          <tbody id="itemTableBody">
            <tr id="emptyRow"><td colspan="5" class="text-center text-muted">Belum ada item.</td></tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="<?= site_url('consumables/requests') ?>" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Simpan Permintaan
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const labSelect   = document.getElementById('labSelect');
  const itemSection = document.getElementById('itemSection');
  const labHint     = document.getElementById('labHint');
  const consumableSel = document.getElementById('consumableSelect');
  const tbody       = document.getElementById('itemTableBody');
  const emptyRow    = document.getElementById('emptyRow');
  let items = [];
  let availableItems = {};

  labSelect.addEventListener('change', function () {
    const labId = this.value;
    if (! labId) {
      itemSection.classList.add('d-none');
      labHint.classList.remove('d-none');
      return;
    }
    fetch(`<?= site_url('consumables/api/items-by-lab') ?>?lab_id=${labId}`)
      .then(r => r.json())
      .then(data => {
        availableItems = {};
        consumableSel.innerHTML = '<option value="">— Pilih Bahan —</option>';
        data.forEach(d => {
          availableItems[d.id] = d;
          const opt = document.createElement('option');
          opt.value = d.id;
          opt.textContent = `${d.name} (Stok: ${parseFloat(d.stock_available).toFixed(2)} ${d.unit_symbol || ''})`;
          consumableSel.appendChild(opt);
        });
        itemSection.classList.remove('d-none');
        labHint.classList.add('d-none');
      });
  });

  document.getElementById('addItemBtn').addEventListener('click', function () {
    const id = consumableSel.value;
    if (! id || items.some(i => i.id == id)) return;
    const d = availableItems[id];
    items.push({id: d.id, name: d.name, unit: d.unit_symbol || '', maxQty: parseFloat(d.stock_available)});
    renderTable();
    consumableSel.value = '';
  });

  function renderTable () {
    if (items.length === 0) {
      tbody.innerHTML = '';
      tbody.appendChild(emptyRow);
      return;
    }
    const idx = items.length - 1;
    if (emptyRow.parentNode) emptyRow.parentNode.removeChild(emptyRow);
    const rows = items.map((it, i) => `
      <tr>
        <td>${escHtml(it.name)}<input type="hidden" name="items[${i}][consumable_id]" value="${it.id}"></td>
        <td><input type="number" step="0.01" min="0.01" max="${it.maxQty}" name="items[${i}][qty]" class="form-control form-control-sm" value="1" required></td>
        <td>${escHtml(it.unit)}</td>
        <td><input type="text" name="items[${i}][notes]" class="form-control form-control-sm" placeholder="opsional"></td>
        <td><button type="button" class="btn btn-xs btn-danger" data-idx="${i}"><i class="fas fa-times"></i></button></td>
      </tr>`).join('');
    tbody.innerHTML = rows;
    tbody.querySelectorAll('[data-idx]').forEach(btn => {
      btn.addEventListener('click', function () {
        items.splice(parseInt(this.dataset.idx), 1);
        renderTable();
      });
    });
  }

  function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
})();
</script>
