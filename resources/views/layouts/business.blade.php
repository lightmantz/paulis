<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>@yield('title', 'Dashboard') · {{ auth()->user()->business->name ?? "Pauli's" }}</title>
<link rel="stylesheet" href="{{ asset('css/business.css') }}">
@stack('head')
<style>
  /* ── User dropdown ─────────────────────────────────────── */
  .user-menu{position:relative;display:inline-block}
  .user-menu-trigger{display:flex;align-items:center;gap:9px;border:1px solid transparent;background:#fff;border-radius:999px;padding:4px 10px 4px 4px;cursor:pointer;transition:.15s}
  .user-menu-trigger:hover{border-color:var(--line);background:#fafbff}
  .user-menu-trigger .avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#7767ef,#a057d6);color:#fff;display:grid;place-items:center;font-weight:800;font-size:12px;overflow:hidden;flex:none}
  .user-menu-trigger .avatar img{width:100%;height:100%;object-fit:cover;display:block}
  .user-menu-trigger .who{display:flex;flex-direction:column;align-items:flex-start;line-height:1.15;min-width:0}
  .user-menu-trigger .who b{font-size:11px;color:#172033;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px}
  .user-menu-trigger .who small{font-size:9px;color:#667085}
  .user-menu-trigger .caret{border:solid #667085;border-width:0 1.6px 1.6px 0;display:inline-block;padding:3px;transform:rotate(45deg);margin-left:4px;transition:.2s}
  .user-menu.open .user-menu-trigger .caret{transform:rotate(-135deg)}

  .user-menu-panel{position:absolute;right:0;top:calc(100% + 10px);width:230px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 14px 40px rgba(15,23,42,.12);padding:6px;opacity:0;visibility:hidden;transform:translateY(-4px);transition:.15s;z-index:50}
  .user-menu.open .user-menu-panel{opacity:1;visibility:visible;transform:translateY(0)}
  .user-menu-head{padding:10px 12px;border-bottom:1px solid var(--line);margin-bottom:4px}
  .user-menu-head b{display:block;font-size:12px;color:#172033}
  .user-menu-head small{display:block;font-size:10px;color:#667085;margin-top:2px;overflow-wrap:anywhere}
  .user-menu-item{display:flex;align-items:center;gap:10px;width:100%;border:0;background:none;color:#344054;border-radius:8px;padding:9px 11px;text-align:left;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;transition:.12s}
  .user-menu-item:hover{background:#f5f3ff;color:#4b3ec4}
  .user-menu-item .icon{width:18px;text-align:center;font-size:13px;color:#5a4ac2}
  .user-menu-item.danger{color:#b13d3d}
  .user-menu-item.danger .icon{color:#c2554f}
  .user-menu-item.danger:hover{background:#feecec;color:#8a2929}
  .user-menu-sep{height:1px;background:var(--line);margin:4px 0}

  @media(max-width:900px){
    .user-menu-trigger .who{display:none}
    .user-menu-trigger .caret{display:none}
    .user-menu-trigger{padding:3px}
    .user-menu-panel{right:0}
  }
</style>
@stack('styles')
</head>
<body>
<aside class="side" id="side">
  <div class="brand">
    <div class="logo">PC</div>
    <div><b>Pauli's</b><small>Computer Shop</small></div>
    <button class="close" onclick="menu(false)">×</button>
  </div>

  <div class="nav">
    <a class="nav-btn @if(request()->routeIs('business.dashboard')) active @endif"
       href="{{ route('business.dashboard') }}"><span>⌂</span>Dashboard</a>

    <a class="nav-btn @if(request()->routeIs('business.pos')) active @endif"
       href="{{ route('business.pos') }}"><span>▣</span>Point of Sale</a>

    <a class="nav-btn @if(request()->routeIs('business.sales*')) active @endif"
       href="{{ route('business.sales') }}"><span>▥</span>Sales</a>

    <a class="nav-btn @if(request()->routeIs('business.inventory')) active @endif"
       href="{{ route('business.inventory') }}"><span>▦</span>Inventory</a>

    <a class="nav-btn @if(request()->routeIs('business.service')) active @endif"
       href="{{ route('business.service') }}"><span>⚒</span>Repair, Maintenance &amp; Service</a>

    <a class="nav-btn @if(request()->routeIs('business.purchases')) active @endif"
       href="{{ route('business.purchases') }}"><span>↓</span>Purchases</a>

    <a class="nav-btn @if(request()->routeIs('business.customers')) active @endif"
       href="{{ route('business.customers') }}"><span>♙</span>Customers</a>

    <a class="nav-btn @if(request()->routeIs('business.suppliers')) active @endif"
       href="{{ route('business.suppliers') }}"><span>◇</span>Suppliers</a>

    <a class="nav-btn @if(request()->routeIs('business.expenses')) active @endif"
       href="{{ route('business.expenses') }}"><span>↗</span>Expenses</a>

    <a class="nav-btn @if(request()->routeIs('business.financial')) active @endif"
       href="{{ route('business.financial') }}"><span>◈</span>Financial Information</a>

    <a class="nav-btn @if(request()->routeIs('business.stock-taking')) active @endif"
       href="{{ route('business.stock-taking') }}"><span>▧</span>Stock Taking</a>

    <a class="nav-btn @if(request()->routeIs('business.returns')) active @endif"
       href="{{ route('business.returns') }}"><span>↩</span>Returns</a>

    <a class="nav-btn @if(request()->routeIs('business.reports')) active @endif"
       href="{{ route('business.reports') }}"><span>▥</span>Reports</a>

    <a class="nav-btn @if(request()->routeIs('business.activity')) active @endif"
       href="{{ route('business.activity') }}"><span>◷</span>Activity History</a>

    <a class="nav-btn @if(request()->routeIs('business.users')) active @endif"
       href="{{ route('business.users') }}"><span>♙</span>Users &amp; Access</a>

    <a class="nav-btn @if(request()->routeIs('business.settings')) active @endif"
       href="{{ route('business.settings') }}"><span>⚙</span>Settings</a>
  </div>
</aside>

<div class="scrim" id="scrim" onclick="menu(false)"></div>

<main class="main">
  <div class="top">
    <button class="hamb" onclick="menu(true)">☰</button>
    <b class="mobile-title">@yield('page-title', 'Dashboard')</b>

    <div class="top-right">
      <input class="search" placeholder="⌕  Search anything…" oninput="if(window.globalSearch)globalSearch(this.value)">

      <button style="border:0;background:#f2f3f6;border-radius:8px;padding:9px;cursor:pointer"
              onclick="if(window.toast)toast('No new notifications')">♧</button>

      <div class="user-menu" id="userMenu">
        <button type="button" class="user-menu-trigger" onclick="toggleUserMenu(event)">
          <span class="avatar">
            @php
              $u = auth()->user();
              $initials = collect(explode(' ', $u->name))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
            @endphp
            @if ($u->avatar)
              <img src="{{ asset('storage/' . $u->avatar) }}" alt="{{ $u->name }}">
            @else
              {{ strtoupper($initials) }}
            @endif
          </span>
          <span class="who">
            <b>{{ auth()->user()->name }}</b>
            <small>{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</small>
          </span>
          <span class="caret"></span>
        </button>

        <div class="user-menu-panel" role="menu" onclick="event.stopPropagation()">
          <div class="user-menu-head">
            <b>{{ auth()->user()->name }}</b>
            <small>{{ auth()->user()->email }}</small>
          </div>

          <a href="{{ route('business.profile') }}" class="user-menu-item" role="menuitem">
            <span class="icon">👤</span> My profile
          </a>

          <a href="{{ route('business.settings') }}" class="user-menu-item" role="menuitem">
            <span class="icon">⚙</span> Business settings
          </a>

          <div class="user-menu-sep"></div>

          <form method="POST" action="{{ route('business.logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="user-menu-item danger" role="menuitem">
              <span class="icon">↪</span> Sign out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    @yield('content')
  </div>
</main>

<script>
  function menu(v) {
    document.getElementById('side').classList.toggle('open', v);
    document.getElementById('scrim').classList.toggle('show', v);
  }
  function toggleUserMenu(e) {
    e.stopPropagation();
    document.getElementById('userMenu').classList.toggle('open');
  }
  document.addEventListener('click', function (e) {
    var menu = document.getElementById('userMenu');
    if (menu && !menu.contains(e.target)) menu.classList.remove('open');
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.getElementById('userMenu')?.classList.remove('open');
  });
</script>
@stack('scripts')
</body>
</html>
