<?php
$currentUser  = auth()->user();
$currentUrl   = current_url();
$userGroups   = $currentUser->getGroups();
$active       = activeGroup();
$authGroups   = config('AuthGroups');

// Badge color per group
$badgeColors = [
    'superadmin' => 'danger',
    'admin'      => 'warning',
    'manager'    => 'info',
    'user'       => 'primary',
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
