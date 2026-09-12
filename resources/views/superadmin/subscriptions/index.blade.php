@extends('layouts.superadmin')
@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">BILLING CONTROL</p>
      <h1>Subscriptions</h1>
      <p>Plan distribution and recurring revenue per business.</p>
    </div>
  </div>

  <div class="stats">
    <div class="stat"><div class="stat-top"><span>Monthly recurring revenue</span><i class="stat-icon">↗</i></div>
      <strong>TSh {{ number_format($mrr) }}</strong><small class="up">From active plans</small></div>
    <div class="stat"><div class="stat-top"><span>Outstanding / at risk</span><i class="stat-icon">!</i></div>
      <strong>TSh {{ number_format($outstanding) }}</strong><small class="warn-text">Trials + suspended</small></div>
    <div class="stat"><div class="stat-top"><span>Starter</span><i class="stat-icon">S</i></div>
      <strong>{{ $businesses->where('plan','Starter')->count() }}</strong><small>Accounts</small></div>
    <div class="stat"><div class="stat-top"><span>Growth + Pro</span><i class="stat-icon">G</i></div>
      <strong>{{ $businesses->whereIn('plan',['Growth','Professional'])->count() }}</strong><small>Accounts</small></div>
  </div>

  <section class="panel" style="margin-top:18px">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Business</th><th>Plan</th><th>Monthly fee</th><th>Renewal</th><th>Status</th></tr></thead>
        <tbody>
          @foreach ($businesses as $b)
            <tr>
              <td><b>{{ $b->name }}</b><small>{{ $b->slug }}</small></td>
              <td>{{ $b->plan }}</td>
              <td>TSh {{ number_format($b->monthly_fee) }}</td>
              <td>{{ $b->renewal_date?->format('d M Y') ?? '—' }}</td>
              <td><span class="pill @if(in_array($b->status,['Suspended','Revoked'])) bad @elseif($b->status==='Trial') warn @endif">{{ $b->status }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection