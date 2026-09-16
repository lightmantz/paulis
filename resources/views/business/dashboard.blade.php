@extends('layouts.business')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
  $hour = (int) now()->format('G');
  if ($hour < 12)      $salutation = 'Good morning';
  elseif ($hour < 17)  $salutation = 'Good afternoon';
  elseif ($hour < 21)  $salutation = 'Good evening';
  else                 $salutation = 'Good night';

  $firstName  = explode(' ', auth()->user()->name)[0];
  $todayLabel = strtoupper(now()->locale('en')->isoFormat('dddd D MMMM YYYY'));
@endphp

@section('content')
  @if (session('success'))
    <div class="created-banner"><b>✓ {{ session('success') }}</b><span>Saved just now</span></div>
  @endif

  {{-- Head --}}
  <div class="head">
    <div>
      <p class="eyebrow dashboard-clock">
        <span id="dashboardClock">{{ $todayLabel }}</span>
        <span class="live-time" id="dashboardTime">{{ now()->format('h:i:s A') }}</span>
      </p>
      <h1 id="dashboardGreeting">{{ $salutation }}, {{ $firstName }}</h1>
      <p>Here's what's happening at your shop today.</p>
    </div>
    <a href="{{ route('business.pos') }}" class="primary" style="text-decoration:none">＋ New sale</a>
  </div>

  {{-- KPI cards --}}
  <div class="cards">
    <div class="card">
      <span class="icon">↗</span>
      <div>
        <p>Today's sales</p>
        <strong>TSh {{ number_format($todaySales) }}</strong>
        <small class="up">Live from posted sales</small>
      </div>
    </div>
    <div class="card">
      <span class="icon">▦</span>
      <div>
        <p>Stock value</p>
        <strong>TSh {{ number_format($stockValue) }}</strong>
        <small>{{ $stockCount }} units across {{ $productCount }} products</small>
      </div>
    </div>
    <div class="card">
      <span class="icon">⌁</span>
      <div>
        <p>Active repairs</p>
        <strong>{{ $activeRepairs }}</strong>
        <small>{{ $awaitingApproval }} awaiting customer action</small>
      </div>
    </div>
    <div class="card">
      <span class="icon">▤</span>
      <div>
        <p>Cash position</p>
        <strong>TSh {{ number_format($cash) }}</strong>
        <small>Register + M-Pesa</small>
      </div>
    </div>
  </div>

  {{-- Chart + Total Expenses --}}
  <div class="grid">
    <section class="panel">
      <header>
        <div>
          <h2>Sales overview</h2>
          <p>Revenue across the last 7 days</p>
        </div>
        <select disabled aria-label="Range">
          <option>Last 7 days</option>
        </select>
      </header>
      <div class="chart" id="chart">
        @foreach ($chart as $day)
          @php $height = $chartMax > 0 ? max(6, round($day['total'] / $chartMax * 100)) : 6; @endphp
          <div class="bar" title="{{ $day['date'] }} · TSh {{ number_format($day['total']) }}">
            <i style="height:{{ $height }}%"></i>
            <span>{{ $day['label'] }}</span>
          </div>
        @endforeach
      </div>
    </section>

    <section class="panel">
      <header>
        <div>
          <h2>Total Expenses</h2>
          <p>Operational spending summary</p>
        </div>
        <a class="link" href="{{ route('business.expenses') }}">View all</a>
      </header>

      {{-- Big number: this month --}}
      <div class="expenses-hero">
        <small>THIS MONTH</small>
        <strong>TSh {{ number_format($expensesMonth) }}</strong>
        <span>Year to date · TSh {{ number_format($expensesYear) }}</span>
      </div>

      {{-- Quick stats --}}
      <a class="alert" href="{{ route('business.expenses') }}">
        <span>↗</span>
        <div>
          <b>Today · TSh {{ number_format($expensesToday) }}</b>
          <small>Spending recorded today</small>
        </div>
        ›
      </a>

      <a class="alert" href="{{ route('business.expenses', ['status' => 'Pending Owner approval']) }}">
        <span>!</span>
        <div>
          <b>{{ $expensesPendingCount }} awaiting approval</b>
          <small>TSh {{ number_format($expensesPendingValue) }} pending review</small>
        </div>
        ›
      </a>

      {{-- Top categories --}}
      <div class="expense-categories">
        <h4>Top categories this month</h4>
        @forelse ($topExpenseCategories as $cat)
          <div class="expense-cat-row">
            <span>{{ $cat->category ?: 'Uncategorised' }}</span>
            <b>TSh {{ number_format($cat->total) }}</b>
          </div>
        @empty
          <div class="expense-empty">No expenses recorded this month.</div>
        @endforelse
      </div>
    </section>
  </div>

  {{-- Recent sales + Repair desk --}}
  <div class="lower">
    <section class="panel">
      <header>
        <div>
          <h2>Recent sales</h2>
          <p>Latest transactions</p>
        </div>
        <a class="link" href="{{ route('business.pos') }}">View all sales</a>
      </header>
      <div class="table">
        <table>
          <thead>
            <tr>
              <th>Invoice</th>
              <th>Customer</th>
              <th>Item</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentSales as $sale)
              <tr>
                <td><b>{{ $sale->invoice_no }}</b></td>
                <td>{{ $sale->customer?->name ?? 'Walk-in customer' }}</td>
                <td>
                  @php
                    $firstItem = $sale->items->first();
                    $moreItems = max(0, $sale->items->count() - 1);
                  @endphp
                  {{ $firstItem?->product?->name ?? '—' }}{{ $moreItems > 0 ? " × +$moreItems" : '' }}
                </td>
                <td><b>TSh {{ number_format($sale->total) }}</b></td>
                <td>
                  <span class="pill @if ($sale->balance > 0) warn @endif">
                    {{ $sale->balance > 0 ? 'Part paid' : 'Paid' }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="text-align:center;color:#8d94a0;padding:24px">
                  No sales yet. Click <b>New sale</b> to record the first one.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <header>
        <div>
          <h2>Repair desk</h2>
          <p>Jobs in progress</p>
        </div>
        <a class="link" href="{{ route('business.service') }}">View all jobs</a>
      </header>
      @forelse ($recentJobs as $job)
        <a class="job" href="{{ route('business.service') }}" style="text-decoration:none;color:inherit">
          <b>{{ $job->job_no }} · {{ ucwords(str_replace('_', ' ', $job->status)) }}</b>
          <strong>{{ $job->customer?->name ?? '—' }}</strong>
          <small>{{ $job->device }} · {{ \Illuminate\Support\Str::limit($job->issue, 48) }}</small>
        </a>
      @empty
        <div style="padding:24px;text-align:center;color:#8d94a0;font-size:11px">
          No active repair jobs.
        </div>
      @endforelse
    </section>
  </div>

  <script>
    (function () {
      function tick() {
        var now = new Date();
        var dateEl = document.getElementById('dashboardClock');
        var timeEl = document.getElementById('dashboardTime');
        if (dateEl) dateEl.textContent = new Intl.DateTimeFormat('en-TZ', {weekday:'long', day:'numeric', month:'long', year:'numeric'}).format(now).toUpperCase();
        if (timeEl) timeEl.textContent = new Intl.DateTimeFormat('en-TZ', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true}).format(now);
      }
      tick();
      setInterval(tick, 1000);
    })();
  </script>
@endsection
