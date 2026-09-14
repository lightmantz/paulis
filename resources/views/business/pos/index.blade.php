@extends('layouts.business')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@push('head')
<style>
.pos-layout{display:grid;grid-template-columns:1.55fr .9fr;gap:13px}
.product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:12px}
.product-tile{border:1px solid var(--line);background:#fff;border-radius:9px;padding:12px;text-align:left;cursor:pointer;transition:.15s}
.product-tile:hover{border-color:var(--p);box-shadow:0 3px 12px #6755d91a;transform:translateY(-1px)}
.product-tile[disabled]{opacity:.45;cursor:not-allowed}
.product-tile .prod-icon{height:70px;border-radius:7px;background:linear-gradient(135deg,#f2f0ff,#e7efff);display:grid;place-items:center;font-size:27px;margin-bottom:9px;position:relative}
.stock-flag{position:absolute;top:6px;right:6px;background:#fff;border-radius:20px;padding:3px 6px;font-size:7px;color:#227b5f;box-shadow:0 2px 8px #0001}
.stock-flag.low{background:#fff3dc;color:#a76813}
.stock-flag.out{background:#feecec;color:#ac3e3e}
.product-tile b{font-size:11px;display:block;line-height:1.3}
.product-tile small{font-size:9px;color:var(--muted);display:block;margin-top:3px}
.product-tile strong{display:block;color:var(--p);margin-top:7px;font-size:12px}
.pos-filter{display:grid;grid-template-columns:1fr 180px;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line)}
.pos-filter input,.pos-filter select{border:1px solid var(--line);border-radius:8px;padding:10px 11px;font-size:12px;background:#fff;width:100%}
.cart{padding:14px;max-height:640px;overflow:auto}
.cart-line{display:grid;grid-template-columns:1fr auto;gap:8px;padding:11px 0;border-bottom:1px solid var(--line);align-items:start}
.cart-line b{font-size:11px;display:block;line-height:1.3}
.cart-line small{font-size:9px;color:var(--muted);display:block;margin-top:3px}
.cart-qty{display:flex;align-items:center;gap:5px;margin-top:6px}
.cart-qty button{width:26px;height:26px;border:1px solid var(--line);background:#fff;border-radius:6px;font-weight:800;color:#5143bd;cursor:pointer}
.cart-qty b{font-size:12px;min-width:20px;text-align:center}
.cart-remove{border:0!important;background:none!important;color:#b54848!important;width:auto!important;font-size:9px;margin-left:6px}
.empty-cart{text-align:center;padding:40px 10px;color:var(--muted)}
.empty-cart b{display:block;color:var(--ink);margin-bottom:5px;font-size:13px}
.summary-row{display:flex;justify-content:space-between;padding:8px 0;font-size:11px}
.summary-row span{color:var(--muted)}
.cart-total{display:flex;justify-content:space-between;font-size:16px;padding:14px 0;border-top:1px solid var(--line);margin-top:10px}
.cart-total b:last-child{color:var(--p)}
.pay{width:100%;border:0;background:#21a777;color:#fff;border-radius:9px;padding:13px;font-weight:800;font-size:12px;cursor:pointer;margin-top:8px}
.pay:hover{background:#199166}
.pay[disabled]{opacity:.5;cursor:not-allowed}
.checkout-form{display:grid;gap:9px;margin-top:12px}
.checkout-form label{font-size:9px;font-weight:800;color:#344054}
.checkout-form input,.checkout-form select{border:1px solid #dfe3ea;border-radius:8px;padding:9px 10px;font-size:11px;background:#fff;width:100%}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
@media(max-width:1050px){.pos-layout{grid-template-columns:1fr}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:420px){.product-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif
  @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif

  <div class="head">
    <div><p class="eyebrow">POINT OF SALE</p><h1>New Sale</h1>
      <p>Select products, confirm the cart, complete the sale.</p></div>
  </div>

  <div class="pos-layout">
    <section class="panel">
      <div class="pos-filter">
        <input id="posSearch" placeholder="Search name, SKU or barcode…" oninput="filterProducts()">
        <select id="posCondition" onchange="filterProducts()">
          <option value="">All conditions</option>
          <option>New</option><option>Used</option><option>Refurbished</option>
        </select>
      </div>
      <div class="product-grid" id="productGrid"></div>
    </section>

    <section class="panel">
      <header style="padding:14px 16px;border-bottom:1px solid var(--line)">
        <h2 style="font-size:13px;margin:0 0 3px">Current sale</h2>
        <p style="margin:0;font-size:9px;color:var(--muted)" id="cartCount">0 items</p>
      </header>
      <div class="cart" id="cart"></div>
    </section>
  </div>

  <script>
  const PRODUCTS = @json($products);
  const CUSTOMERS = @json($customers);
  const CURRENCY = 'TSh ';
  const fmt = n => CURRENCY + Math.round(n).toLocaleString('en-US');
  let cart = [];

  function renderProducts() {
    const grid = document.getElementById('productGrid');
    const q = (document.getElementById('posSearch').value || '').toLowerCase();
    const cond = document.getElementById('posCondition').value;

    const list = PRODUCTS.filter(p =>
      (!q || [p.name, p.sku, p.barcode].join(' ').toLowerCase().includes(q)) &&
      (!cond || p.condition === cond)
    );

    if (!list.length) {
      grid.innerHTML = '<div class="empty-cart" style="grid-column:1/-1"><b>No matching products</b>Adjust your search or filters.</div>';
      return;
    }

    grid.innerHTML = list.map(p => {
      const flag = p.stock === 0 ? 'out' : p.stock <= 3 ? 'low' : '';
      return `<button class="product-tile" ${p.stock === 0 ? 'disabled' : ''} onclick="addToCart(${p.id})">
        <span class="prod-icon">${p.name[0] || '□'}
          <span class="stock-flag ${flag}">${p.stock === 0 ? 'Out' : p.stock + ' left'}</span>
        </span>
        <b>${escapeHtml(p.name)}</b>
        <small>${p.condition} · ${p.sku}</small>
        <strong>${fmt(p.selling_price)}</strong>
      </button>`;
    }).join('');
  }

  function filterProducts() { renderProducts(); }

  function addToCart(id) {
    const p = PRODUCTS.find(x => x.id === id);
    if (!p || p.stock <= 0) return;
    const line = cart.find(x => x.product_id === id);
    if (line) {
      if (line.qty >= p.stock) { alert(`Only ${p.stock} units available`); return; }
      line.qty++;
    } else {
      cart.push({ product_id: id, name: p.name, price: Number(p.selling_price), qty: 1, stock: p.stock });
    }
    renderCart();
  }

  function changeQty(id, delta) {
    const line = cart.find(x => x.product_id === id);
    if (!line) return;
    line.qty += delta;
    if (line.qty < 1) cart = cart.filter(x => x.product_id !== id);
    if (line.qty > line.stock) line.qty = line.stock;
    renderCart();
  }

  function removeLine(id) { cart = cart.filter(x => x.product_id !== id); renderCart(); }
  function clearCart() { if (cart.length && confirm('Clear the current sale?')) { cart = []; renderCart(); } }

  function renderCart() {
    const el = document.getElementById('cart');
    const count = cart.reduce((s, x) => s + x.qty, 0);
    document.getElementById('cartCount').textContent = count + ' item' + (count === 1 ? '' : 's');

    if (!cart.length) {
      el.innerHTML = '<div class="empty-cart"><b>Cart is empty</b>Click a product to add it.</div>';
      return;
    }

    const subtotal = cart.reduce((s, x) => s + x.qty * x.price, 0);

    el.innerHTML = cart.map(l => `
      <div class="cart-line">
        <div>
          <b>${escapeHtml(l.name)}</b>
          <small>${l.qty} × ${fmt(l.price)}</small>
          <div class="cart-qty">
            <button onclick="changeQty(${l.product_id},-1)">−</button>
            <b>${l.qty}</b>
            <button onclick="changeQty(${l.product_id},1)">＋</button>
            <button class="cart-remove" onclick="removeLine(${l.product_id})">Remove</button>
          </div>
        </div>
        <b>${fmt(l.qty * l.price)}</b>
      </div>
    `).join('') + `
      <div class="summary-row"><span>Subtotal</span><b>${fmt(subtotal)}</b></div>
      <div class="summary-row"><span>Items</span><b>${count}</b></div>
      <div class="cart-total"><b>Total</b><b id="cartTotal">${fmt(subtotal)}</b></div>

      <div class="checkout-form">
        <div>
          <label>Customer</label>
          <select id="payCustomer">
            <option value="">Walk-in customer</option>
            ${CUSTOMERS.map(c => `<option value="${c.id}">${escapeHtml(c.name)}${c.phone ? ' · ' + c.phone : ''}</option>`).join('')}
          </select>
        </div>
        <div>
          <label>Payment method</label>
          <select id="payMethod">
            <option>Cash</option><option>M-Pesa</option><option>Bank transfer</option><option>Credit</option>
          </select>
        </div>
        <div>
          <label>Discount (TSh)</label>
          <input id="payDiscount" type="number" min="0" value="0" oninput="recalcTotal()">
        </div>
        <div>
          <label>Amount paid (TSh)</label>
          <input id="payPaid" type="number" min="0" value="${subtotal}" oninput="recalcTotal()">
        </div>
        <div>
          <label>Document</label>
          <select id="payDocument">
            <option>Receipt</option><option>Invoice</option><option>Quotation</option>
          </select>
        </div>
        <button class="pay" onclick="completeSale()">Complete sale →</button>
        <button class="pay" style="background:#fff;border:1px solid var(--line);color:#c8443a" onclick="clearCart()">Clear cart</button>
      </div>
    `;
  }

  function recalcTotal() {
    const subtotal = cart.reduce((s, x) => s + x.qty * x.price, 0);
    const discount = Math.min(subtotal, Math.max(0, Number(document.getElementById('payDiscount')?.value || 0)));
    const paidEl = document.getElementById('payPaid');
    if (paidEl && document.activeElement !== paidEl) paidEl.value = Math.max(0, subtotal - discount);
    const total = subtotal - discount;
    document.getElementById('cartTotal').textContent = fmt(total);
  }

  function completeSale() {
    if (!cart.length) return;
    const subtotal = cart.reduce((s, x) => s + x.qty * x.price, 0);
    const discount = Math.min(subtotal, Math.max(0, Number(document.getElementById('payDiscount').value || 0)));
    const total = subtotal - discount;
    const paid = Math.max(0, Math.min(total, Number(document.getElementById('payPaid').value || 0)));
    const method = document.getElementById('payMethod').value;
    const finalPaid = method === 'Credit' ? Math.max(0, Math.min(total, paid)) : total;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('business.pos.store') }}";
    form.innerHTML = `@csrf
      <input name="customer_id" value="${document.getElementById('payCustomer').value}">
      <input name="payment_method" value="${method}">
      <input name="discount" value="${discount}">
      <input name="paid" value="${finalPaid}">
      <input name="document_type" value="${document.getElementById('payDocument').value}">
    `;
    cart.forEach((l, i) => {
      form.innerHTML += `<input name="items[${i}][product_id]" value="${l.product_id}">
                         <input name="items[${i}][qty]" value="${l.qty}">`;
    });
    document.body.appendChild(form);
    form.submit();
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // Bootstrap
  renderProducts();
  renderCart();
  </script>
@endsection
