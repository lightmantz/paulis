@extends('layouts.superadmin')
@section('title', 'Business Accounts')
@section('page-title', 'Business Accounts')

@section('content')
  @if (session('success'))
    <div class="flash">{{ session('success') }}</div>
  @endif

  <div class="page-head">
    <div>
      <p class="eyebrow">TENANT MANAGEMENT</p>
      <h1>Business Accounts</h1>
      <p>{{ $businesses->total() }} total · search, filter, suspend or manage every shop.</p>
    </div>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search name, owner, email, city…">
      <select name="status" onchange="this.form.submit()">
        @foreach (['All','Active','Trial','Suspended','Revoked'] as $s)
          <option @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
      </select>
      <select name="plan" onchange="this.form.submit()">
        @foreach (['All','Starter','Growth','Professional'] as $p)
          <option @selected(request('plan') === $p)>{{ $p }}</option>
        @endforeach
      </select>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Business</th><th>Owner</th><th>Plan</th><th>Users</th>
            <th>Status</th><th>Monthly</th><th>Renewal</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($businesses as $b)
            <tr>
              <td><b>{{ $b->name }}</b><small>{{ $b->slug }} · {{ $b->city }}</small></td>
              <td><b>{{ $b->owner_name }}</b><small>{{ $b->email }}</small></td>
              <td>{{ $b->plan }}</td>
              <td>{{ $b->users()->count() }}</td>
              <td><span class="pill @if($b->status==='Trial') warn @elseif(in_array($b->status,['Suspended','Revoked'])) bad @endif">{{ $b->status }}</span></td>
              <td>TSh {{ number_format($b->monthly_fee) }}</td>
              <td>{{ $b->renewal_date?->format('d M Y') ?? '—' }}</td>
              <td>
                <div class="row-actions">
                  <button type="button" class="open" onclick="openManage({{ $b->id }})">Manage</button>
                  <form method="POST" action="{{ route('superadmin.businesses.toggle', $b) }}" style="display:inline">
                    @csrf
                    <button type="submit">{{ $b->status === 'Active' ? 'Suspend' : 'Activate' }}</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">No matching business accounts.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $businesses->links() }}</div>
  </section>

  {{-- Manage modal --}}
  <div class="modal-backdrop" id="manageModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="manageForm">
        @csrf @method('PUT')
        <header class="modal-head">
          <div><h2 id="mTitle">Manage business</h2><p>Change plan, status, fee or renewal.</p></div>
          <button type="button" onclick="closeManage()">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field"><label>Plan</label>
              <select name="plan" id="mPlan">
                <option>Starter</option><option>Growth</option><option>Professional</option>
              </select></div>
            <div class="field"><label>Status</label>
              <select name="status" id="mStatus">
                <option>Active</option><option>Trial</option><option>Suspended</option><option>Revoked</option>
              </select></div>
            <div class="field"><label>Monthly fee (TSh)</label>
              <input name="monthly_fee" id="mFee" type="number" min="0" required></div>
            <div class="field"><label>Renewal date</label>
              <input name="renewal_date" id="mRenewal" type="date"></div>
            <div class="field full"><label>Administrative note</label>
              <textarea name="note" required placeholder="Reason for this change"></textarea></div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeManage()">Cancel</button>
          <button type="submit" class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
    const DATA = @json($businesses->getCollection()->map(fn($b) => [
      'id' => $b->id, 'name' => $b->name, 'plan' => $b->plan, 'status' => $b->status,
      'monthly_fee' => $b->monthly_fee,
      'renewal_date' => $b->renewal_date?->format('Y-m-d'),
    ]));
    function openManage(id) {
      const b = DATA.find(x => x.id === id); if (!b) return;
      document.getElementById('mTitle').textContent = 'Manage ' + b.name;
      document.getElementById('mPlan').value = b.plan;
      document.getElementById('mStatus').value = b.status;
      document.getElementById('mFee').value = Math.round(b.monthly_fee);
      document.getElementById('mRenewal').value = b.renewal_date || '';
      document.getElementById('manageForm').action = '/superadmin/businesses/' + b.id;
      document.getElementById('manageModal').classList.add('open');
    }
    function closeManage() { document.getElementById('manageModal').classList.remove('open'); }
  </script>
@endsection