@extends('layouts.superadmin')
@section('title', 'All Users')
@section('page-title', 'All Users')

@section('content')
  @if (session('success'))
    <div class="flash">{{ session('success') }}</div>
  @endif

  <div class="page-head">
    <div>
      <p class="eyebrow">CENTRAL USER DIRECTORY</p>
      <h1>All Users</h1>
      <p>{{ $users->total() }} users across all business accounts.</p>
    </div>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search name, email, username…">
      <select name="role" onchange="this.form.submit()">
        @foreach (['All','owner','repair_person','sales_person'] as $r)
          <option @selected(request('role') === $r)>{{ $r }}</option>
        @endforeach
      </select>
      <select name="business" onchange="this.form.submit()">
        <option>All</option>
        @foreach ($businesses as $b)
          <option value="{{ $b->id }}" @selected((int) request('business') === $b->id)>{{ $b->name }}</option>
        @endforeach
      </select>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>User</th><th>Business</th><th>Role</th><th>Status</th><th>Last login</th><th>Action</th></tr>
        </thead>
        <tbody>
          @forelse ($users as $u)
            <tr>
              <td><b>{{ $u->name }}</b><small>{{ $u->email }}</small></td>
              <td>{{ $u->business?->name ?? '—' }}</td>
              <td><span class="pill blue">{{ $u->role }}</span></td>
              <td><span class="pill @if($u->status!=='Active') bad @endif">{{ $u->status }}</span></td>
              <td>{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
              <td>
                <form method="POST" action="{{ route('superadmin.users.toggle', $u) }}">
                  @csrf
                  <button type="submit">{{ $u->status === 'Active' ? 'Disable' : 'Enable' }}</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No matching users.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $users->links() }}</div>
  </section>
@endsection