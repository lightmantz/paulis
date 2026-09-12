@extends('layouts.superadmin')
@section('title', 'System Financials')
@section('page-title', 'System Financials')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">PLATFORM FINANCE</p>
      <h1>System Financials</h1>
      <p>Subscription revenue for the platform — not individual shop trading profit.</p>
    </div>
  </div>

  <div class="stats">
    <div class="stat"><div class="stat-top"><span>Billed</span><i class="stat-icon">▤</i></div>
      <strong>TSh {{ number_format($billed) }}</strong><small>All subscriptions</small></div>
    <div class="stat"><div class="stat-top"><span>Collected</span><i class="stat-icon">✓</i></div>
      <strong>TSh {{ number_format($collected) }}</strong><small class="up">From active accounts</small></div>
    <div class="stat"><div class="stat-top"><span>Outstanding</span><i class="stat-icon">◷</i></div>
      <strong>TSh {{ number_format($outstanding) }}</strong><small class="warn-text">Requires action</small></div>
    <div class="stat"><div class="stat-top"><span>Net position</span><i class="stat-icon">↗</i></div>
      <strong>TSh {{ number_format($net) }}</strong><small class="up">After est. 28% cost</small></div>
  </div>

  <section class="panel" style="margin-top:18px">
    <div class="panel-head"><div><h2>Revenue by business</h2><p>Sorted by plan value</p></div></div>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Business</th><th>Plan</th><th>Billed</th><th>Collected</th><th>Outstanding</th></tr></thead>
        <tbody>
          @foreach ($byBusiness as $b)
            @php $paid = $b->status === 'Active' ? $b->monthly_fee : 0; @endphp
            <tr>
              <td><b>{{ $b->name }}</b></td>
              <td>{{ $b->plan }}</td>
              <td>TSh {{ number_format($b->monthly_fee) }}</td>
              <td class="up">TSh {{ number_format($paid) }}</td>
              <td class="warn-text">TSh {{ number_format($b->monthly_fee - $paid) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection