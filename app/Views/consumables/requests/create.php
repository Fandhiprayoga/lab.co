<?php
$labs       = $labs ?? [];
$categories = $categories ?? [];
?>

<link rel="stylesheet" href="<?= base_url('assets/modules/select2/dist/css/select2.min.css') ?>">

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
          <div class="row no-gutters align-items-center">
            <div class="col pr-2">
              <select id="consumableSelect" class="form-control">
                <option value="">— Pilih Bahan —</option>
              </select>
            </div>
            <div class="col-auto">
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
          <tbody id="itemTableBody"></tbody>
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
document.addEventListener('DOMContentLoaded', function () {
  var s = document.createElement('script');
  s.src = '<?= base_url('assets/modules/select2/dist/js/select2.min.js') ?>';
  s.onload = function () {

    const itemSection   = document.getElementById('itemSection');
    const labHint       = document.getElementById('labHint');
    const consumableSel = document.getElementById('consumableSelect');
    let items          = [];
    let availableItems = {};

    // --- Select2 ---
    $('#labSelect').select2({
      placeholder: '— Pilih Lab —',
      allowClear: true,
      width: '100%',
    });
    $('#consumableSelect').select2({
      placeholder: '— Pilih Bahan —',
      allowClear: true,
      width: '100%',
    });

    // --- DataTables ---
    const dt = $('#itemTable').DataTable({
      ordering:  false,
      searching: false,
      paging:    false,
      info:      false,
      language:  { emptyTable: 'Belum ada item.' },
    });

    function reindex() {
      dt.rows().nodes().each(function (tr, i) {
        $(tr).find('[data-field="consumable_id"]').attr('name', `items[${i}][consumable_id]`);
        $(tr).find('[data-field="qty"]').attr('name', `items[${i}][qty]`);
        $(tr).find('[data-field="notes"]').attr('name', `items[${i}][notes]`);
      });
    }

    function escHtml(str) {
      return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // --- Lab change: load consumables ---
    $('#labSelect').on('change', function () {
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
          // Clear table items when lab changes
          items = [];
          dt.clear().draw();
          $(consumableSel).val('').trigger('change');
          itemSection.classList.remove('d-none');
          labHint.classList.add('d-none');
        });
    });

    // --- Add item button ---
    document.getElementById('addItemBtn').addEventListener('click', function () {
      const id = $(consumableSel).val();
      if (! id || items.some(i => i.id == id)) return;
      const d    = availableItems[id];
      const item = { id: d.id, name: d.name, unit: d.unit_symbol || '', maxQty: parseFloat(d.stock_available) };
      items.push(item);

      const $tr = $(`<tr>
        <td>${escHtml(item.name)}<input type="hidden" data-field="consumable_id" value="${item.id}"></td>
        <td><input type="number" step="0.01" min="0.01" max="${item.maxQty}" data-field="qty" class="form-control form-control-sm" value="1" required></td>
        <td>${escHtml(item.unit)}</td>
        <td><input type="text" data-field="notes" class="form-control form-control-sm" placeholder="opsional"></td>
        <td><button type="button" class="btn btn-xs btn-danger"><i class="fas fa-times"></i></button></td>
      </tr>`);

      $tr.find('button').on('click', function () {
        items.splice(items.findIndex(i => i.id == item.id), 1);
        dt.row($tr[0]).remove().draw();
        reindex();
      });

      dt.row.add($tr[0]).draw();
      reindex();
      $(consumableSel).val('').trigger('change');
    });

    // Trigger if old value was pre-selected (validation failed)
    if ($('#labSelect').val()) {
      $('#labSelect').trigger('change');
    }
  };
  document.body.appendChild(s);
});
</script>
