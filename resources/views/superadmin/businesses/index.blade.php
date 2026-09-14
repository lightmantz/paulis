@extends('layouts.superadmin')
@section('title', 'Business Accounts')
@section('page-title', 'Business Accounts')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">TENANT MANAGEMENT</p>
      <h1>Business Accounts</h1>
      <p>{{ $businesses->total() }} total · create, open, suspend or revoke every vendor account.</p>
    </div>
    <button class="primary" type="button" onclick="document.getElementById('newBusinessModal').classList.add('open')">＋ Add business</button>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search business, owner, city or ID…">
      <select name="status" onchange="this.form.submit()">
        @foreach (['All statuses','Active','Trial','Suspended','Revoked'] as $s)
          <option @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
      </select>
      <select name="plan" onchange="this.form.submit()">
        @foreach (['All plans','Starter','Growth','Professional'] as $p)
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
              <td>
                @php
                  $cls = $b->status === 'Trial' ? 'warn' : (in_array($b->status, ['Suspended','Revoked']) ? 'bad' : '');
                @endphp
                <span class="pill {{ $cls }}">{{ $b->status }}</span>
              </td>
              <td><b>TSh {{ number_format($b->monthly_fee) }}</b></td>
              <td>{{ $b->renewal_date?->format('d M Y') ?? '—' }}</td>
              <td>
                <div class="row-actions">
                  <button type="button" class="open" onclick="openManage({{ $b->id }})">Manage</button>
                  <form method="POST" action="{{ route('superadmin.businesses.toggle', $b) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="{{ $b->status === 'Active' ? 'danger-btn' : 'ok-btn' }}">
                      {{ $b->status === 'Active' ? 'Suspend' : 'Activate' }}
                    </button>
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

    @if ($businesses->hasPages())
      <div class="pager">{{ $businesses->links() }}</div>
    @endif
  </section>

  {{-- New business modal --}}
  <div class="modal-backdrop" id="newBusinessModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('superadmin.businesses.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Create business account</h2><p>Register a new tenant and its Business Owner.</p></div>
          <button type="button" onclick="document.getElementById('newBusinessModal').classList.remove('open')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field full"><label>Business name</label><input name="name" required></div>
            <div class="field"><label>Owner name</label><input name="owner" required></div>
            <div class="field"><label>Owner email</label><input name="email" type="email" required></div>
            <div class="field"><label>Phone</label><input name="phone" required></div>
            <div class="field"><label>City</label><input name="city" value="Mwanza" required></div>
            <div class="field full"><label>Subscription plan</label>
              <select name="plan">
                <option>Starter</option><option>Growth</option><option selected>Professional</option>
              </select>
            </div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="document.getElementById('newBusinessModal').classList.remove('open')">Cancel</button>
          <button type="submit" class="primary">Create account</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- Manage modal --}}
  <div class="modal-backdrop" id="manageModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="manageForm">
        @csrf @method('PUT')
        <header class="modal-head">
          <div><h2 id="mTitle">Manage business</h2><p>Change status, plan, monthly fee and audit note.</p></div>
          <button type="button" onclick="document.getElementById('manageModal').classList.remove('open')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field"><label>Plan</label>
              <select name="plan" id="mPlan">
                <option>Starter</option><option>Growth</option><option>Professional</option>
              </select>
            </div>
            <div class="field"><label>Status</label>
              <select name="status" id="mStatus">
                <option>Active</option><option>Trial</option><option>Suspended</option><option>Revoked</option>
              </select>
            </div>
            <div class="field full"><label>Monthly fee (TSh)</label>
              <input name="monthly_fee" id="mFee" type="number" min="0" required>
            </div>
            <div class="field full"><label>Administrative note</label>
              <textarea name="note" rows="3" required placeholder="Reason for this change"></textarea>
            </div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="document.getElementById('manageModal').classList.remove('open')">Cancel</button>
          <button type="submit" class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  var MANAGE_DATA = {!! json_encode($businesses->map(function ($b) {
      return [
          'id' => $b->id,
          'name' => $b->name,
          'plan' => $b->plan,
          'status' => $b->status,
          'monthly_fee' => (float) $b->monthly_fee,
      ];
  })->values()) !!};

  function openManage(id) {
    var b = MANAGE_DATA.find(function (x) { return x.id === id; });
    if (!b) return;
    document.getElementById('mTitle').textContent = 'Manage ' + b.name;
    document.getElementById('mPlan').value = b.plan;
    document.getElementById('mStatus').value = b.status;
    document.getElementById('mFee').value = Math.round(b.monthly_fee);
    document.getElementById('manageForm').action = '/superadmin/businesses/' + b.id;
    document.getElementById('manageModal').classList.add('open');
  }
</script>
@endpush
