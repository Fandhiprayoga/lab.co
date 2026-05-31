<?php
$currentUser  = auth()->user();
$currentUrl   = current_url();
$userGroups   = $currentUser->getGroups();
$active       = activeGroup();
$authGroups   = config('AuthGroups');

// Badge color per group
$badgeColors = [
    'superadmin' => 'danger',
    'laboran'    => 'warning',
    'asisten'    => 'info',
    'kepala_lab' => 'success',
    'dosen'      => 'primary',
    'mahasiswa'  => 'secondary',
];
?>
<nav class="navbar navbar-expand-lg main-navbar">
  <form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
      <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
      <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
    </ul>
    <div class="search-element" style="position:relative;">
      <input
        id="navbar-menu-search"
        class="form-control"
        type="search"
        placeholder="Cari menu…"
        aria-label="Cari menu"
        autocomplete="off"
        data-width="250"
      >
      <button class="btn" type="button" id="navbar-menu-search-btn"><i class="fas fa-search"></i></button>
      <div class="search-backdrop"></div>

      <!-- Dropdown hasil pencarian menu -->
      <div
        id="navbar-menu-search-dropdown"
        class="dropdown-menu shadow-sm"
        style="display:none; position:absolute; top:calc(100% + 4px); left:0; min-width:260px; z-index:9999; max-height:320px; overflow-y:auto;"
      ></div>
    </div>
  </form>

  <script>
  (function () {
    var input    = document.getElementById('navbar-menu-search');
    var dropdown = document.getElementById('navbar-menu-search-dropdown');
    var activeIdx = -1;
    var items    = [];
    var debounce;
    var baseSearch = '<?= base_url('menu-search') ?>';

    if (!input || !dropdown) return;

    function renderItems(data) {
      items = data;
      activeIdx = -1;
      dropdown.innerHTML = '';

      if (data.length === 0) {
        dropdown.innerHTML = '<span class="dropdown-item text-muted small">Tidak ada menu ditemukan</span>';
        dropdown.style.display = 'block';
        return;
      }

      data.forEach(function (item, i) {
        var a = document.createElement('a');
        a.className = 'dropdown-item d-flex align-items-center';
        a.href = item.url;
        a.setAttribute('data-index', i);
        a.innerHTML =
          '<i class="' + escHtml(item.icon) + ' mr-2 text-muted" style="width:16px;text-align:center;"></i>' +
          '<span>' + escHtml(item.label) + '</span>';
        dropdown.appendChild(a);
      });

      dropdown.style.display = 'block';
    }

    function escHtml(str) {
      return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function closeDropdown() {
      dropdown.style.display = 'none';
      dropdown.innerHTML = '';
      items = [];
      activeIdx = -1;
    }

    function setActive(idx) {
      var links = dropdown.querySelectorAll('a.dropdown-item');
      links.forEach(function (el) { el.classList.remove('active'); });
      if (idx >= 0 && idx < links.length) {
        links[idx].classList.add('active');
        links[idx].scrollIntoView({ block: 'nearest' });
        activeIdx = idx;
      }
    }

    input.addEventListener('input', function () {
      clearTimeout(debounce);
      var q = input.value.trim();
      if (q.length < 1) { closeDropdown(); return; }

      debounce = setTimeout(function () {
        fetch(baseSearch + '?q=' + encodeURIComponent(q), {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (r) { return r.json(); })
          .then(renderItems)
          .catch(function () { closeDropdown(); });
      }, 200);
    });

    input.addEventListener('keydown', function (e) {
      var links = dropdown.querySelectorAll('a.dropdown-item');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setActive(Math.min(activeIdx + 1, links.length - 1));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setActive(Math.max(activeIdx - 1, 0));
      } else if (e.key === 'Enter') {
        if (activeIdx >= 0 && links[activeIdx]) {
          e.preventDefault();
          window.location.href = links[activeIdx].href;
        } else if (items.length === 1) {
          e.preventDefault();
          window.location.href = items[0].url;
        }
      } else if (e.key === 'Escape') {
        closeDropdown();
      }
    });

    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        closeDropdown();
      }
    });

    // Prevent form submit
    input.closest('form').addEventListener('submit', function (e) {
      e.preventDefault();
    });
  })();
  </script>
  <ul class="navbar-nav navbar-right">

    <!-- Group Switcher -->
    <?php if (count($userGroups) > 1): ?>
    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg">
        <i class="fas fa-user-shield"></i>
        <span class="badge badge-<?= $badgeColors[$active] ?? 'secondary' ?>">
          <?= esc($authGroups->groups[$active]['title'] ?? ucfirst($active)) ?>
        </span>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <div class="dropdown-title">Switch Role</div>
        <?php foreach ($userGroups as $grp): ?>
          <?php if ($grp === $active): ?>
            <span class="dropdown-item active disabled">
              <i class="fas fa-check mr-1"></i>
              <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
            </span>
          <?php else: ?>
            <form action="<?= base_url('switch-group') ?>" method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="group" value="<?= $grp ?>">
              <button type="submit" class="dropdown-item">
                <i class="far fa-circle mr-1"></i>
                <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
              </button>
            </form>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </li>
    <?php endif; ?>

    <!-- Notification Bell -->
    <li class="nav-item dropdown">
      <a href="#" class="nav-link nav-link-lg notif-bell-btn" data-toggle="dropdown" id="notif-toggle" aria-haspopup="true" aria-expanded="false">
        <i class="far fa-bell"></i>
        <span class="notif-count-badge d-none" id="notif-badge"></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right" id="notif-panel">
        <div class="notif-panel-head">
          <span class="notif-panel-title">Notifikasi</span>
          <a href="#" id="notif-mark-all-read" class="notif-mark-all-btn">Tandai semua dibaca</a>
        </div>
        <div id="notif-list" class="notif-scroll-body">
          <div class="notif-state-msg"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat...</div>
        </div>
        <div class="notif-panel-foot">
          <a href="<?= base_url('notifications') ?>">Lihat semua notifikasi &rarr;</a>
        </div>
      </div>
    </li>

    <script>
    (function () {
      var notifToggle = document.getElementById('notif-toggle');
      var notifList   = document.getElementById('notif-list');
      var notifBadge  = document.getElementById('notif-badge');
      var markAllBtn  = document.getElementById('notif-mark-all-read');
      var baseUrl     = '<?= base_url() ?>';

      function escHtml(str) {
        return String(str)
          .replace(/&/g, '&amp;').replace(/</g, '&lt;')
          .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }

      function timeAgo(dateStr) {
        if (!dateStr) return '';
        var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)    return diff + 'd lalu';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + 'j lalu';
        return Math.floor(diff / 86400) + ' hari lalu';
      }

      function iconStyle(color) {
        var map = {
          primary:   'background:#ede9fe;color:#7c3aed',
          success:   'background:#d1fae5;color:#059669',
          warning:   'background:#fef3c7;color:#d97706',
          danger:    'background:#fee2e2;color:#dc2626',
          info:      'background:#dbeafe;color:#2563eb',
          secondary: 'background:#f3f4f6;color:#6b7280'
        };
        return map[color] || map.secondary;
      }

      function renderNotifs(items) {
        if (!items || items.length === 0) {
          notifList.innerHTML = '<div class="notif-state-msg">Tidak ada notifikasi</div>';
          return;
        }

        var html = '';
        items.forEach(function (n) {
          var unreadCls = n.is_read ? '' : 'notif-unread';
          var itemUrl   = n.url || (baseUrl + 'notifications');
          var dot       = n.is_read ? '' : '<span class="notif-unread-pip"></span>';
          html += '<a href="' + escHtml(itemUrl) + '" class="notif-row ' + unreadCls + '" data-id="' + escHtml(n.id) + '" data-read="' + (n.is_read ? '1' : '0') + '">';
          html += '  <div class="notif-row-icon" style="' + iconStyle(n.color) + '"><i class="' + escHtml(n.icon) + '"></i></div>';
          html += '  <div class="notif-row-body">';
          html += '    <div class="notif-row-title">' + escHtml(n.title) + '</div>';
          html += '    <div class="notif-row-msg">'   + escHtml(n.message) + '</div>';
          html += '    <div class="notif-row-time">'  + timeAgo(n.created_at) + '</div>';
          html += '  </div>';
          html += dot;
          html += '</a>';
        });

        notifList.innerHTML = html;
      }

      function fetchRecent() {
        fetch(baseUrl + 'notifications/recent', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (r) { return r.json(); })
          .then(renderNotifs)
          .catch(function () {
            notifList.innerHTML = '<div class="notif-state-msg">Gagal memuat notifikasi</div>';
          });
      }

      // Klik pada item notifikasi → mark-read, lalu navigate
      if (notifList) {
        notifList.addEventListener('click', function (e) {
          var item = e.target.closest('.notif-row');
          if (!item) return;
          if (item.dataset.read === '0') {
            var id = item.dataset.id;
            fetch(baseUrl + 'notifications/' + id + '/read', {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
              }
            }).catch(function () {});
          }
        });
      }

      // Buka dropdown → muat notifikasi (native click, tanpa jQuery)
      if (notifToggle) {
        notifToggle.addEventListener('click', function () {
          // parentElement = <li class="nav-item dropdown">
          var menu = notifToggle.parentElement.querySelector('.dropdown-menu');
          if (menu && !menu.classList.contains('show')) {
            // Dropdown akan DIBUKA — reset & ambil data
            notifList.innerHTML = '<div class="notif-state-msg"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat...</div>';
            fetchRecent();
          }
        });
      }

      // Tandai semua dibaca
      if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          fetch(baseUrl + 'notifications/read-all', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
          })
            .then(function () {
              if (notifBadge) { notifBadge.classList.add('d-none'); notifBadge.textContent = '0'; }
              fetchRecent();
            })
            .catch(function () {});
        });
      }
    })();
    </script>

    <!-- User Menu -->
    <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
      <img alt="image" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mr-1">
      <div class="d-sm-none d-lg-inline-block">Hi, <?= esc($currentUser->username ?? 'User') ?></div></a>
      <div class="dropdown-menu dropdown-menu-right">
        <div class="dropdown-title">Logged in as <?= esc($currentUser->username ?? 'User') ?></div>
        <?php if (count($userGroups) === 1): ?>
        <div class="dropdown-item disabled text-muted">
          <i class="fas fa-user-shield"></i> Role: <span class="badge badge-<?= $badgeColors[$active] ?? 'secondary' ?>"><?= esc(activeGroupTitle()) ?></span>
        </div>
        <?php endif; ?>
        <a href="<?= base_url('profile') ?>" class="dropdown-item has-icon">
          <i class="far fa-user"></i> Profil
        </a>
        <?php if (activeGroupCan('admin.settings')): ?>
        <a href="<?= base_url('admin/settings') ?>" class="dropdown-item has-icon">
          <i class="fas fa-cog"></i> Pengaturan
        </a>
        <?php endif; ?>
        <div class="dropdown-divider"></div>
        <a href="<?= base_url('logout') ?>" class="dropdown-item has-icon text-danger">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </li>
  </ul>
</nav>
