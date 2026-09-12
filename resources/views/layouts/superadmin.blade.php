<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>@yield('title', 'Super Admin') · Pauli's POS Cloud</title>
<link rel="stylesheet" href="{{ asset('css/superadmin.css') }}">
@stack('head')
</head>
<body>
<div class="app">
  <aside class="side" id="side">
    <div class="brand">
      <div class="mark">SA</div>
      <div><b>Super Admin</b><small>Pauli's POS Cloud</small></div>
      <button class="close-side" onclick="menu(false)">×</button>
    </div>

    <nav class="nav">
      <div class="nav-label">Platform</div>
      <a class="nav-btn @if(request()->routeIs('superadmin.dashboard')) active @endif"
         href="{{ route('superadmin.dashboard') }}"><span>⌂</span>Dashboard</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.businesses')) active @endif"
         href="{{ route('superadmin.businesses') }}"><span>▦</span>Business Accounts</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.users')) active @endif"
         href="{{ route('superadmin.users') }}"><span>♙</span>All Users</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.subscriptions')) active @endif"
         href="{{ route('superadmin.subscriptions') }}"><span>◈</span>Subscriptions</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.finance')) active @endif"
         href="{{ route('superadmin.finance') }}"><span>▤</span>System Financials</a>
      <div class="nav-label">Control</div>
      <a class="nav-btn @if(request()->routeIs('superadmin.roles')) active @endif"
         href="{{ route('superadmin.roles') }}"><span>⌘</span>Roles &amp; Access</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.audit')) active @endif"
         href="{{ route('superadmin.audit') }}"><span>◷</span>Audit History</a>
      <a class="nav-btn @if(request()->routeIs('superadmin.settings')) active @endif"
         href="{{ route('superadmin.settings') }}"><span>⚙</span>Settings</a>
    </nav>

    <div class="account">
      <div class="avatar">SA</div>
      <div>
        <b>{{ auth('superadmin')->user()->name }}</b>
        <small>Super Admin</small>
      </div>
      <form method="POST" action="{{ route('superadmin.logout') }}">
        @csrf
        <button type="submit" title="Sign out">↪</button>
      </form>
    </div>
  </aside>

  <div class="scrim" id="scrim" onclick="menu(false)"></div>

  <main class="main">
    <header class="top">
      <div class="top-title">
        <button class="hamb" onclick="menu(true)">☰</button>
        <div>
          <b>@yield('page-title', 'Dashboard')</b>
          <span id="liveClock"></span>
        </div>
      </div>
      <div class="top-actions">
        <input class="global-search" placeholder="Search businesses or users…">
        <span class="role-chip">Super Admin</span>
      </div>
    </header>

    <div class="content">
      @yield('content')
    </div>
  </main>
</div>

<script>
function menu(v) {
  document.getElementById('side').classList.toggle('open', v);
  document.getElementById('scrim').classList.toggle('show', v);
}
function tick() {
  document.getElementById('liveClock').textContent =
    new Intl.DateTimeFormat('en-TZ', {dateStyle:'medium', timeStyle:'medium', timeZone:'Africa/Dar_es_Salaam'})
      .format(new Date());
}
tick(); setInterval(tick, 1000);
</script>
@stack('scripts')
</body>
</html>
