@extends('layouts.business')
@section('title', 'Customers')
@section('page-title', 'Customers')

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
.view-list span{color:var(--muted);font-size:10px;text-transform:uppercase;font-weight:800}
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
      <p class="eyebrow">CUSTOMERS</p>
      <h1>Customers</h1>
      <p>Profiles, credit and purchase history.</p>
    </div>
    <button class="primary" onclick="openModal('addModal')">＋ Add customer</button>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Phone</th><th>Type</th><th>Balance</th><th>Credit</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($customers as $c)
            <tr>
              <td><b>{{ $c->name }}</b><small>{{ $c->email }}</small></td>
              <td>{{ $c->phone }}</td>
              <td>{{ $c->type }}</td>
              <td>TSh {{ number_format($c->balance ?? 0) }}</td>
              <td>{{ !empty($c->credit_allowed) ? 'Allowed' : 'No' }}</td>
              <td class="actions-cell">
                <button class="row-action-icon view" onclick="viewCustomer({{ $c->id }})">View</button>
                <button class="row-action-icon edit" onclick='editCustomer(@json($c))'>Edit</button>
                <form method="POST" action="{{ route('business.customers.destroy', $c) }}" style="display:inline"
                      onsubmit="return confirm('Delete {{ addslashes($c->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="row-action-icon del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No customers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $customers->links() }}</div>
  </section>

  {{-- Add modal --}}
  <div class="modal-backdrop" id="addModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.customers.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add customer</h2><p>Create a customer profile.</p></div>
          <button type="button" onclick="closeModal('addModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Name *</label><input name="name" required></div>
          <div class="field"><label>Phone</label><input name="phone"></div>
          <div class="field"><label>Email</label><input name="email" type="email"></div>
          <div class="field"><label>Type *</label>
            <select name="type"><option>Individual</option><option>Business</option><option>School</option></select></div>
          <div class="field full"><label>Address</label><input name="address"></div>
          <div class="field full">
            <label style="display:flex;align-items:center;gap:8px">
              <input type="hidden" name="credit_allowed" value="0">
              <input type="checkbox" name="credit_allowed" value="1">
              Credit allowed
            </label>
          </div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('addModal')">Cancel</button>
          <button class="primary">Add customer</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- View modal --}}
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div><h2 id="viewName">Customer</h2><p id="viewSubtitle"></p></div>
        <button type="button" onclick="closeModal('viewModal')">×</button>
      </header>
      <div class="modal-body"><div class="view-list" id="viewBody"></div></div>
      <footer class="modal-actions">
        <button type="button" class="secondary" onclick="closeModal('viewModal')">Close</button>
      </footer>
    </div>
  </div>

  {{-- Edit modal --}}
  <div class="modal-backdrop" id="editModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="editForm">
        @csrf @method('PUT')
        <header class="modal-head">
          <div><h2>Edit customer</h2><p id="editSubtitle"></p></div>
          <button type="button" onclick="closeModal('editModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Name *</label><input name="name" id="e_name" required></div>
          <div class="field"><label>Phone</label><input name="phone" id="e_phone"></div>
          <div class="field"><label>Email</label><input name="email" id="e_email" type="email"></div>
          <div class="field"><label>Type *</label>
            <select name="type" id="e_type"><option>Individual</option><option>Business</option><option>School</option></select></div>
          <div class="field full"><label>Address</label><input name="address" id="e_address"></div>
          <div class="field full">
            <label style="display:flex;align-items:center;gap:8px">
              <input type="hidden" name="credit_allowed" value="0">
              <input type="checkbox" name="credit_allowed" id="e_credit" value="1">
              Credit allowed
            </label>
          </div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('editModal')">Cancel</button>
          <button class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }

  async function viewCustomer(id) {
    const res = await fetch(`/app/customers/${id}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Could not load customer');
    const c = await res.json();

    document.getElementById('viewName').textContent = c.name;
    document.getElementById('viewSubtitle').textContent = c.type + (c.phone ? ' · ' + c.phone : '');

    document.getElementById('viewBody').innerHTML = `
      <div><span>Phone</span><b>${esc(c.phone || '—')}</b></div>
      <div><span>Email</span><b>${esc(c.email || '—')}</b></div>
      <div><span>Type</span><b>${esc(c.type)}</b></div>
      <div><span>Address</span><b>${esc(c.address || '—')}</b></div>
      <div><span>Credit allowed</span><b>${c.credit_allowed ? 'Yes' : 'No'}</b></div>
      <div><span>Balance</span><b>TSh ${Math.round(c.balance).toLocaleString()}</b></div>
      <div><span>Sales</span><b>${c.sales_count} invoices · TSh ${Math.round(c.sales_total).toLocaleString()}</b></div>
    `;
    openModal('viewModal');
  }

  function editCustomer(c) {
    document.getElementById('editSubtitle').textContent = c.name;
    document.getElementById('editForm').action = '/app/customers/' + c.id;
    document.getElementById('e_name').value = c.name;
    document.getElementById('e_phone').value = c.phone || '';
    document.getElementById('e_email').value = c.email || '';
    document.getElementById('e_type').value = c.type;
    document.getElementById('e_address').value = c.address || '';
    document.getElementById('e_credit').checked = !!c.credit_allowed;
    openModal('editModal');
  }

  function esc(s) { return String(s==null?'':s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  </script>
@endsection
