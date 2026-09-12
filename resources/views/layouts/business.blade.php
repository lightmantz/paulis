<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>@yield('title', 'Dashboard') · {{ auth()->user()->business->name }}</title>
<link rel="stylesheet" href="{{ asset('css/business.css') }}">
@stack('head')
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

  <div class="account">
    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
    <div>
      <b>{{ auth()->user()->name }}</b>
      <small>{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</small>
    </div>
    <form method="POST" action="{{ route('business.logout') }}">
      @csrf
      <button type="submit" title="Log out">↪</button>
    </form>
  </div>
</aside>
<div class="scrim" id="scrim" onclick="menu(false)"></div>

<main class="main">
  <div class="top">
    <button class="hamb" onclick="menu(true)">☰</button>
    <b class="mobile-title">@yield('page-title', 'Dashboard')</b>
    <div class="top-right">
      <input class="search" placeholder="⌕  Search anything…">
      <button style="border:0;background:#f2f3f6;border-radius:8px;padding:9px">♧</button>
      <span class="signed-label">
        Signed in as
        <b class="role-badge">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</b>
      </span>
    </div>
  </div>
  <div class="content">
    @yield('content')
  </div>
</main>

<script>
function menu(v){document.getElementById('side').classList.toggle('open',v);document.getElementById('scrim').classList.toggle('show',v);}
</script>
@stack('scripts')
</body>
</html>
