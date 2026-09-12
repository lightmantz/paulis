@extends('layouts.business')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif

  <div class="head">
    <div><p class="eyebrow">SUPPLIERS</p><h1>Suppliers</h1>
      <p>Contacts, purchases and balances.</p></div>
    <button class="primary" onclick="openAdd()">＋ Add supplier</button>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Balance</th></tr></thead>
        <tbody>
          @forelse ($suppliers as $s)
            <tr>
              <td><b>{{ $s->name }}</b><small>{{ $s->email }}</small></td>
              <td>{{ $s->contact_person }}</td>
              <td>{{ $s->phone }}</td>
              <td>TSh {{ number_format($s->balance ?? 0) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="empty">No suppliers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $suppliers->links() }}</div>
  </section>

  <div class="modal-backdrop" id="addModal">
    <div class="modal-card">
      <form method="POST" action="{{ route('business.suppliers.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add supplier</h2><p>Link a supplier to your business.</p></div>
          <button type="button" onclick="closeAdd()">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Supplier name *</label><input name="name" required></div>
          <div class="field"><label>Contact person</label><input name="contact_person"></div>
          <div class="field"><label>Phone</label><input name="phone"></div>
          <div class="field"><label>Email</label><input name="email" type="email"></div>
          <div class="field full"><label>Address</label><input name="address"></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeAdd()">Cancel</button>
          <button class="primary">Add supplier</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
    function openAdd(){document.getElementById('addModal').classList.add('open')}
    function closeAdd(){document.getElementById('addModal').classList.remove('open')}
  </script>
@endsection