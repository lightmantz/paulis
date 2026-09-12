@extends('layouts.business')
@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif

  <div class="head">
    <div><p class="eyebrow">STOCK CONTROL</p><h1>Inventory &amp; Products</h1>
      <p>Track every device, accessory, serial number and condition.</p></div>
    <button class="primary" onclick="openAdd()">＋ Add product</button>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search name, SKU, barcode…">
    </form>
    <div class="table-wrap">
      <table class="data">
        <thead><tr>
          <th>Product</th><th>SKU / barcode</th><th>Condition</th>
          <th>Stock</th><th>Cost</th><th>Price</th><th>Status</th>
        </tr></thead>
        <tbody>
          @forelse ($products as $p)
            <tr>
              <td><b>{{ $p->name }}</b><small>{{ $p->specs }}</small></td>
              <td><code>{{ $p->sku }}</code><small>{{ $p->barcode }}</small></td>
              <td>{{ $p->condition }}</td>
              <td>{{ $p->stock }}</td>
              <td>TSh {{ number_format($p->unit_cost) }}</td>
              <td>TSh {{ number_format($p->selling_price) }}</td>
              <td><span class="pill @if($p->stock <= $p->reorder_level) warn @endif">
                {{ $p->stock == 0 ? 'Out of stock' : ($p->stock <= $p->reorder_level ? 'Low stock' : 'In stock') }}
              </span></td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">No products yet. Click “Add product” to create the first one.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $products->links() }}</div>
  </section>

  <div class="modal-backdrop" id="addModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.inventory.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add product</h2><p>Create a new inventory item.</p></div>
          <button type="button" onclick="closeAdd()">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field"><label>Product name *</label><input name="name" required></div>
            <div class="field"><label>SKU *</label><input name="sku" required></div>
            <div class="field"><label>Barcode</label><input name="barcode"></div>
            <div class="field"><label>Condition *</label>
              <select name="condition"><option>New</option><option>Used</option><option>Refurbished</option></select></div>
            <div class="field"><label>Tracking *</label>
              <select name="tracking">
                <option value="quantity">Quantity only</option>
                <option value="optional_serial">Optional serial</option>
                <option value="required_serial">Required serial</option>
              </select></div>
            <div class="field"><label>Stock *</label><input name="stock" type="number" value="0" min="0" required></div>
            <div class="field"><label>Reorder level *</label><input name="reorder_level" type="number" value="5" min="0" required></div>
            <div class="field"><label>Unit cost (TSh) *</label><input name="unit_cost" type="number" value="0" min="0" required></div>
            <div class="field"><label>Selling price (TSh) *</label><input name="selling_price" type="number" value="0" min="0" required></div>
            <div class="field full"><label>Specifications</label><textarea name="specs" rows="3"></textarea></div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeAdd()">Cancel</button>
          <button type="submit" class="primary">Add product</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
    function openAdd(){document.getElementById('addModal').classList.add('open')}
    function closeAdd(){document.getElementById('addModal').classList.remove('open')}
  </script>
@endsection