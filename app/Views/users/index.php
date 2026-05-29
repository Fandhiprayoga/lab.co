<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
  /* ── Side Drawer ──────────────────────────────────── */
  .user-filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease;
    z-index: 2147483000;
  }
  .user-filter-overlay.is-open {
    opacity: 1;
    visibility: visible;
  }

  .user-filter-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: min(360px, 92vw);
    height: 100vh;
    background: #fff;
    box-shadow: -12px 0 30px rgba(0,0,0,.18);
    transform: translateX(100%);
    transition: transform .25s ease;
    z-index: 2147483010;
    display: flex;
    flex-direction: column;
  }
  .user-filter-drawer.is-open { transform: translateX(0); }

  .user-filter-drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
  }
  .user-filter-drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
  }
  .user-filter-drawer__footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    gap: .5rem;
  }

  /* ── Active filter chips ─────────────────────────── */
  .user-active-filters {
    display: none;
    align-items: center;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
  }
  .user-active-filters.is-visible { display: flex; }

  .user-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .65rem;
    border-radius: 999px;
    background: #f1f3f5;
    color: #343a40;
    font-size: .8rem;
    line-height: 1;
  }
  .user-filter-chip__remove {
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    line-height: 1;
    cursor: pointer;
  }
</style>
<?= $this->endSection() ?>

<!-- ── Main card ─────────────────────────────────────── -->
<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4>Daftar User</h4>
    <div class="card-header-action d-flex" style="gap:.5rem;">
      <button type="button" id="open-user-filter-drawer" class="btn btn-outline-secondary">
        <i class="fas fa-filter"></i> Filter
      </button>
      <?php if (activeGroupCan('users.create')): ?>
      <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah User
      </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body">
    <!-- Active filter chips -->
    <div id="user-active-filters" class="user-active-filters" aria-live="polite">
      <span class="text-muted small mr-1">Filter aktif:</span>
      <div id="user-active-filter-chips" class="d-flex flex-wrap" style="gap:.5rem;"></div>
    </div>

    <div class="table-responsive">
      <table id="table-users" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th width="100">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Drawer overlay ──────────────────────────────── -->
<div id="user-filter-overlay" class="user-filter-overlay"></div>

<!-- ── Side Drawer ────────────────────────────────── -->
<aside id="user-filter-drawer" class="user-filter-drawer" aria-hidden="true">
  <div class="user-filter-drawer__header">
    <h6 class="mb-0"><i class="fas fa-filter mr-2 text-muted"></i>Filter User</h6>
    <button type="button" class="btn btn-sm btn-light" id="close-user-filter-drawer" aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="user-filter-drawer__body">
    <div class="form-group">
      <label for="filter-user-group" class="font-weight-bold small">Role / Group</label>
      <select id="filter-user-group" class="form-control">
        <option value="">Semua Role</option>
        <?php foreach ($groups as $key => $group): ?>
        <option value="<?= esc($key) ?>"><?= esc($group['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group mb-0">
      <label for="filter-user-status" class="font-weight-bold small">Status</label>
      <select id="filter-user-status" class="form-control">
        <option value="">Semua Status</option>
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
      </select>
    </div>
  </div>

  <div class="user-filter-drawer__footer">
    <button type="button" id="reset-user-filter-drawer" class="btn btn-light">Reset</button>
    <button type="button" id="apply-user-filter-drawer" class="btn btn-primary flex-fill">Terapkan Filter</button>
  </div>
</aside>

<?= $this->section('js') ?>
<script>
$(function () {
  var drawer      = $('#user-filter-drawer');
  var overlay     = $('#user-filter-overlay');
  var activeWrap  = $('#user-active-filters');
  var activeChips = $('#user-active-filter-chips');

  // Move to body so it isn't clipped by any ancestor overflow
  if (drawer.parent()[0] !== document.body)  drawer.appendTo('body');
  if (overlay.parent()[0] !== document.body) overlay.appendTo('body');

  /* ── Drawer state ─────────────────────── */
  function setDrawerState(open) {
    drawer.toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
    overlay.toggleClass('is-open', open);
    $('body').toggleClass('overflow-hidden', open);
  }

  /* ── Group labels map ─────────────────── */
  var groupLabels = {};
  <?php foreach ($groups as $key => $group): ?>
  groupLabels['<?= esc($key) ?>'] = '<?= addslashes(esc($group['title'])) ?>';
  <?php endforeach; ?>
  var statusLabels = { '1': 'Aktif', '0': 'Nonaktif' };

  /* ── Filter chip rendering ────────────── */
  function renderChips() {
    var filters = [
      { key: 'group',  label: 'Role',   el: '#filter-user-group',  display: groupLabels },
      { key: 'status', label: 'Status', el: '#filter-user-status', display: statusLabels },
    ].filter(function (f) { return $(f.el).val() !== ''; });

    activeChips.empty();
    if (!filters.length) { activeWrap.removeClass('is-visible'); return; }

    filters.forEach(function (f) {
      var raw     = $(f.el).val();
      var display = f.display ? (f.display[raw] || raw) : raw;
      var chip = $('<span class="user-filter-chip"></span>');
      chip.append(document.createTextNode(f.label + ': ' + display));
      var rm = $('<button type="button" class="user-filter-chip__remove" aria-label="Hapus filter">&times;</button>');
      rm.data('filter-key', f.key);
      chip.append(rm);
      activeChips.append(chip);
    });
    activeWrap.addClass('is-visible');
  }

  /* ── DataTable ────────────────────────── */
  var table = $('#table-users').DataTable({
    serverSide : true,
    processing : true,
    pageLength : 25,
    order      : [[1, 'asc']],
    ajax: {
      url : '<?= base_url('admin/users/datatable') ?>',
      data: function (d) {
        d.filter_group  = $('#filter-user-group').val();
        d.filter_status = $('#filter-user-status').val();
      }
    },
    columnDefs: [
      { targets: [0, 3, 4, 5], orderable: false, searchable: false },
    ],
    drawCallback: function () {
      var api   = this.api();
      var start = api.page.info().start;
      api.column(0).nodes().each(function (td, i) {
        $(td).text(start + i + 1);
      });
    },
    language: {
      search      : 'Cari:',
      lengthMenu  : 'Tampilkan _MENU_ data',
      info        : 'Menampilkan _START_ – _END_ dari _TOTAL_ data',
      infoEmpty   : 'Tidak ada data',
      emptyTable  : 'Belum ada data user.',
      zeroRecords : 'Data tidak ditemukan.',
      processing  : '<div class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</div>',
      paginate    : { first: 'Awal', last: 'Akhir', next: '&rsaquo;', previous: '&lsaquo;' }
    }
  });

  /* ── Events ─────────────────────────────── */
  $('#open-user-filter-drawer').on('click', function () { setDrawerState(true); });
  $('#close-user-filter-drawer, #user-filter-overlay').on('click', function () { setDrawerState(false); });

  $('#apply-user-filter-drawer').on('click', function () {
    table.ajax.reload();
    renderChips();
    setDrawerState(false);
  });

  $('#reset-user-filter-drawer').on('click', function () {
    $('#filter-user-group, #filter-user-status').val('');
    table.ajax.reload();
    renderChips();
  });

  var keyMap = { group: '#filter-user-group', status: '#filter-user-status' };
  activeChips.on('click', '.user-filter-chip__remove', function () {
    var key = $(this).data('filter-key');
    if (keyMap[key]) $(keyMap[key]).val('');
    table.ajax.reload();
    renderChips();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') setDrawerState(false);
  });
});
</script>
<?= $this->endSection() ?>
