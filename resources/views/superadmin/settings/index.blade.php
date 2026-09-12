@extends('layouts.superadmin')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">SYSTEM CONFIGURATION</p>
      <h1>Settings</h1>
      <p>Super Admin identity and platform security controls.</p>
    </div>
  </div>

  <div class="grid">
    <section class="panel">
      <div class="panel-head"><div><h2>Administrator profile</h2><p>Primary Super Admin</p></div></div>
      <div style="padding:18px">
        <div class="form-grid">
          <div class="field"><label>Display name</label><input value="{{ auth('superadmin')->user()->name }}" readonly></div>
          <div class="field"><label>Email</label><input value="{{ auth('superadmin')->user()->email }}" readonly></div>
          <div class="field full"><label>Office</label><input value="Mwanza, Tanzania" readonly></div>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head"><div><h2>Security controls</h2><p>Active protections</p></div></div>
      <div class="activity">
        <div class="activity-item"><i class="activity-icon">✓</i><div><b>Business isolation</b><p>Every tenant sees only their own data</p></div></div>
        <div class="activity-item"><i class="activity-icon">✓</i><div><b>Admin audit history</b><p>All sensitive actions are logged</p></div></div>
        <div class="activity-item"><i class="activity-icon">✓</i><div><b>Password reset control</b><p>Super Admin and Owner scopes are separated</p></div></div>
      </div>
    </section>
  </div>
@endsection