@extends('layouts.business')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
  @if (session('success'))
    <div class="created-banner"><b>✓ {{ session('success') }}</b></div>
  @endif

  <div class="head">
    <div>
      <p class="eyebrow">TODAY</p>
      <h1>Welcome, {{ auth()->user()->name }}</h1>
      <p>Live trading position for {{ auth()->user()->business->name }}.</p>
    </div>
  </div>

  <div class="cards">
    <div class="card"><span class="icon">↗</span>
      <div><p>Today's sales</p><strong>TSh {{ number_format($todaySales) }}</strong><small>Real posted sales</small></div></div>
    <div class="card"><span class="icon">▦</span>
      <div><p>Stock value</p><strong>TSh {{ number_format($stockValue) }}</strong><small>At unit cost</small></div></div>
    <div class="card"><span class="icon">⌁</span>
      <div><p>Active repairs</p><strong>{{ $activeRepairs }}</strong><small>Open job cards</small></div></div>
    <div class="card"><span class="icon">▤</span>
      <div><p>Cash position</p><strong>TSh {{ number_format($cashIn) }}</strong><small>In − out</small></div></div>
  </div>
@endsection