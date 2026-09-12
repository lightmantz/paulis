@extends('layouts.business')
@section('title', 'Users & Access')
@section('page-title', 'Users & Access')

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif
  @if (session('error'))<div class="created-banner" style="background:#feecec;border-color:#f3c8c6;color:#b13d3d"><b>✕ {{ session('error') }}</b></div>@endif

  <div class="head">
    <div><p class="eyebrow">USERS</p><h1>Users &amp; Access</h1>
      <p>People who can sign in to {{ auth()->user()->business->name }}.</p></div>
    @if (auth()->user()->role === 'owner')
      <button class="primary" onclick="openAdd()">＋ Add user</button>
    @endif
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last login</th></tr></thead>
        <tbody>
          @foreach ($users as $u)
            <tr>
              <td><b>{{ $u->name }}</b><small>{{ $u->email }}</small></td>
              <td>{{ $u->username }}</td>
              <td><span class="pill blue">{{ ucwords(str_replace('_',' ', $u->role)) }}</span></td>
              <td><span class="pill @if($u->status !== 'Active') bad @endif">{{ $u->status }}</span></td>
              <td>{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  @if (auth()->user()->role === 'owner')
    <div class="modal-backdrop" id="addModal">
      <div class="modal-card">
        <form method="POST" action="{{ route('business.users.store') }}">
          @csrf
          <header class="modal-head">
            <div><h2>Add user</h2><p>Create a Repair Person or Sales Person account.</p></div>
            <button type="button" onclick="closeAdd()">×</button>
          </header>
          <div class="modal-body"><div class="form-grid">
            <div class="field"><label>Full name *</label><input name="name" required></div>
            <div class="field"><label>Username *</label><input name="username" required></div>
            <div class="field"><label>Email *</label><input name="email" type="email" required></div>
            <div class="field"><label>Phone</label><input name="phone"></div>
            <div class="field"><label>Role *</label>
              <select name="role">
                <option value="sales_person">Sales Person</option>
                <option value="repair_person">Repair Person</option>
              </select></div>
            <div class="field"><label>Temporary password *</label><input name="password" type="text" minlength="6" required></div>
          </div></div>
          <footer class="modal-actions">
            <button type="button" class="secondary" onclick="closeAdd()">Cancel</button>
            <button class="primary">Add user</button>
          </footer>
        </form>
      </div>
    </div>

    <script>
      function openAdd(){document.getElementById('addModal').classList.add('open')}
      function closeAdd(){document.getElementById('addModal').classList.remove('open')}
    </script>
  @endif
@endsection