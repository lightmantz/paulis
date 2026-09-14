@extends('layouts.superadmin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
  $h = now()->hour;
  $greet = $h < 12 ? 'morning' : ($h < 18 ? 'afternoon' : 'evening');
@endphp

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">MULTIVENDOR CONTROL CENTRE</p>
      <h1>Good {{ $greet }}, System Administrator</h1>
      <p>Here is the current position across all managed business accounts.</p>
    </div>
    <a href="{{ route('superadmin.businesses') }}" class="primary">＋ New business account</a>
  </div>

  <div class="stats">
    <article class="stat">
      <div class="stat-top"><span>Business accounts</span><i class="stat-icon">▦</i></div>
      <strong>{{ $totalBusinesses }}</strong>
      <small class="up">{{ $activeCount }} active accounts</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Registered users</span><i class="stat-icon">♙</i></div>
      <strong>{{ $totalUsers }}</strong>
      <small class="up">{{ $activeUsers }} active users</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Monthly subscriptions</span><i class="stat-icon">◈</i></div>
      <strong>TSh {{ number_format($mrr) }}</strong>
      <small class="up">Recurring platform revenue</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Accounts requiring action</span><i class="stat-icon">!</i></div>
      <strong>{{ $needsAction }}</strong>
      <small class="warn-text">Trials, suspended or overdue</small>
    </article>
  </div>

  <div class="grid">
    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Subscription revenue trend</h2>
          <p>Recurring platform fees over the last seven months</p>
        </div>
        <a class="secondary" href="{{ route('superadmin.finance') }}">Financial details</a>
      </div>
      <div class="chart">
        @foreach ($trend as $t)
          @php $pct = $trendMax > 0 ? max(6, round($t['value'] / $trendMax * 100)) : 6; @endphp
          <div class="bar-wrap" title="TSh {{ number_format($t['value']) }}">
            <b>{{ $t['value'] >= 1000000 ? round($t['value']/1000000,1).'M' : round($t['value']/1000).'K' }}</b>
            <div class="bar" style="height:{{ $pct }}%"></div>
            <small>{{ $t['label'] }}</small>
          </div>
        @endforeach
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Recent platform activity</h2>
          <p>Latest security and account events</p>
        </div>
        <a class="secondary" href="{{ route('superadmin.audit') }}">View all</a>
      </div>
      <div class="activity">
        @forelse ($recentActivity as $e)
          <div class="activity-item">
            <i class="activity-icon">◷</i>
            <div>
              <b>{{ $e->description }}</b>
              <p>{{ class_basename($e->subject_type ?? 'System') }} · {{ $e->causer?->name ?? 'System' }}</p>
              <time>{{ $e->created_at->diffForHumans() }}</time>
            </div>
          </div>
        @empty
          <div class="empty">No activity yet.</div>
        @endforelse
      </div>
    </section>
  </div>
@endsection
