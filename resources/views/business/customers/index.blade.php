@extends('layouts.business')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif

  <div class="head">
    <div><p class="eyebrow">CUSTOMERS</p><h1>Customers</h1>
      <p>Profiles, credit and purchase history.</p></div>
    <button class="primary" onclick="openAdd()">＋ Add customer</button>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Phone</th><th>Type</th><th>Balance</th><th>Credit</th></tr></thead>
        <tbody>
          @forelse ($customers as $c)
            <tr>
              <td><b>{{ $c->name }}</b><small>{{ $c->email }}</small></td>
              <td>{{ $c->phone }}</td>
              <td>{{ $c->type }}</td>
              <td>TSh {{ number_format($c->balance ?? 0) }}</td>
              <td>{{ !empty($c->credit_allowed) ? 'Allowed' : 'No' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="empty">No customers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $customers->links() }}</div>
  </section>

  <div class="modal-backdrop" id="addModal">
    <div class="modal-card">
      <form method="POST" action="{{ route('business.customers.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add customer</h2></header>
          <button type="button" onclick="closeAdd()">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Name *</label><input name="name" required></div>
          <div class="field"><label>Phone</label><input name="phone"></div>
          <div class="field"><label>Email</label><input name="email" type="email"></div>
          <div class="field"><label>Type *</label>
            <select name="type"><option>Individual</option><option>Business</option><option>School</option></select></div>
          <div class="field full"><label>Address</label><input name="address"></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeAdd()">Cancel</button>
          <button class="primary">Add customer</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
    function openAdd(){document.getElementById('addModal').classList.add('open')}
    function closeAdd(){document.getElementById('addModal').classList.remove('open')}
  </script>
@endsection