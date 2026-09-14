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
.po-line{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;align-items:end;padding:9px 0;border-bottom:1px solid var(--line)}
.po-line:last-child{border:0}
.po-line label{font-size:9px;font-weight:800;color:#344054;display:block;margin-bottom:4px}
.po-line input,.po-line select{border:1px solid #dfe3ea;border-radius:8px;padding:9px 10px;font-size:11px;background:#fff;width:100%}
.po-line button{background:#feecec;color:#b13d3d;border:0;border-radius:7px;padding:9px 11px;font-size:11px;font-weight:800;cursor:pointer}
.receive-block{border:1px solid var(--line);border-radius:9px;padding:12px;margin-bottom:10px;background:#fafbfc}
.receive-block h4{font-size:11px;margin:0 0 9px;color:#2a225e}
.receive-block .grid3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:8px}
.receive-block label{font-size:9px;font-weight:800;color:#344054;display:block;margin-bottom:3px}
.receive-block input{border:1px solid #dfe3ea;border-radius:7px;padding:8px 9px;font-size:11px;background:#fff;width:100%}
.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(900px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
.modal-head{display:flex;justify-content:space-between;align-items:flex-start;padding:18px 20px;border-bottom:1px solid var(--line);position:sticky;top:0;background:#fff;z-index:2}
.modal-head h2{margin:0 0 4px;font-size:17px}
.modal-head p{margin:0;color:var(--muted);font-size:10px}
.modal-head button{border:0;background:#f0f2f6;border-radius:8px;width:34px;height:34px;font-size:18px;cursor:pointer}
.modal-body{padding:18px 20px}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid var(--line)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.form-grid .field{display:flex;flex-direction:column;gap:5px}
.form-grid .full{grid-column:1/-1}
.form-grid label{font-size:10px;font-weight:800;color:#344054}
.form-grid input,.form-grid select,.form-grid textarea{border:1px solid #dfe3ea;border-radius:8px;padding:10px 11px;font-size:12px;background:#fff;outline:none}
.form-grid input:focus,.form-grid select:focus,.form-grid textarea:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(103,85,217,.09)}
.po-total-row{display:flex;justify-content:space-between;align-items:center;background:#f5f3ff;border-radius:9px;padding:12px 14px;margin:14px 0}
.po-total-row span{font-size:11px;color:#6355bc}
.po-total-row b{font-size:16px;color:#4f40ba}
@media(max-width:900px){.purchase-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.purchase-stats{grid-template-columns:1fr}.form-grid,.po-line,.receive-block .grid3{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif

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
              <tr><td colspan="8" class="empty">No purchase orders yet. Click “New purchase order”.</td></tr>
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
          <thead><tr><th>Purchase</th><th>Supplier</th><th>Date</th><th>Subtotal</th><th>Expenses</th><th>Total</th><th>Paid</th><th>Balance</th></tr></thead>
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
              </tr>
            @empty
              <tr><td colspan="8" class="empty">No received purchases yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  @endif

  {{-- New PO modal --}}
  <div class="modal-backdrop" id="newPoModal">
    <div class="modal-card">
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
                @foreach ($suppliers as $s)
                  <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="field"><label>Status</label>
              <select name="status"><option>Draft</option><option>Ordered</option></select>
            </div>
            <div class="field"><label>Order date *</label><input name="order_date" type="date" value="{{ date('Y-m-d') }}" required></div>
            <div class="field"><label>Expected date</label><input name="expected_date" type="date" value="{{ date('Y-m-d', strtotime('+7 days')) }}"></div>
            <div class="field full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
          </div>

          <h3 style="font-size:11px;margin:0 0 8px;color:#2a225e">Product lines</h3>
          <div id="poLines"></div>
          <button type="button" class="secondary" style="margin-top:8px" onclick="addPoLine()">＋ Add product line</button>

          <div class="po-total-row">
            <span>Estimated PO value</span>
            <b id="poTotal">TSh 0</b>
          </div>
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
    <div class="modal-card">
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

  function addPoLine() {
    const wrap = document.getElementById('poLines');
    const idx = wrap.querySelectorAll('.po-line').length;
    const options = PRODUCTS.map(p => `<option value="${p.id}" data-cost="${p.unit_cost}">${p.name} · ${p.sku || ''}</option>`).join('');
    wrap.insertAdjacentHTML('beforeend', `
      <div class="po-line">
        <div><label>Product</label><select name="items[${idx}][product_id]" onchange="onProductChange(this)" required><option value="">Select product…</option>${options}</select></div>
        <div><label>Qty</label><input name="items[${idx}][qty]" type="number" min="1" value="1" oninput="recalcPO()" required></div>
        <div><label>Unit cost (TSh)</label><input name="items[${idx}][unit_cost]" type="number" min="0" value="0" oninput="recalcPO()" required></div>
        <div><label>Line total</label><input class="line-total" readonly value="TSh 0"></div>
        <div><button type="button" onclick="this.closest('.po-line').remove(); recalcPO()">Remove</button></div>
      </div>`);
  }

  function onProductChange(select) {
    const cost = select.selectedOptions[0]?.dataset.cost || 0;
    const line = select.closest('.po-line');
    line.querySelector('[name*="unit_cost"]').value = Math.round(cost);
    recalcPO();
  }

  function recalcPO() {
    let total = 0;
    document.querySelectorAll('#poLines .po-line').forEach(line => {
      const qty = Number(line.querySelector('[name*="qty"]').value || 0);
      const cost = Number(line.querySelector('[name*="unit_cost"]').value || 0);
      const lineTotal = qty * cost;
      total += lineTotal;
      line.querySelector('.line-total').value = fmt(lineTotal);
    });
    document.getElementById('poTotal').textContent = fmt(total);
  }

  function openReceive(id) {
    const po = PO_ORDERS[id];
    if (!po) return alert('PO not found');

    document.getElementById('receiveSubtitle').textContent = `${po.reference} · ${po.supplier?.name || ''}`;
    document.getElementById('receiveForm').action = `/app/purchases/${id}/receive`;

    let html = '';
    po.items.forEach((item, idx) => {
      html += `
        <div class="receive-block">
          <h4>${item.product_name} — ordered ${item.qty}</h4>
          <div class="grid3">
            <div><label>Quantity received</label><input name="qty_${idx}" type="number" min="0" value="${item.qty}" required></div>
            <div><label>Actual unit cost (TSh)</label><input name="cost_${idx}" type="number" min="0" value="${Math.round(item.unit_cost)}" required></div>
            <div><label>Reference / serials</label><input name="refs_${idx}" placeholder="Optional"></div>
          </div>
        </div>`;
    });
    html += `
      <h3 style="font-size:11px;margin:14px 0 8px;color:#2a225e">Additional expenses</h3>
      <div class="form-grid">
        <div class="field"><label>Transport (TSh)</label><input name="expense_transport" type="number" min="0" value="0"></div>
        <div class="field"><label>Freight (TSh)</label><input name="expense_freight" type="number" min="0" value="0"></div>
        <div class="field"><label>Tax / clearing (TSh)</label><input name="expense_tax" type="number" min="0" value="0"></div>
        <div class="field"><label>Other (TSh)</label><input name="expense_other" type="number" min="0" value="0"></div>
        <div class="field"><label>Amount paid (TSh)</label><input name="paid" type="number" min="0" value="0"></div>
        <div class="field"><label>Receipt date</label><input name="purchase_date" type="date" value="{{ date('Y-m-d') }}"></div>
        <div class="field full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
      </div>`;
    document.getElementById('receiveBody').innerHTML = html;
    openModal('receiveModal');
  }

  addPoLine();
  </script>
@endsection
