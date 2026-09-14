@extends('layouts.business')
@section('title', 'Stock Taking')
@section('page-title', 'Stock Taking')

@push('head')
<style>
.st-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.st-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.st-stats span{display:block;color:var(--muted);font-size:9px}
.st-stats b{display:block;font-size:16px;margin-top:5px}
.st-stats small{display:block;margin-top:5px;font-size:8px;color:#2b936e}
.st-stats article.loss b{color:#c8443a}
.st-stats article.surplus b{color:#238b66}
.st-tabs{display:flex;gap:7px;margin-bottom:14px;flex-wrap:wrap}
.st-tabs a{border:1px solid var(--line);background:#fff;color:#596171;border-radius:8px;padding:9px 13px;font-weight:700;font-size:11px;text-decoration:none}
.st-tabs a.active{background:var(--p);border-color:var(--p);color:#fff}
.st-toolbar{display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
.st-toolbar input{flex:1;border:1px solid var(--line);border-radius:8px;padding:9px 11px;font-size:11px;background:#fff;min-width:200px}
.alert-success{background:#e8f7f0;color:#20795c;border:1px solid #c8ecdd;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.count-row td{padding:9px 10px;font-size:11px}
.count-input{width:90px;border:1px solid #cfd5df;border-radius:7px;padding:7px 9px;text-align:right;font-weight:800;font-size:11px}
.count-input:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(103,85,217,.09);outline:none}
.variance-neg{color:#c8443a;font-weight:800}
.variance-pos{color:#238b66;font-weight:800}
.variance-zero{color:#8a91a1}
.st-locked{padding:14px;background:#fff8e8;border:1px solid #f0d9a7;color:#805b12;border-radius:9px;font-size:11px;margin-bottom:12px}
.reconcile-table .total-row{background:#f6f5ff;font-weight:800}
@media(max-width:900px){.st-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.st-stats{grid-template-columns:1fr}.st-toolbar{display:grid;grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if (session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">STOCK CONTROL</p>
      <h1>Stock Taking</h1>
      <p>Count physical stock, reconcile variances and post adjustments.</p>
    </div>
    @if (auth()->user()->role === 'owner')
      <form method="POST" action="{{ route('business.stock-taking.start') }}">
        @csrf
        <button class="primary" type="submit">＋ New stock take</button>
      </form>
    @endif
  </div>

  <div class="st-stats">
    <article><span>System stock</span><b>{{ number_format($stats['systemUnits']) }} units</b><small>TSh {{ number_format($stats['systemValue']) }} at cost</small></article>
    <article><span>Physical count</span><b>{{ number_format($stats['physicalUnits']) }} units</b><small>TSh {{ number_format($stats['physicalValue']) }} at cost</small></article>
    <article class="loss"><span>Shortage / lost value</span><b>{{ number_format($stats['shortageUnits']) }} units</b><small>TSh {{ number_format($stats['lostValue']) }} lost</small></article>
    <article class="surplus"><span>Surplus found</span><b>{{ number_format($stats['surplusUnits']) }} units</b><small>Verify source before posting</small></article>
  </div>

  <nav class="st-tabs">
    <a href="?view=count"        class="{{ $view === 'count'        ? 'active' : '' }}">Physical Count</a>
    <a href="?view=reconcile"    class="{{ $view === 'reconcile'    ? 'active' : '' }}">Reconciliation</a>
    <a href="?view=valuation"    class="{{ $view === 'valuation'    ? 'active' : '' }}">Valuation Report</a>
  </nav>

  @if (!$take)
    <section class="panel" style="padding:40px;text-align:center;color:var(--muted)">
      No active stock take. Click "New stock take" to begin.
    </section>
  @else
    @if ($take->status !== 'draft')
      <div class="st-locked">
        <b>Reference {{ $take->reference }}</b> · {{ ucfirst($take->status) }}
        {{ $take->posted_at ? '· posted '.$take->posted_at->diffForHumans() : '' }}
      </div>
    @endif

    @if ($view === 'count')
      <section class="panel">
        <div class="st-toolbar" style="padding:12px 14px">
          <input id="stSearch" placeholder="Search product name, SKU or barcode…" oninput="filterStRows()">
          <span style="font-size:10px;color:var(--muted)">Reference: <b>{{ $take->reference }}</b> · {{ $take->items->count() }} products</span>
          @if ($take->status === 'draft' && auth()->user()->role === 'owner')
            <form method="POST" action="{{ route('business.stock-taking.post', $take) }}" onsubmit="return confirm('Post physical counts to inventory? Stock will be updated.')">
              @csrf
              <button class="primary" type="submit">Post adjustments</button>
            </form>
          @endif
        </div>

        <div class="table-wrap">
          <table class="data">
            <thead>
              <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>System</th>
                <th>Physical count</th>
                <th>Variance</th>
                <th>Unit cost</th>
                <th>Variance value</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($take->items as $item)
                <tr class="count-row" data-search="{{ strtolower($item->product->name . ' ' . ($item->product->sku ?? '') . ' ' . ($item->product->barcode ?? '')) }}">
                  <td><b>{{ $item->product->name }}</b></td>
                  <td><code>{{ $item->product->sku }}</code></td>
                  <td>{{ $item->book_qty }}</td>
                  <td>
                    <input class="count-input" type="number" min="0" value="{{ $item->physical_qty ?? '' }}"
                           @disabled($take->status !== 'draft')
                           onchange="updateCount({{ $take->id }}, {{ $item->product_id }}, this)">
                  </td>
                  <td data-role="variance">
                    @php $v = $item->variance; @endphp
                    <span class="{{ $v === null ? 'variance-zero' : ($v < 0 ? 'variance-neg' : ($v > 0 ? 'variance-pos' : 'variance-zero')) }}">
                      {{ $v === null ? '—' : ($v > 0 ? '+' : '') . $v }}
                    </span>
                  </td>
                  <td>TSh {{ number_format($item->unit_cost) }}</td>
                  <td data-role="variance-value">
                    @php $vv = $item->variance_value; @endphp
                    <span class="{{ $vv < 0 ? 'variance-neg' : ($vv > 0 ? 'variance-pos' : 'variance-zero') }}">
                      {{ $vv == 0 ? '—' : 'TSh ' . number_format($vv) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>

    @elseif ($view === 'reconcile')
      <section class="panel">
        <div class="table-wrap">
          <table class="data reconcile-table">
            <thead><tr><th>Product</th><th>System</th><th>Physical</th><th>Variance</th><th>Unit cost</th><th>Value</th><th>Result</th></tr></thead>
            <tbody>
              @foreach ($take->items as $item)
                @if ($item->physical_qty === null) @continue @endif
                @php
                  $v = $item->variance;
                  $result = $v < 0 ? 'Shortage' : ($v > 0 ? 'Surplus' : 'Matched');
                  $cls = $v < 0 ? 'variance-neg' : ($v > 0 ? 'variance-pos' : 'variance-zero');
                @endphp
                <tr>
                  <td><b>{{ $item->product->name }}</b></td>
                  <td>{{ $item->book_qty }}</td>
                  <td>{{ $item->physical_qty }}</td>
                  <td><span class="{{ $cls }}">{{ $v > 0 ? '+' : '' }}{{ $v }}</span></td>
                  <td>TSh {{ number_format($item->unit_cost) }}</td>
                  <td><span class="{{ $cls }}">{{ $item->variance_value == 0 ? '—' : 'TSh ' . number_format($item->variance_value) }}</span></td>
                  <td><span class="pill @if($v < 0) bad @elseif($v > 0) warn @endif">{{ $result }}</span></td>
                </tr>
              @endforeach
              @if ($take->items->where('physical_qty', '!=', null)->isEmpty())
                <tr><td colspan="7" class="empty">No counts entered yet. Use the Physical Count tab.</td></tr>
              @endif
            </tbody>
          </table>
        </div>
      </section>

    @else
      <section class="panel">
        <div class="table-wrap">
          <table class="data">
            <thead><tr><th>Product</th><th>Category</th><th>System qty</th><th>Unit cost</th><th>Cost value</th><th>Selling price</th><th>Retail value</th></tr></thead>
            <tbody>
              @foreach ($products as $p)
                <tr>
                  <td><b>{{ $p->name }}</b><small>{{ $p->sku }}</small></td>
                  <td>{{ $p->category_id ? '—' : 'Uncategorised' }}</td>
                  <td>{{ $p->stock }}</td>
                  <td>TSh {{ number_format($p->unit_cost) }}</td>
                  <td><b>TSh {{ number_format($p->stock * $p->unit_cost) }}</b></td>
                  <td>TSh {{ number_format($p->selling_price) }}</td>
                  <td>TSh {{ number_format($p->stock * $p->selling_price) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    @endif
  @endif

  <script>
  const CSRF = '{{ csrf_token() }}';

  function filterStRows() {
    const q = document.getElementById('stSearch').value.toLowerCase();
    document.querySelectorAll('[data-search]').forEach(row => {
      row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
    });
  }

  async function updateCount(takeId, productId, input) {
    const val = input.value === '' ? null : Number(input.value);
    if (val === null) return;

    const res = await fetch(`/app/stock-taking/${takeId}/count`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ product_id: productId, physical_qty: val }),
    });
    if (!res.ok) { const e = await res.json().catch(() => ({})); alert(e.error || 'Could not save'); return; }
    const data = await res.json();

    const row = input.closest('tr');
    const v = data.variance;
    row.querySelector('[data-role="variance"]').innerHTML =
      `<span class="${v < 0 ? 'variance-neg' : (v > 0 ? 'variance-pos' : 'variance-zero')}">${v > 0 ? '+' : ''}${v}</span>`;
    const vv = data.variance_value;
    row.querySelector('[data-role="variance-value"]').innerHTML =
      `<span class="${vv < 0 ? 'variance-neg' : (vv > 0 ? 'variance-pos' : 'variance-zero')}">${vv == 0 ? '—' : 'TSh ' + Math.round(vv).toLocaleString()}</span>`;
  }
  </script>
@endsection
