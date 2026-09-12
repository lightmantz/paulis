@extends('layouts.superadmin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
  @if (session('success'))
    <div class="flash">{{ session('success') }}</div>
  @endif

  <div class="page-head">
    <div>
      <p class="eyebrow">MULTIVENDOR CONTROL CENTRE</p>
      <h1>Hello, {{ auth('superadmin')->user()->name }}</h1>
      <p>Live platform position across all business accounts.</p>
    </div>
  </div>

  <div class="stats">
    <div class="stat"><div class="stat-top"><span>Business accounts</span><i class="stat-icon">▦</i></div>
      <strong>{{ $totalBusinesses }}</strong><small class="up">{{ $activeCount }} active</small></div>
    <div class="stat"><div class="stat-top"><span>Registered users</span><i class="stat-icon">♙</i></div>
      <strong>{{ $totalUsers }}</strong><small class="up">{{ $activeUsers }} active</small></div>
    <div class="stat"><div class="stat-top"><span>Monthly subscriptions</span><i class="stat-icon">◈</i></div>
      <strong>TSh {{ number_format($mrr) }}</strong><small class="up">Recurring revenue</small></div>
    <div class="stat"><div class="stat-top"><span>Need attention</span><i class="stat-icon">!</i></div>
      <strong>{{ $needsAction }}</strong><small class="warn-text">Trials, suspended, revoked</small></div>
  </div>

  <section class="panel" style="margin-top:18px">
    <div class="panel-head">
      <div><h2>Recent platform activity</h2><p>Latest events across all tenants</p></div>
      <a class="link" href="{{ route('superadmin.audit') }}">View all</a>
    </div>
    <div class="activity">
      @forelse ($recentActivity as $event)
        <div class="activity-item">
          <i class="activity-icon">◷</i>
          <div>
            <b>{{ $event->description }}</b>
            <p>{{ $event->causer?->name ?? 'System' }} · {{ $event->subject_type ?? '—' }}</p>
            <time>{{ $event->created_at->diffForHumans() }}</time>
          </div>
        </div>
      @empty
        <div class="empty">No activity yet.</div>
      @endforelse
    </div>
  </section>
@endsection