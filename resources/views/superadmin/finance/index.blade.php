@extends('layouts.superadmin')
@section('title', 'System Financials')
@section('page-title', 'System Financials')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">PLATFORM FINANCE</p>
      <h1>System Financials</h1>
      <p>Subscription revenue and platform-level financial position — not individual shop trading profit.</p>
    </div>
  </div>

  <div class="stats">
    <article class="stat">
      <div class="stat-top"><span>Subscription revenue billed</span><i class="stat-icon">▤</i></div>
      <strong>TSh {{ number_format($billed) }}</strong>
      <small>All tenant subscription charges</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Cash collected</span><i class="stat-icon">✓</i></div>
      <strong>TSh {{ number_format($collected) }}</strong>
      <small class="up">From active subscriptions</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Accounts receivable</span><i class="stat-icon">◷</i></div>
      <strong>TSh {{ number_format($outstanding) }}</strong>
      <small class="warn-text">Outstanding subscription fees</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Net platform position</span><i class="stat-icon">↗</i></div>
      <strong>TSh {{ number_format($net) }}</strong>
      <small class="up">After estimated 28% operating cost</small>
    </article>
  </div>

  <div class="grid">
    <section class="panel">
      <div class="panel-head">
        <div><h2>Revenue by business</h2><p>Subscription income only</p></div>
      </div>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Business</th><th>Plan</th><th>Billed</th><th>Collected</th><th>Outstanding</th></tr></thead>
          <tbody>
            @foreach ($byBusiness as $b)
              @php $paid = $b->status === 'Active' ? (float) $b->monthly_fee : 0; @endphp
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

    <section class="panel">
      <div class="panel-head">
        <div><h2>Platform position</h2><p>Selected period</p></div>
      </div>
      <div class="activity">
        <div class="activity-item"><i class="activity-icon">R</i><div><b>Collected revenue</b><p>TSh {{ number_format($collected) }}</p></div></div>
        <div class="activity-item"><i class="activity-icon">C</i><div><b>Platform operating cost (est. 28%)</b><p>TSh {{ number_format($cost) }}</p></div></div>
        <div class="activity-item"><i class="activity-icon">N</i><div><b>Net position</b><p>TSh {{ number_format($net) }}</p></div></div>
      </div>
    </section>
  </div>
@endsection
