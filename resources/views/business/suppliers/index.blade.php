@extends('layouts.business')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

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
      <p class="eyebrow">SUPPLIERS</p>
      <h1>Suppliers</h1>
      <p>Contacts, purchases and balances.</p>
    </div>
    <button class="primary" onclick="openModal('addModal')">＋ Add supplier</button>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Balance</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($suppliers as $s)
            <tr>
              <td><b>{{ $s->name }}</b><small>{{ $s->email }}</small></td>
              <td>{{ $s->contact_person }}</td>
              <td>{{ $s->phone }}</td>
              <td>TSh {{ number_format($s->balance ?? 0) }}</td>
              <td class="actions-cell">
                <button class="row-action-icon view" onclick="viewSupplier({{ $s->id }})">View</button>
                <button class="row-action-icon edit" onclick='editSupplier(@json($s))'>Edit</button>
                <form method="POST" action="{{ route('business.suppliers.destroy', $s) }}" style="display:inline"
                      onsubmit="return confirm('Delete {{ addslashes($s->name) }}?')">
                  @csrf @method('DELETE')
                  <button class="row-action-icon del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="empty">No suppliers yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $suppliers->links() }}</div>
  </section>

  {{-- Add --}}
  <div class="modal-backdrop" id="addModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.suppliers.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Add supplier</h2><p>Link a supplier to your business.</p></div>
          <button type="button" onclick="closeModal('addModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Supplier name *</label><input name="name" required></div>
          <div class="field"><label>Contact person</label><input name="contact_person"></div>
          <div class="field"><label>Phone</label><input name="phone"></div>
          <div class="field"><label>Email</label><input name="email" type="email"></div>
          <div class="field full"><label>Address</label><input name="address"></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('addModal')">Cancel</button>
          <button class="primary">Add supplier</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- View --}}
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div><h2 id="viewName">Supplier</h2><p id="viewSubtitle"></p></div>
        <button type="button" onclick="closeModal('viewModal')">×</button>
      </header>
      <div class="modal-body"><div class="view-list" id="viewBody"></div></div>
      <footer class="modal-actions">
        <button type="button" class="secondary" onclick="closeModal('viewModal')">Close</button>
      </footer>
    </div>
  </div>

  {{-- Edit --}}
  <div class="modal-backdrop" id="editModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="editForm">
        @csrf @method('PUT')
        <header class="modal-head">
          <div><h2>Edit supplier</h2><p id="editSubtitle"></p></div>
          <button type="button" onclick="closeModal('editModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Supplier name *</label><input name="name" id="e_name" required></div>
          <div class="field"><label>Contact person</label><input name="contact_person" id="e_contact"></div>
          <div class="field"><label>Phone</label><input name="phone" id="e_phone"></div>
          <div class="field"><label>Email</label><input name="email" id="e_email" type="email"></div>
          <div class="field full"><label>Address</label><input name="address" id="e_address"></div>
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

  async function viewSupplier(id) {
    const res = await fetch(`/app/suppliers/${id}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Could not load supplier');
    const s = await res.json();

    document.getElementById('viewName').textContent = s.name;
    document.getElementById('viewSubtitle').textContent = s.contact_person || '—';

    document.getElementById('viewBody').innerHTML = `
      <div><span>Contact</span><b>${esc(s.contact_person || '—')}</b></div>
      <div><span>Phone</span><b>${esc(s.phone || '—')}</b></div>
      <div><span>Email</span><b>${esc(s.email || '—')}</b></div>
      <div><span>Address</span><b>${esc(s.address || '—')}</b></div>
      <div><span>Balance</span><b>TSh ${Math.round(s.balance).toLocaleString()}</b></div>
      <div><span>Purchases</span><b>${s.purchases_count} orders · TSh ${Math.round(s.purchases_total).toLocaleString()}</b></div>
    `;
    openModal('viewModal');
  }

  function editSupplier(s) {
    document.getElementById('editSubtitle').textContent = s.name;
    document.getElementById('editForm').action = '/app/suppliers/' + s.id;
    document.getElementById('e_name').value = s.name;
    document.getElementById('e_contact').value = s.contact_person || '';
    document.getElementById('e_phone').value = s.phone || '';
    document.getElementById('e_email').value = s.email || '';
    document.getElementById('e_address').value = s.address || '';
    openModal('editModal');
  }

  function esc(s) { return String(s==null?'':s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  </script>
@endsection
