@extends('layouts.business')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@push('head')
<style>
.pos-layout{display:grid;grid-template-columns:1.55fr .9fr;gap:13px}
.pos-filter{display:grid;grid-template-columns:1fr 180px;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line)}
.pos-filter input,.pos-filter select{border:1px solid var(--line);border-radius:8px;padding:10px 11px;font-size:12px;background:#fff;width:100%}
.product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:12px}
.product-tile{border:1px solid var(--line);background:#fff;border-radius:10px;padding:10px;text-align:left;cursor:pointer;transition:.15s;overflow:hidden;display:flex;flex-direction:column}
.product-tile:hover{border-color:var(--p);box-shadow:0 4px 14px rgba(103,85,217,.10);transform:translateY(-2px)}
.product-tile .prod-photo{height:120px;border-radius:8px;margin-bottom:10px;position:relative;overflow:hidden;display:grid;place-items:center;background-color:#f2f4f8;background-size:cover;background-position:center;color:#98a0ad;font-size:22px}
.product-tile .stock-flag{position:absolute;top:6px;right:6px;background:#fff;border-radius:20px;padding:3px 6px;font-size:7px;color:#227b5f;box-shadow:0 2px 8px #0001;font-weight:800}
.product-tile .stock-flag.low{background:#fff3dc;color:#a76813}
.product-tile .stock-flag.out{background:#feecec;color:#ac3e3e}
.product-tile b{font-size:11px;display:block;line-height:1.3;overflow:hidden;text-overflow:ellipsis}
.product-tile small{font-size:9px;color:var(--muted);display:block;margin-top:3px}
.product-tile strong{display:block;color:var(--p);margin-top:auto;font-size:12px;padding-top:7px}
.empty{text-align:center;padding:40px 10px;color:var(--muted)}
.empty b{display:block;color:var(--ink);margin-bottom:5px;font-size:13px}
@media(max-width:1050px){.pos-layout{grid-template-columns:1fr}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:420px){.product-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">POINT OF SALE</p>
      <h1>New Sale</h1>
      <p>Select products, confirm the cart, complete the sale.</p>
    </div>
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

    <section class="panel" style="padding:14px">
      <h2 style="font-size:13px;margin:0 0 4px">Current sale</h2>
      <p style="margin:0;color:var(--muted);font-size:9px">Cart will be built here soon.</p>
    </section>
  </div>

  <script>
    window.PRODUCTS = {!! json_encode($products ?? []) !!};

    function money(v) { return 'TSh ' + Math.round(Number(v) || 0).toLocaleString('en-US'); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    function renderProducts() {
      var grid = document.getElementById('productGrid');
      if (!grid) return;
      var q = (document.getElementById('posSearch').value || '').toLowerCase();
      var cond = document.getElementById('posCondition').value;

      var list = window.PRODUCTS.filter(function (p) {
        return (!q || [p.name, p.sku, p.barcode].join(' ').toLowerCase().indexOf(q) !== -1)
            && (!cond || p.condition === cond);
      });

      if (!list.length) {
        grid.innerHTML = '<div class="empty" style="grid-column:1/-1"><b>No products yet</b>Add products in Inventory to see them here.</div>';
        return;
      }

      grid.innerHTML = list.map(function (p) {
        var flag = p.stock === 0 ? 'out' : p.stock <= 3 ? 'low' : '';
        var style = p.photo_url ? 'background-image:url(\'' + p.photo_url.replace(/'/g, "\\'") + '\');' : '';
        var inner = p.photo_url ? '' : '▱';
        return '<button type="button" class="product-tile" data-id="' + p.id + '">' +
                 '<span class="prod-photo" style="' + style + '">' + inner +
                   '<span class="stock-flag ' + flag + '">' + (p.stock === 0 ? 'Out' : p.stock + ' left') + '</span>' +
                 '</span>' +
                 '<b>' + esc(p.name) + '</b>' +
                 '<small>' + esc(p.condition) + ' · ' + esc(p.sku) + '</small>' +
                 '<strong>' + money(p.selling_price) + '</strong>' +
               '</button>';
      }).join('');
    }

    function filterProducts() { renderProducts(); }

    renderProducts();
  </script>
@endsection
