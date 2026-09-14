@extends('layouts.superadmin')
@section('title', 'All Users')
@section('page-title', 'All Users')

@section('content')
  @if (session('reset_password'))
    <div class="reset-banner">
      <b>✓ Temporary password generated for {{ session('reset_password.user') }}</b>
      Share this securely — the user must change it on next login.
      <br><br>
      Temporary password: <code>{{ session('reset_password.temp') }}</code>
    </div>
  @endif

  <div class="page-head">
    <div>
      <p class="eyebrow">CENTRAL USER DIRECTORY</p>
      <h1>All Users</h1>
      <p>{{ $users->total() }} users across {{ $businesses->count() }} business accounts.</p>
    </div>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search name, email, username…">
      <select name="role" onchange="this.form.submit()">
        @foreach (['All roles','owner','repair_person','sales_person'] as $r)
          <option @selected(request('role') === $r)>{{ $r }}</option>
        @endforeach
      </select>
      <select name="business" onchange="this.form.submit()">
        <option value="All businesses" @selected(!request('business') || request('business') === 'All businesses')>All businesses</option>
        @foreach ($businesses as $b)
          <option value="{{ $b->id }}" @selected((int) request('business') === $b->id)>{{ $b->name }}</option>
        @endforeach
      </select>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>User</th><th>Business</th><th>Role</th><th>Status</th><th>Last login</th><th>Security actions</th></tr>
        </thead>
        <tbody>
          @forelse ($users as $u)
            <tr>
              <td><b>{{ $u->name }}</b><small>{{ $u->id }} · {{ $u->email }}</small></td>
              <td><b>{{ $u->business?->name ?? '—' }}</b><small>{{ $u->business_id }}</small></td>
              <td><span class="pill blue">{{ ucwords(str_replace('_', ' ', $u->role)) }}</span></td>
              <td>
                @php $cls = $u->status === 'Active' ? '' : 'bad'; @endphp
                <span class="pill {{ $cls }}">{{ $u->status }}</span>
              </td>
              <td>{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
              <td>
                <div class="row-actions">
                  <form method="POST" action="{{ route('superadmin.users.reset-password', $u) }}" style="display:inline"
                        onsubmit="return confirm('Generate a temporary password for {{ addslashes($u->name) }}?')">
                    @csrf
                    <button type="submit">Reset password</button>
                  </form>
                  <form method="POST" action="{{ route('superadmin.users.toggle', $u) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="{{ $u->status === 'Active' ? 'danger-btn' : 'ok-btn' }}">
                      {{ $u->status === 'Active' ? 'Disable' : 'Enable' }}
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No matching users.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($users->hasPages())
      <div class="pager">{{ $users->links() }}</div>
    @endif
  </section>
@endsection
