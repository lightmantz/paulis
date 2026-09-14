@extends('layouts.business')
@section('title', 'Inventory')
@section('page-title', 'Inventory')

@push('head')
<style>
.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(680px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
.modal-head{display:flex;justify-content:space-between;align-items:flex-start;padding:18px 20px;border-bottom:1px solid var(--line);position:sticky;top:0;background:#fff;z-index:2}
.modal-head h2{margin:0 0 4px;font-size:17px}
.modal-head p{margin:0;color:var(--muted);font-size:10px}
.modal-head button{border:0;background:#f0f2f6;border-radius:8px;width:34px;height:34px;font-size:18px;cursor:pointer}
.modal-body{padding:18px 20px}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid var(--line)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-grid .field{display:flex;flex-direction:column;gap:5px}
.form-grid .full{grid-column:1/-1}
.form-grid label{font-size:10px;font-weight:800;color:#344054}
.form-grid input,.form-grid select,.form-grid textarea{border:1px solid #dfe3ea;border-radius:8px;padding:10px 11px;font-size:12px;background:#fff;outline:none}
.form-grid input:focus,.form-grid select:focus,.form-grid textarea:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(103,85,217,.09)}
.view-list{display:grid;gap:9px}
.view-list div{display:grid;grid-template-columns:130px 1fr;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:11px}
.view-list div:last-child{border:0}
.view-list span{color:var(--muted);font-size:10px;text-transform:uppercase;font-weight:800;letter-spacing:.3px}
.view-list b{font-weight:700;overflow-wrap:anywhere}
.row-action-icon{border:1px solid var(--line);background:#fff;border-radius:6px;padding:6px 8px;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap}
.row-action-icon.view{border-color:#d7d1ff;background:#f5f2ff;color:#5947ca}
.row-action-icon.edit{border-color:#c8e4ff;background:#eff7ff;color:#2458a3}
.row-action-icon.del{border-color:#f5c9c5;background:#feecec;color:#b13d3d}
.actions-cell{white-space:nowrap}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif
  @if (session('error'))<div class="created-banner" style="background:#feecec;border-color:#f3c8c6;color:#b13d3d"><b>✕ {{ session('error') }}</b></div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">STOCK CONTROL</p>
      <h1>Inventory &amp; Products</h1>
      <p>Track every device, accessory, serial number and condition.</p>
    </div>
    <button class="primary" onclick="openAdd()">＋ Add product</button>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search name, SKU, barcode…">
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Product</th><th>SKU / barcode</th><th>Condition</th>
            <th>Stock</th><th>Cost</th><th>Price</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($products as $p)
            <tr>
              <td><b>{{ $p->name }}</b><small>{{ $p->specs }}</small></td>
              <td><code>{{ $p->sku }}</code><small>{{ $p->barcode }}</small></td>
              <td>{{ $p->condition }}</td>
              <td>{{ $p->stock }}</td>
              <td>TSh {{ number_format($p->unit_cost) }}</td>
              <td>TSh {{ number_format($p->selling_price) }}</td>
              <td>
                <span class="pill @if($p->stock <= $p->reorder_level) warn @endif">
                  {{ $p->stock == 0 ? 'Out of stock' : ($p->stock <= $p->reorder_level ? 'Low stock' : 'In stock') }}
                </span>
              </td>
              <td class="actions-cell">
                <button class="row-action-icon view" onclick="viewProduct({{ $p->id }})">View</button>
                <button class="row-action-icon edit" onclick='editProduct(@json($p))'>Edit</button>
                <form method="POST" action="{{ route('business.inventory.destroy', $p) }}" style="display:inline"
                      onsubmit="return confirm('Delete {{ addslashes($p->name) }}? This cannot be undone.')">
                  @csrf
                  @method('DELETE')
                  <button class="row-action-icon del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">No products yet. Click “Add product” to create the first one.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $products->links() }}</div>
  </section>

  {{-- Add modal --}}
  <div class="modal-backdrop" id="addModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.inventory.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add product</h2><p>Create a new inventory item.</p></div>
          <button type="button" onclick="closeModal('addModal')">×</button>
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
          <button type="button" class="secondary" onclick="closeModal('addModal')">Cancel</button>
          <button type="submit" class="primary">Add product</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- View modal --}}
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div><h2 id="viewName">Product</h2><p id="viewSku"></p></div>
        <button type="button" onclick="closeModal('viewModal')">×</button>
      </header>
      <div class="modal-body">
        <div class="view-list" id="viewBody"></div>
      </div>
      <footer class="modal-actions">
        <button type="button" class="secondary" onclick="closeModal('viewModal')">Close</button>
      </footer>
    </div>
  </div>

  {{-- Edit modal --}}
  <div class="modal-backdrop" id="editModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="editForm">
        @csrf
        @method('PUT')
        <header class="modal-head">
          <div><h2>Edit product</h2><p id="editSubtitle"></p></div>
          <button type="button" onclick="closeModal('editModal')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field"><label>Product name *</label><input name="name" id="e_name" required></div>
            <div class="field"><label>SKU *</label><input name="sku" id="e_sku" required></div>
            <div class="field"><label>Barcode</label><input name="barcode" id="e_barcode"></div>
            <div class="field"><label>Condition *</label>
              <select name="condition" id="e_condition">
                <option>New</option><option>Used</option><option>Refurbished</option>
              </select></div>
            <div class="field"><label>Tracking *</label>
              <select name="tracking" id="e_tracking">
                <option value="quantity">Quantity only</option>
                <option value="optional_serial">Optional serial</option>
                <option value="required_serial">Required serial</option>
              </select></div>
            <div class="field"><label>Stock *</label><input name="stock" id="e_stock" type="number" min="0" required></div>
            <div class="field"><label>Reorder level *</label><input name="reorder_level" id="e_reorder" type="number" min="0" required></div>
            <div class="field"><label>Unit cost (TSh) *</label><input name="unit_cost" id="e_cost" type="number" min="0" required></div>
            <div class="field"><label>Selling price (TSh) *</label><input name="selling_price" id="e_price" type="number" min="0" required></div>
            <div class="field full"><label>Specifications</label><textarea name="specs" id="e_specs" rows="3"></textarea></div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
  function openAdd() { openModal('addModal'); }

  async function viewProduct(id) {
    const res = await fetch(`/app/inventory/${id}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Could not load product');
    const p = await res.json();

    document.getElementById('viewName').textContent = p.name;
    document.getElementById('viewSku').textContent = p.sku + (p.barcode ? ' · ' + p.barcode : '');

    document.getElementById('viewBody').innerHTML = `
      <div><span>Condition</span><b>${esc(p.condition)}</b></div>
      <div><span>Tracking</span><b>${esc(p.tracking)}</b></div>
      <div><span>Status</span><b>${esc(p.status)}</b></div>
      <div><span>Stock</span><b>${p.stock} units (reorder at ${p.reorder_level})</b></div>
      <div><span>Unit cost</span><b>TSh ${Math.round(p.unit_cost).toLocaleString()}</b></div>
      <div><span>Selling price</span><b>TSh ${Math.round(p.selling_price).toLocaleString()}</b></div>
      <div><span>Specifications</span><b>${esc(p.specs || '—')}</b></div>
    `;
    openModal('viewModal');
  }

  function editProduct(p) {
    document.getElementById('editSubtitle').textContent = p.name + ' · ' + p.sku;
    document.getElementById('editForm').action = '/app/inventory/' + p.id;

    document.getElementById('e_name').value    = p.name;
    document.getElementById('e_sku').value     = p.sku;
    document.getElementById('e_barcode').value = p.barcode || '';
    document.getElementById('e_condition').value = p.condition;
    document.getElementById('e_tracking').value  = p.tracking;
    document.getElementById('e_stock').value   = p.stock;
    document.getElementById('e_reorder').value = p.reorder_level;
    document.getElementById('e_cost').value    = Math.round(p.unit_cost);
    document.getElementById('e_price').value   = Math.round(p.selling_price);
    document.getElementById('e_specs').value   = p.specs || '';
    openModal('editModal');
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  </script>
@endsection
