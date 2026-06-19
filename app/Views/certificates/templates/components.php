<?php
$canvasW = $template->page_orientation === 'landscape' ? 1122 : 794;
$canvasH = $template->page_orientation === 'landscape' ? 794 : 1122;
$componentsJson = [];
foreach ($components as $c) {
    $componentsJson[] = [
        'id'             => (int) $c->id,
        'component_type' => $c->component_type,
        'label'          => $c->label,
        'content'        => $c->content ?? '',
        'x_position'     => (int) $c->x_position,
        'y_position'     => (int) $c->y_position,
        'width'          => $c->width ? (int) $c->width : null,
        'font_size'      => (int) $c->font_size,
        'font_color'     => $c->font_color,
        'font_family'    => $c->font_family,
        'font_weight'    => $c->font_weight,
        'text_align'     => $c->text_align,
        'sort_order'     => (int) $c->sort_order,
    ];
}
$updateUrl = base_url('certificates/templates/' . $template->public_id . '/components');
$csrfName  = csrf_token();
$csrfHash  = csrf_hash();
?>
<style>
.cert-canvas-wrap {
    background: #e0e0e0;
    overflow: auto;
    padding: 20px;
}
.cert-canvas {
    position: relative;
    overflow: hidden;
    margin: 0 auto;
    <?php if ($template->background_path): ?>
    background-image: url('<?= base_url($template->background_path) ?>');
    background-size: 100% 100%;
    background-position: center;
    <?php endif; ?>
    background-color: #fff;
}
.cert-comp {
    position: absolute;
    white-space: pre-wrap;
    word-wrap: break-word;
    cursor: move;
    border: 1px dashed transparent;
    transition: border-color 0.15s;
    user-select: none;
}
.cert-comp:hover {
    border-color: rgba(103,119,239,0.5);
}
.cert-comp.selected {
    border-color: #6777ef !important;
    z-index: 10 !important;
    background: rgba(103,119,239,0.08);
}
.cert-comp.dragging {
    opacity: 0.85;
    border-color: #fc544b !important;
    z-index: 99 !important;
}
.cert-comp-pos {
    position: absolute;
    top: -18px;
    left: 2px;
    font-size: 10px;
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 1px 5px;
    border-radius: 3px;
    white-space: nowrap;
    pointer-events: none;
    display: none;
}
.cert-comp:hover .cert-comp-pos,
.cert-comp.selected .cert-comp-pos,
.cert-comp.dragging .cert-comp-pos {
    display: block;
}
</style>

<div class="row">
  <!-- LEFT PANEL -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Komponen</h4>
      </div>
      <div class="card-body">
        <div id="comp-list">
          <?php if (empty($components)): ?>
            <div class="text-center py-4 text-muted" id="comp-empty">
              <p>Belum ada komponen. Tambahkan di bawah.</p>
            </div>
          <?php endif; ?>
          <table class="table table-sm table-striped" id="comp-table" style="<?= empty($components) ? 'display:none' : '' ?>">
            <thead>
              <tr><th>Label</th><th>Tipe</th><th>Posisi</th><th>Aksi</th></tr>
            </thead>
            <tbody id="comp-tbody">
              <?php foreach ($components as $c): ?>
              <tr data-id="<?= $c->id ?>" class="comp-row">
                <td><?= esc($c->label) ?></td>
                <td><span class="badge badge-light"><?= esc($c->component_type) ?></span></td>
                <td class="comp-pos"><?= $c->x_position ?>, <?= $c->y_position ?></td>
                <td>
                  <button class="btn btn-sm btn-info comp-select-btn" data-id="<?= $c->id ?>" title="Pilih di canvas">
                    <i class="fas fa-mouse-pointer"></i>
                  </button>
                  <button class="btn btn-sm btn-warning comp-edit-btn" data-id="<?= $c->id ?>" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <form action="<?= base_url('certificates/templates/' . $template->public_id . '/components/' . $c->id . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus komponen ini?')">
                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <hr>
        <h5 id="form-title">Tambah Komponen</h5>
        <form id="component-form" method="post" action="<?= base_url('certificates/templates/' . $template->public_id . '/components/store') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="component_id" id="component_id" value="">

          <div class="form-group">
            <label>Tipe Komponen</label>
            <select name="component_type" id="component_type" class="form-control">
              <option value="recipient_name">Nama Penerima</option>
              <option value="cert_number">Nomor Sertifikat</option>
              <option value="issued_date">Tanggal Terbit</option>
              <option value="title">Judul Sertifikat</option>
              <option value="custom_text">Teks Kustom</option>
            </select>
          </div>

          <div class="form-group">
            <label>Label / Nama Internal <span class="text-danger">*</span></label>
            <input type="text" name="label" id="label" class="form-control" required maxlength="100"
                   placeholder="Contoh: Nama Penerima">
          </div>

          <div class="form-group" id="group-content">
            <label>Konten / Teks Default</label>
            <textarea name="content" id="content" class="form-control" rows="2"
                      placeholder="Untuk tipe title / custom_text, masukkan teks di sini"></textarea>
            <small class="text-muted">Tipe dinamis (nama, no, tanggal) diabaikan saat render.</small>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>X Position (px) <span class="text-info" id="x-live"></span></label>
              <input type="number" name="x_position" id="x_position" class="form-control" value="0">
            </div>
            <div class="form-group col-md-4">
              <label>Y Position (px) <span class="text-info" id="y-live"></span></label>
              <input type="number" name="y_position" id="y_position" class="form-control" value="0">
            </div>
            <div class="form-group col-md-4">
              <label>Width (px)</label>
              <input type="number" name="width" id="width" class="form-control" placeholder="Auto">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Font Size</label>
              <input type="number" name="font_size" id="font_size" class="form-control" value="16">
            </div>
            <div class="form-group col-md-4">
              <label>Warna</label>
              <input type="color" name="font_color" id="font_color" class="form-control" value="#000000">
            </div>
            <div class="form-group col-md-4">
              <label>Font Weight</label>
              <select name="font_weight" id="font_weight" class="form-control">
                <option value="normal">Normal</option>
                <option value="bold">Bold</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Text Align</label>
            <select name="text_align" id="text_align" class="form-control">
              <option value="center">Center</option>
              <option value="left">Left</option>
              <option value="right">Right</option>
            </select>
          </div>

          <div class="form-group">
            <label>Urutan (sort_order)</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
          </div>

          <button type="submit" id="form-submit" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Komponen</button>
          <button type="button" id="form-cancel" class="btn btn-secondary d-none" onclick="resetForm()">Batal Edit</button>
          <small class="text-muted ml-2 d-none" id="drag-hint"><i class="fas fa-arrows-alt"></i> Drag komponen di canvas untuk atur posisi</small>
        </form>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL — Live Canvas -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Preview — Drag untuk atur posisi</h4>
        <div>
          <button class="btn btn-sm btn-outline-secondary" id="btn-zoom-out" title="Zoom out">−</button>
          <span class="mx-1 text-muted" id="zoom-label">100%</span>
          <button class="btn btn-sm btn-outline-secondary" id="btn-zoom-in" title="Zoom in">+</button>
          <button class="btn btn-sm btn-outline-secondary ml-2" id="btn-zoom-fit" title="Fit">Fit</button>
          <a href="<?= base_url('certificates/templates/' . $template->public_id . '/preview') ?>" class="btn btn-sm btn-info ml-2" target="_blank">
            <i class="fas fa-external-link-alt"></i> Print Preview
          </a>
        </div>
      </div>
      <div class="card-body p-0 cert-canvas-wrap" id="canvas-wrap">
        <div class="cert-canvas" id="cert-canvas" style="width:<?= $canvasW ?>px;height:<?= $canvasH ?>px;">
          <?php foreach ($components as $c): ?>
          <?php
            $text = match($c->component_type) {
              'recipient_name' => 'Nama Penerima',
              'cert_number'    => 'CERT-20260618000000-ABCDEF',
              'issued_date'    => date('d F Y'),
              'title'          => $c->content,
              'custom_text'    => $c->content,
              default          => $c->content ?? '',
            };
            $wStyle = $c->width ? 'width:' . (int) $c->width . 'px;' : '';
          ?>
          <div class="cert-comp" data-id="<?= $c->id ?>" style="
            left:<?= (int) $c->x_position ?>px;
            top:<?= (int) $c->y_position ?>px;
            <?= $wStyle ?>
            font-size:<?= (int) $c->font_size ?>px;
            color:<?= esc($c->font_color) ?>;
            font-family:<?= esc($c->font_family) ?>;
            font-weight:<?= esc($c->font_weight) ?>;
            text-align:<?= esc($c->text_align) ?>;
          "><span class="cert-comp-pos"><?= $c->x_position ?>,<?= $c->y_position ?></span><?= esc($text) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── DATA ──
const componentsData = <?= json_encode($componentsJson) ?>;
const publicId = <?= json_encode($template->public_id) ?>;
const updateUrl = <?= json_encode($updateUrl) ?>;
const csrfName = <?= json_encode($csrfName) ?>;
const csrfHash = <?= json_encode($csrfHash) ?>;
let selectedId = null;
let zoomLevel = 1.0;

// ── DOM REFS ──
const canvas = document.getElementById('cert-canvas');
const canvasWrap = document.getElementById('canvas-wrap');
const zoomLabel = document.getElementById('zoom-label');

// ── HELPER: get component data ──
function getComp(id) {
    return componentsData.find(c => c.id === id);
}

function getCompIndex(id) {
    return componentsData.findIndex(c => c.id === id);
}

// ── RENDER single component on canvas ──
function renderComp(c) {
    let el = canvas.querySelector('.cert-comp[data-id="' + c.id + '"]');
    if (!el) {
        el = document.createElement('div');
        el.className = 'cert-comp';
        el.setAttribute('data-id', c.id);
        const posSpan = document.createElement('span');
        posSpan.className = 'cert-comp-pos';
        el.appendChild(posSpan);
        canvas.appendChild(el);
    }
    const labelMap = {
        recipient_name: 'Nama Penerima',
        cert_number: 'CERT-20260618000000-ABCDEF',
        issued_date: '<?= date('d F Y') ?>',
        title: c.content,
        custom_text: c.content,
    };
    const text = labelMap[c.component_type] || (c.content || '');
    const wStyle = c.width ? 'width:' + c.width + 'px;' : '';
    el.style.cssText = 'left:' + c.x_position + 'px;' +
        'top:' + c.y_position + 'px;' +
        wStyle +
        'font-size:' + c.font_size + 'px;' +
        'color:' + c.font_color + ';' +
        'font-family:' + c.font_family + ';' +
        'font-weight:' + c.font_weight + ';' +
        'text-align:' + c.text_align + ';';
    if (el.childNodes.length > 1) {
        el.childNodes[1].nodeValue = text;
    } else if (el.childNodes[0] && el.childNodes[0].className === 'cert-comp-pos') {
        // only pos span exists, append text
        el.appendChild(document.createTextNode(text));
    }
    const posSpan = el.querySelector('.cert-comp-pos');
    if (posSpan) posSpan.textContent = c.x_position + ',' + c.y_position;
}

// ── SELECT component ──
function selectComponent(id) {
    // Deselect previous
    if (selectedId !== null) {
        const prev = canvas.querySelector('.cert-comp[data-id="' + selectedId + '"]');
        if (prev) prev.classList.remove('selected');
    }
    selectedId = id;
    if (id !== null) {
        const el = canvas.querySelector('.cert-comp[data-id="' + id + '"]');
        if (el) el.classList.add('selected');
    }
    // Highlight row
    document.querySelectorAll('.comp-row').forEach(r => r.classList.remove('table-primary'));
    if (id !== null) {
        const row = document.querySelector('.comp-row[data-id="' + id + '"]');
        if (row) row.classList.add('table-primary');
    }
}

// ── Sync component data → form ──
function populateForm(c) {
    document.getElementById('form-title').innerText = 'Edit Komponen';
    document.getElementById('component-form').action = updateUrl + '/' + c.id + '/update';
    document.getElementById('component_id').value = c.id;
    document.getElementById('component_type').value = c.component_type;
    document.getElementById('label').value = c.label;
    document.getElementById('content').value = c.content || '';
    document.getElementById('x_position').value = c.x_position;
    document.getElementById('y_position').value = c.y_position;
    document.getElementById('width').value = c.width || '';
    document.getElementById('font_size').value = c.font_size;
    document.getElementById('font_color').value = c.font_color;
    document.getElementById('font_weight').value = c.font_weight;
    document.getElementById('text_align').value = c.text_align;
    document.getElementById('sort_order').value = c.sort_order;
    document.getElementById('form-submit').innerHTML = '<i class="fas fa-save"></i> Perbarui Komponen';
    document.getElementById('form-cancel').classList.remove('d-none');
    document.getElementById('drag-hint').classList.remove('d-none');
}

// ── AJAX save position ──
function savePosition(id, x, y) {
    const body = new URLSearchParams();
    body.append(csrfName, csrfHash);
    body.append('x_position', x);
    body.append('y_position', y);
    fetch(updateUrl + '/' + id + '/ajax-update', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
    }).then(r => r.json()).then(json => {
        if (json.ok && json.csrf_hash) {
            // Refresh CSRF token
            const csrfInput = document.querySelector('input[name="' + csrfName + '"]');
            if (csrfInput) csrfInput.value = json.csrf_hash;
        }
    }).catch(() => {});
}

// ── DRAG LOGIC ──
let dragState = null;

canvas.addEventListener('mousedown', function(e) {
    const comp = e.target.closest('.cert-comp');
    if (!comp) {
        // Click outside → deselect
        selectComponent(null);
        resetForm();
        return;
    }
    const id = parseInt(comp.getAttribute('data-id'));
    e.preventDefault();

    dragState = {
        id: id,
        el: comp,
        startX: e.clientX,
        startY: e.clientY,
        origLeft: parseInt(comp.style.left) || 0,
        origTop: parseInt(comp.style.top) || 0,
    };
    comp.classList.add('dragging');
    selectComponent(id);
});

window.addEventListener('mousemove', function(e) {
    if (!dragState) return;
    const dx = (e.clientX - dragState.startX) / zoomLevel;
    const dy = (e.clientY - dragState.startY) / zoomLevel;
    const newX = Math.max(0, Math.round(dragState.origLeft + dx));
    const newY = Math.max(0, Math.round(dragState.origTop + dy));
    dragState.el.style.left = newX + 'px';
    dragState.el.style.top = newY + 'px';
    // Live update form
    document.getElementById('x_position').value = newX;
    document.getElementById('y_position').value = newY;
    document.getElementById('x-live').textContent = '(live: ' + newX + ')';
    document.getElementById('y-live').textContent = '(live: ' + newY + ')';
    // Live update position label on canvas
    const posSpan = dragState.el.querySelector('.cert-comp-pos');
    if (posSpan) posSpan.textContent = newX + ',' + newY;
});

window.addEventListener('mouseup', function() {
    if (!dragState) return;
    const el = dragState.el;
    const id = dragState.id;
    const newX = parseInt(el.style.left) || 0;
    const newY = parseInt(el.style.top) || 0;
    el.classList.remove('dragging');

    // Update data
    const c = getComp(id);
    if (c) {
        c.x_position = newX;
        c.y_position = newY;
    }

    // Update table row
    const row = document.querySelector('.comp-row[data-id="' + id + '"]');
    if (row) {
        const posCell = row.querySelector('.comp-pos');
        if (posCell) posCell.textContent = newX + ', ' + newY;
    }

    // Sync form if this component is selected
    if (selectedId === id) {
        document.getElementById('x_position').value = newX;
        document.getElementById('y_position').value = newY;
        document.getElementById('x-live').textContent = '';
        document.getElementById('y-live').textContent = '';
    }

    // Auto-save to server
    savePosition(id, newX, newY);

    dragState = null;
});

// ── TABLE BUTTONS: select / edit ──
document.addEventListener('click', function(e) {
    // "Pilih di canvas" button
    const selBtn = e.target.closest('.comp-select-btn');
    if (selBtn) {
        const id = parseInt(selBtn.getAttribute('data-id'));
        selectComponent(id);
        return;
    }
    // "Edit" button
    const editBtn = e.target.closest('.comp-edit-btn');
    if (editBtn) {
        const id = parseInt(editBtn.getAttribute('data-id'));
        const c = getComp(id);
        if (c) {
            selectComponent(id);
            populateForm(c);
        }
        return;
    }
});

// ── CLICK on canvas component = edit ──
canvas.addEventListener('click', function(e) {
    // Ignore after drag (dragState already cleared in mouseup)
    if (dragState) return;
    const comp = e.target.closest('.cert-comp');
    if (comp) {
        const id = parseInt(comp.getAttribute('data-id'));
        const c = getComp(id);
        if (c) {
            selectComponent(id);
            populateForm(c);
        }
    }
});

// ── ZOOM ──
function applyZoom() {
    canvas.style.transform = 'scale(' + zoomLevel + ')';
    canvas.style.transformOrigin = 'top left';
    canvasWrap.scrollTop = 0;
    canvasWrap.scrollLeft = 0;
    zoomLabel.textContent = Math.round(zoomLevel * 100) + '%';
}

document.getElementById('btn-zoom-in').addEventListener('click', function() {
    zoomLevel = Math.min(2.0, zoomLevel + 0.15);
    applyZoom();
});
document.getElementById('btn-zoom-out').addEventListener('click', function() {
    zoomLevel = Math.max(0.3, zoomLevel - 0.15);
    applyZoom();
});
document.getElementById('btn-zoom-fit').addEventListener('click', function() {
    const wrapW = canvasWrap.clientWidth - 40;
    const wrapH = canvasWrap.clientHeight - 40;
    const w = <?= $canvasW ?>;
    const h = <?= $canvasH ?>;
    zoomLevel = Math.min(wrapW / w, wrapH / h, 1.0);
    applyZoom();
});

// Auto-fit on load
setTimeout(function() {
    document.getElementById('btn-zoom-fit').click();
}, 200);

// ── FORM HELPERS ──
function editComponent(c) {
    // Legacy compatibility — called from table buttons or programmatically
    selectComponent(c.id);
    populateForm(c);
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Tambah Komponen';
    document.getElementById('component-form').action = updateUrl + '/store';
    document.getElementById('component_id').value = '';
    document.getElementById('component-form').reset();
    document.getElementById('font_color').value = '#000000';
    document.getElementById('form-submit').innerHTML = '<i class="fas fa-plus"></i> Tambah Komponen';
    document.getElementById('form-cancel').classList.add('d-none');
    document.getElementById('drag-hint').classList.add('d-none');
    document.getElementById('x-live').textContent = '';
    document.getElementById('y-live').textContent = '';
    selectComponent(null);
}

// ── FORM X/Y manual input = update canvas live ──
['x_position', 'y_position'].forEach(function(field) {
    document.getElementById(field).addEventListener('input', function() {
        if (selectedId === null) return;
        const c = getComp(selectedId);
        if (!c) return;
        const val = parseInt(this.value) || 0;
        if (field === 'x_position') c.x_position = val;
        else c.y_position = val;
        renderComp(c);
        // Update table row
        const row = document.querySelector('.comp-row[data-id="' + selectedId + '"]');
        if (row) {
            row.querySelector('.comp-pos').textContent = c.x_position + ', ' + c.y_position;
        }
    });
});
</script>
