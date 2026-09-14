@extends('layouts.superadmin')
@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">BILLING CONTROL</p>
      <h1>Subscriptions</h1>
      <p>Manage plans, renewals, payment status and access consequences.</p>
    </div>
  </div>

  <div class="stats">
    <article class="stat">
      <div class="stat-top"><span>Monthly recurring revenue</span><i class="stat-icon">↗</i></div>
      <strong>TSh {{ number_format($mrr) }}</strong>
      <small class="up">From active subscriptions</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Outstanding subscriptions</span><i class="stat-icon">!</i></div>
      <strong>TSh {{ number_format($outstanding) }}</strong>
      <small class="warn-text">Requires collection or review</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Active plans</span><i class="stat-icon">◈</i></div>
      <strong>{{ $businesses->where('status', 'Active')->count() }}</strong>
      <small>Currently billable accounts</small>
    </article>
    <article class="stat">
      <div class="stat-top"><span>Trial accounts</span><i class="stat-icon">T</i></div>
      <strong>{{ $businesses->where('status', 'Trial')->count() }}</strong>
      <small>Conversion follow-up required</small>
    </article>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Business</th><th>Plan</th><th>Monthly fee</th><th>Renewal</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          @foreach ($businesses as $b)
            <tr>
              <td><b>{{ $b->name }}</b><small>{{ $b->id }}</small></td>
              <td>{{ $b->plan }}</td>
              <td><b>TSh {{ number_format($b->monthly_fee) }}</b></td>
              <td>{{ $b->renewal_date?->format('d M Y') ?? '—' }}</td>
              <td>
                @php
                  $label = $b->status === 'Active' ? 'Paid' : ($b->status === 'Trial' ? 'Trial' : ($b->status === 'Suspended' ? 'Overdue' : 'Revoked'));
                  $cls   = in_array($label, ['Overdue', 'Revoked']) ? 'bad' : ($label === 'Trial' ? 'warn' : '');
                @endphp
                <span class="pill {{ $cls }}">{{ $label }}</span>
              </td>
              <td>
                <div class="row-actions">
                  <form method="POST" action="{{ route('superadmin.subscriptions.pay', $b) }}">
                    @csrf
                    <button class="ok-btn" type="submit">Record payment</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
