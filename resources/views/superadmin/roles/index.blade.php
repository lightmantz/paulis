@extends('layouts.superadmin')
@section('title', 'Roles & Access')
@section('page-title', 'Roles & Access')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">ACCESS GOVERNANCE</p>
      <h1>Roles &amp; Access</h1>
      <p>The Super Admin controls platform access; each Business Owner controls permissions within their own account.</p>
    </div>
  </div>

  <div class="role-grid">
    @foreach ([
      ['SA','Super Admin','Platform-wide administration',['Manage all business accounts and users','Manage subscriptions and system financials','Reset passwords and revoke accounts']],
      ['BO','Business Owner','Full control inside one business account',['Add users and assign roles','Perform every business task','Approve sensitive transactions']],
      ['RP','Repair Person','Repair and maintenance operations',['Register job cards','Self-assign repair work','Create repair bills','Other permissions assigned by Owner']],
      ['SP','Sales Person','Sales and daily operations',['POS and product list','Sales, quotations and invoices','Cash receipts and expenses','Other permissions assigned by Owner']],
    ] as $r)
      <article class="role-card">
        <div class="role-badge-large">{{ $r[0] }}</div>
        <h3>{{ $r[1] }}</h3>
        <p>{{ $r[2] }}</p>
        <ul>@foreach ($r[3] as $line)<li>{{ $line }}</li>@endforeach</ul>
      </article>
    @endforeach
  </div>
@endsection
