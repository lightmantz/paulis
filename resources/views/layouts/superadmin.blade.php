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
      <div>
        <b>Super Admin</b>
        <small>Pauli's POS Cloud</small>
      </div>
      <button class="close-side" onclick="menu(false)">×</button>
    </div>
    <nav class="nav">
      <div class="nav-label">Platform</div>
      <a href="{{ route('superadmin.dashboard') }}"      class="nav-btn @if(request()->routeIs('superadmin.dashboard')) active @endif"><span>⌂</span>Dashboard</a>
      <a href="{{ route('superadmin.businesses') }}"     class="nav-btn @if(request()->routeIs('superadmin.businesses')) active @endif"><span>▦</span>Business Accounts</a>
      <a href="{{ route('superadmin.users') }}"          class="nav-btn @if(request()->routeIs('superadmin.users')) active @endif"><span>♙</span>All Users</a>
      <a href="{{ route('superadmin.subscriptions') }}"  class="nav-btn @if(request()->routeIs('superadmin.subscriptions')) active @endif"><span>◈</span>Subscriptions</a>
      <a href="{{ route('superadmin.finance') }}"        class="nav-btn @if(request()->routeIs('superadmin.finance')) active @endif"><span>▤</span>System Financials</a>
      <div class="nav-label">Control</div>
      <a href="{{ route('superadmin.roles') }}"          class="nav-btn @if(request()->routeIs('superadmin.roles')) active @endif"><span>⌘</span>Roles &amp; Access</a>
      <a href="{{ route('superadmin.audit') }}"          class="nav-btn @if(request()->routeIs('superadmin.audit')) active @endif"><span>◷</span>Audit History</a>
      <a href="{{ route('superadmin.settings') }}"       class="nav-btn @if(request()->routeIs('superadmin.settings')) active @endif"><span>⚙</span>Settings</a>
    </nav>
    <div class="account">
      <div class="avatar">SA</div>
      <div>
        <b>{{ auth('superadmin')->user()->name }}</b>
        <small>Super Admin</small>
      </div>
      <form method="POST" action="{{ route('superadmin.logout') }}" style="margin-left:auto">
        @csrf
        <button type="submit" title="Sign out" style="border:0;background:transparent;color:#fff;font-size:17px;cursor:pointer">↪</button>
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
        <input class="global-search" id="globalSearch" placeholder="Search businesses or users…" oninput="globalLookup(this.value)">
        <span class="role-chip">Super Admin</span>
      </div>
    </header>

    <div class="content">
      @if (session('success'))
        <div class="flash">✓ {{ session('success') }}</div>
      @endif
      @yield('content')
    </div>
  </main>
</div>

<script>
function menu(v) {
  document.getElementById('side').classList.toggle('open', v);
  document.getElementById('scrim').classList.toggle('show', v);
}
function updateClock() {
  document.getElementById('liveClock').textContent =
    new Intl.DateTimeFormat('en-TZ', {dateStyle:'medium', timeStyle:'medium', timeZone:'Africa/Dar_es_Salaam'})
      .format(new Date());
}
updateClock(); setInterval(updateClock, 1000);

function globalLookup(q){
  q = (q||'').trim();
  if (q.length < 2) return;
  const path = location.pathname;
  if (path.endsWith('/businesses') || path.endsWith('/users')) {
    const inp = document.querySelector('input[name="q"]');
    if (inp) { inp.value = q; inp.form.submit(); return; }
  }
  location.href = '{{ route('superadmin.businesses') }}?q=' + encodeURIComponent(q);
}
</script>
@stack('scripts')
</body>
</html>
