@extends('layouts.business')
@section('title', 'Activity History')
@section('page-title', 'Activity History')

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">AUDIT TRAIL</p>
      <h1>Activity History</h1>
      <p>{{ $events->total() }} recorded events in your business.</p>
    </div>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search description…">
      <select name="log" onchange="this.form.submit()">
        <option value="All" @selected(request('log') === 'All')>All modules</option>
        @foreach ($logs as $log)
          <option value="{{ $log }}" @selected(request('log') === $log)>{{ ucfirst($log) }}</option>
        @endforeach
      </select>
      <select name="user" onchange="this.form.submit()">
        <option value="All" @selected(request('user') === 'All')>All users</option>
        @foreach ($users as $u)
          <option value="{{ $u->id }}" @selected((string) request('user') === (string) $u->id)>{{ $u->name }}</option>
        @endforeach
      </select>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Time</th>
            <th>User</th>
            <th>Module</th>
            <th>Action</th>
            <th>Subject</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($events as $e)
            <tr>
              <td>
                <b>{{ $e->created_at->format('d M Y · H:i') }}</b>
                <small>{{ $e->created_at->diffForHumans() }}</small>
              </td>
              <td>
                <b>{{ $e->causer?->name ?? 'System' }}</b>
                <small>{{ $e->causer?->role ?? '—' }}</small>
              </td>
              <td><span class="pill blue">{{ $e->log_name ?? 'default' }}</span></td>
              <td>{{ $e->description }}</td>
              <td>
                @if ($e->subject_type)
                  {{ class_basename($e->subject_type) }} #{{ $e->subject_id }}
                @else
                  —
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="empty">No activity yet. Actions like sales, inventory changes, and expense records will appear here.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $events->links() }}</div>
  </section>
@endsection
