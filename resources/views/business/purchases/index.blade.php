@extends('layouts.business')
@section('title', 'Purchases')
@section('page-title', 'Purchases')

@push('head')
<style>
.purchase-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.purchase-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.purchase-stats span{display:block;color:var(--muted);font-size:9px}
.purchase-stats b{display:block;font-size:16px;margin-top:5px}
.purchase-stats small{display:block;margin-top:5px;font-size:8px;color:#2b936e}
.pur-tabs{display:flex;gap:7px;margin-bottom:14px;flex-wrap:wrap}
.pur-tabs a{border:1px solid var(--line);background:#fff;color:#596171;border-radius:8px;padding:9px 13px;font-weight:700;font-size:11px;text-decoration:none}
.pur-tabs a.active{background:var(--p);border-color:var(--p);color:#fff}
.alert-success{background:#e8f7f0;color:#20795c;border:1px solid #c8ecdd;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}

/* Row action buttons */
.row-action-icon{border:1px solid var(--line);background:#fff;border-radius:6px;padding:6px 8px;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap}
.row-action-icon.view{border-color:#d7d1ff;background:#f5f2ff;color:#5947ca}
.row-action-icon.edit{border-color:#c8e4ff;background:#eff7ff;color:#2458a3}
.row-action-icon.del{border-color:#f5c9c5;background:#feecec;color:#b13d3d}
.row-actions-cell{white-space:nowrap;display:flex;gap:5px}

/* Modals */
.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(720px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
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
.view-list table{width:100%;border-collapse:collapse;font-size:11px}
.view-list table th{background:#fafbfc;padding:8px;font-size:8px;text-transform:uppercase;color:#667085;text-align:left}
.view-list table td{padding:8px;border-top:1px solid var(--line)}

@media(max-width:650px){.purchase-stats{grid-template-columns:repeat(2,1fr)}.form-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if (session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">SUPPLY CHAIN</p>
      <h1>Purchases</h1>
      <p>Purchase orders, receiving, landed cost and supplier balances.</p>
    </div>
    <button class="primary" onclick="openModal('newPoModal')">＋ New purchase order</button>
  </div>

  <div class="purchase-stats">
    <article><span>Open purchase orders</span><b>{{ $openOrders->count() }}</b><small>{{ $orders->where('status','Ordered')->count() }} ordered</small></article>
    <article><span>Ordered value</span><b>TSh {{ number_format($orderedValue) }}</b><small>Awaiting receipt</small></article>
    <article><span>Received this session</span><b>TSh {{ number_format($receivedValue) }}</b><small>{{ $purchases->count() }} purchases</small></article>
    <article><span>Supplier payable</span><b>TSh {{ number_format($payable) }}</b><small>Outstanding balance</small></article>
  </div>

  <nav class="pur-tabs">
    <a href="?tab=orders"   class="{{ $tab === 'orders' ? 'active' : '' }}">Purchase Orders</a>
    <a href="?tab=receive"  class="{{ $tab === 'receive' ? 'active' : '' }}">Receive Products</a>
    <a href="?tab=history"  class="{{ $tab === 'history' ? 'active' : '' }}">Purchase History</a>
  </nav>

  @if ($tab === 'orders')
    <section class="panel">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>PO</th><th>Supplier</th><th>Items</th><th>Ordered</th><th>Expected</th><th>Value</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            @forelse ($orders as $o)
              <tr>
                <td><b>{{ $o->reference }}</b></td>
                <td>{{ $o->supplier?->name ?? '—' }}</td>
                <td>{{ $o->items->count() }} lines<small>{{ $o->items->sum('qty') }} units</small></td>
                <td>{{ $o->order_date?->format('d M Y') }}</td>
                <td>{{ $o->expected_date?->format('d M Y') ?? '—' }}</td>
                <td>TSh {{ number_format($o->estimated_total) }}</td>
                <td><span class="pill @if($o->status === 'Received') @elseif($o->status === 'Ordered') blue @else warn @endif">{{ $o->status }}</span></td>
                <td>
                  @if ($o->status !== 'Received')
                    <button class="row-action" onclick="openReceive({{ $o->id }})">Receive</button>
                  @else
                    <span style="font-size:9px;color:#8d94a0">Completed</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="empty">No purchase orders yet. Click "New purchase order".</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @elseif ($tab === 'receive')
    <section class="panel">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>PO</th><th>Supplier</th><th>Items</th><th>Value</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            @forelse ($openOrders as $o)
              <tr>
                <td><b>{{ $o->reference }}</b></td>
                <td>{{ $o->supplier?->name ?? '—' }}</td>
                <td>{{ $o->items->count() }} lines</td>
                <td>TSh {{ number_format($o->estimated_total) }}</td>
                <td><span class="pill warn">{{ $o->status }}</span></td>
                <td><button class="row-action" onclick="openReceive({{ $o->id }})">Receive</button></td>
              </tr>
            @empty
              <tr><td colspan="6" class="empty">No open purchase orders.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @else
    <section class="panel">
      <div class="table-wrap">
        <table class="data">
          <thead>
            <tr>
              <th>Purchase</th><th>Supplier</th><th>Date</th>
              <th>Subtotal</th><th>Expenses</th><th>Total</th>
              <th>Paid</th><th>Balance</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($purchases as $p)
              <tr>
                <td><b>{{ $p->reference }}</b></td>
                <td>{{ $p->supplier?->name ?? '—' }}</td>
                <td>{{ $p->purchase_date?->format('d M Y') }}</td>
                <td>TSh {{ number_format($p->subtotal) }}</td>
                <td>TSh {{ number_format($p->expenses_total) }}</td>
                <td><b>TSh {{ number_format($p->total) }}</b></td>
                <td class="up">TSh {{ number_format($p->paid) }}</td>
                <td class="warn-text">TSh {{ number_format($p->balance) }}</td>
                <td class="row-actions-cell">
                  <button class="row-action-icon view" onclick="viewPurchase({{ $p->id }})">View</button>
                  <button class="row-action-icon edit" onclick='editPurchase(@json($p->toArray()))'>Edit</button>
                  <form method="POST" action="{{ route('business.purchases.destroy', $p) }}" style="display:inline"
                        onsubmit="return confirm('Delete purchase {{ $p->reference }}? This will also revert the stock added by this purchase.')">
                    @csrf
                    @method('DELETE')
                    <button class="row-action-icon del" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="empty">No received purchases yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endif

  {{-- View modal --}}
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div><h2 id="vTitle">Purchase</h2><p id="vSubtitle"></p></div>
        <button type="button" onclick="closeModal('viewModal')">×</button>
      </header>
      <div class="modal-body">
        <div id="vBody"></div>
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
          <div><h2>Edit purchase</h2><p id="editSubtitle"></p></div>
          <button type="button" onclick="closeModal('editModal')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field full"><label>Reference *</label><input name="reference" id="e_reference" required></div>
            <div class="field"><label>Purchase date</label><input type="date" name="purchase_date" id="e_date"></div>
            <div class="field"><label>Status</label>
              <select name="status" id="e_status">
                <option>Received</option><option>Part received</option><option>Ordered</option><option>Cancelled</option>
              </select>
            </div>
            <div class="field"><label>Subtotal (TSh) *</label><input type="number" step="0.01" min="0" name="subtotal" id="e_subtotal" required></div>
            <div class="field"><label>Additional expenses (TSh)</label><input type="number" step="0.01" min="0" name="expenses_total" id="e_expenses"></div>
            <div class="field"><label>Amount paid (TSh) *</label><input type="number" step="0.01" min="0" name="paid" id="e_paid" required></div>
            <div class="field full"><label>Notes</label><textarea name="notes" id="e_notes" rows="3"></textarea></div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- New PO modal --}}
  <div class="modal-backdrop" id="newPoModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.purchases.orders.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>New purchase order</h2><p>Select a supplier and add product lines.</p></div>
          <button type="button" onclick="closeModal('newPoModal')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field"><label>Supplier *</label>
              <select name="supplier_id" required>
                <option value="">Select supplier…</option>
                @foreach ($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
              </select>
            </div>
            <div class="field"><label>Status</label>
              <select name="status"><option>Draft</option><option>Ordered</option></select>
            </div>
            <div class="field"><label>Order date *</label><input type="date" name="order_date" value="{{ date('Y-m-d') }}" required></div>
            <div class="field"><label>Expected date</label><input type="date" name="expected_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}"></div>
            <div class="field full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
          </div>

          <h3 style="font-size:11px;margin:14px 0 8px;color:#2a225e">Product lines</h3>
          <div id="poLines"></div>
          <button type="button" class="secondary" style="margin-top:8px" onclick="addPoLine()">＋ Add product line</button>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('newPoModal')">Cancel</button>
          <button type="submit" class="primary">Create purchase order</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- Receive modal --}}
  <div class="modal-backdrop" id="receiveModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="receiveForm">
        @csrf
        <header class="modal-head">
          <div><h2>Receive products</h2><p id="receiveSubtitle"></p></div>
          <button type="button" onclick="closeModal('receiveModal')">×</button>
        </header>
        <div class="modal-body" id="receiveBody"></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('receiveModal')">Cancel</button>
          <button type="submit" class="primary">Complete receipt</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
  const PO_ORDERS = @json($orders->keyBy('id'));
  const PRODUCTS  = @json($products);
  const CURRENCY  = 'TSh ';
  const fmt = n => CURRENCY + Math.round(n).toLocaleString('en-US');

  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

  /* ── Purchase: View ──────────────────────────────────── */
  async function viewPurchase(id) {
    const res = await fetch('/app/purchases/' + id, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Could not load purchase');
    const p = await res.json();

    document.getElementById('vTitle').textContent = p.reference;
    document.getElementById('vSubtitle').textContent = p.supplier + ' · ' + p.purchase_date;

    var itemRows = p.items.map(function (i) {
      return '<tr><td>' + i.name + '</td><td>' + i.qty + '</td><td>' + fmt(i.unit_cost) + '</td><td><b>' + fmt(i.line_total) + '</b></td></tr>';
    }).join('');

    var expenseRows = p.expenses.length
      ? p.expenses.map(function (e) { return '<tr><td>' + e.name + '</td><td>' + fmt(e.amount) + '</td></tr>'; }).join('')
      : '<tr><td colspan="2" style="color:#8d94a0">No additional expenses</td></tr>';

    document.getElementById('vBody').innerHTML = `
      <div class="view-list">
        <div><span>Status</span><b>${p.status}</b></div>
        <div><span>Subtotal</span><b>${fmt(p.subtotal)}</b></div>
        <div><span>Additional expenses</span><b>${fmt(p.expenses_total)}</b></div>
        <div><span>Total</span><b>${fmt(p.total)}</b></div>
        <div><span>Paid</span><b>${fmt(p.paid)}</b></div>
        <div><span>Balance</span><b>${fmt(p.balance)}</b></div>
        <div><span>Notes</span><b>${p.notes || '—'}</b></div>
      </div>

      <h3 style="font-size:11px;margin:18px 0 8px;color:#2a225e">Items</h3>
      <table><thead><tr><th>Product</th><th>Qty</th><th>Unit cost</th><th>Line total</th></tr></thead>
        <tbody>${itemRows}</tbody></table>

      <h3 style="font-size:11px;margin:18px 0 8px;color:#2a225e">Additional expenses</h3>
      <table><thead><tr><th>Description</th><th>Amount</th></tr></thead>
        <tbody>${expenseRows}</tbody></table>
    `;
    openModal('viewModal');
  }

  /* ── Purchase: Edit ──────────────────────────────────── */
  function editPurchase(p) {
    document.getElementById('editSubtitle').textContent = p.reference;
    document.getElementById('editForm').action = '/app/purchases/' + p.id;

    document.getElementById('e_reference').value = p.reference;
    document.getElementById('e_date').value      = p.purchase_date || '';
    document.getElementById('e_status').value    = p.status;
    document.getElementById('e_subtotal').value  = Math.round(p.subtotal);
    document.getElementById('e_expenses').value  = Math.round(p.expenses_total || 0);
    document.getElementById('e_paid').value      = Math.round(p.paid);
    document.getElementById('e_notes').value     = p.notes || '';

    openModal('editModal');
  }

  /* ── New PO ──────────────────────────────────────────── */
  function addPoLine() {
    const wrap = document.getElementById('poLines');
    const idx = wrap.querySelectorAll('.po-line').length;
    const options = PRODUCTS.map(p => `<option value="${p.id}" data-cost="${p.unit_cost}">${p.name} · ${p.sku || ''}</option>`).join('');
    wrap.insertAdjacentHTML('beforeend', `
      <div class="po-line" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:end;padding:9px 0;border-bottom:1px solid var(--line)">
        <div><label>Product</label><select name="items[${idx}][product_id]" onchange="onProductChange(this)" required><option value="">Select…</option>${options}</select></div>
        <div><label>Qty</label><input name="items[${idx}][qty]" type="number" min="1" value="1" required></div>
        <div><label>Unit cost</label><input name="items[${idx}][unit_cost]" type="number" min="0" value="0" required></div>
        <div><button type="button" class="row-action-icon del" onclick="this.closest('.po-line').remove()">Remove</button></div>
      </div>`);
  }

  function onProductChange(select) {
    var cost = select.selectedOptions[0]?.dataset.cost || 0;
    select.closest('.po-line').querySelector('[name*="unit_cost"]').value = Math.round(cost);
  }

  /* ── Receive ────────────────────────────────────────── */
  function openReceive(id) {
    const po = PO_ORDERS[id];
    if (!po) return alert('PO not found');

    document.getElementById('receiveSubtitle').textContent = po.reference + ' · ' + (po.supplier?.name || '');
    document.getElementById('receiveForm').action = '/app/purchases/' + id + '/receive';

    var html = '';
    po.items.forEach(function (item, idx) {
      html += `
        <div style="border:1px solid var(--line);border-radius:9px;padding:12px;margin-bottom:10px;background:#fafbfc">
          <h4 style="font-size:11px;margin:0 0 9px;color:#2a225e">${item.product_name} — ordered ${item.qty}</h4>
          <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:8px">
            <div><label>Qty received</label><input name="qty_${idx}" type="number" min="0" value="${item.qty}" required></div>
            <div><label>Actual unit cost</label><input name="cost_${idx}" type="number" min="0" value="${Math.round(item.unit_cost)}" required></div>
            <div><label>Reference</label><input name="refs_${idx}"></div>
          </div>
        </div>`;
    });
    html += `
      <div class="form-grid" style="margin-top:12px">
        <div class="field"><label>Transport (TSh)</label><input name="expense_transport" type="number" min="0" value="0"></div>
        <div class="field"><label>Freight (TSh)</label><input name="expense_freight" type="number" min="0" value="0"></div>
        <div class="field"><label>Tax (TSh)</label><input name="expense_tax" type="number" min="0" value="0"></div>
        <div class="field"><label>Other (TSh)</label><input name="expense_other" type="number" min="0" value="0"></div>
        <div class="field"><label>Amount paid (TSh)</label><input name="paid" type="number" min="0" value="0"></div>
        <div class="field"><label>Receipt date</label><input name="purchase_date" type="date" value="{{ date('Y-m-d') }}"></div>
      </div>`;
    document.getElementById('receiveBody').innerHTML = html;
    openModal('receiveModal');
  }

  addPoLine();
  </script>
@endsection
