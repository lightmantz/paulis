@extends('layouts.business')
@section('title', 'Sales')
@section('page-title', 'Sales')

@push('head')
<style>
.filter-bar{display:grid;grid-template-columns:180px 1fr 1fr 1fr;gap:10px;align-items:end;padding:14px 16px;border-bottom:1px solid var(--line);background:#fbfcfe}
.filter-bar label{display:block;font-size:9px;font-weight:800;color:#667085;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px}
.filter-bar input,.filter-bar select{width:100%;border:1px solid var(--line);border-radius:8px;padding:9px 11px;background:#fff;font-size:12px}
.filter-bar .actions{display:flex;gap:6px;align-items:end}
.filter-bar .actions button{height:39px;white-space:nowrap}
.hidden{display:none!important}

.totals{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;padding:14px 16px;border-bottom:1px solid var(--line);background:#fafbff}
.totals article{background:#fff;border:1px solid var(--line);border-radius:9px;padding:11px 13px}
.totals span{display:block;font-size:9px;color:#667085;margin-bottom:4px}
.totals b{font-size:15px;color:#2a225e;overflow-wrap:anywhere}

.export-row{display:flex;justify-content:flex-end;gap:8px;padding:12px 16px;border-bottom:1px solid var(--line)}
.export-row button{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:8px 12px;font-size:11px;font-weight:800;color:#344054;cursor:pointer}
.export-row button:hover{border-color:var(--p);color:var(--p)}

@media(max-width:900px){
  .filter-bar{grid-template-columns:1fr 1fr}
  .filter-bar .actions{grid-column:1/-1}
  .filter-bar .actions button{flex:1}
  .totals{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:520px){
  .filter-bar{grid-template-columns:1fr}
  .totals{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">SALES</p>
      <h1>All Sales</h1>
      <p>Browse every completed sale. Filter by day, month or year — then export.</p>
    </div>
  </div>

  <section class="panel">
    {{-- Filter bar --}}
    <form class="filter-bar" method="GET" id="filterForm">
      <div>
        <label for="filter">Filter by</label>
        <select name="filter" id="filter" onchange="applyFilter()">
          <option value="all"   @selected($filter === 'all')>All sales</option>
          <option value="day"   @selected($filter === 'day')>Specific day</option>
          <option value="month" @selected($filter === 'month')>Specific month</option>
          <option value="year"  @selected($filter === 'year')>Specific year</option>
          <option value="range" @selected($filter === 'range')>Date range</option>
        </select>
      </div>

      <div id="block-day" class="{{ $filter === 'day' ? '' : 'hidden' }}">
        <label for="date">Pick a day</label>
        <input type="date" name="date" id="date" value="{{ $date }}">
      </div>

      <div id="block-month" class="{{ $filter === 'month' ? '' : 'hidden' }}">
        <label for="month">Pick a month</label>
        <input type="month" name="month" id="month" value="{{ $month }}">
      </div>

      <div id="block-year" class="{{ $filter === 'year' ? '' : 'hidden' }}">
        <label for="year">Pick a year</label>
        <input type="number" name="year" id="year" min="2000" max="2100" value="{{ $year }}">
      </div>

      <div id="block-range" class="{{ $filter === 'range' ? '' : 'hidden' }}" style="grid-column:span 2;display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label for="from">From</label>
          <input type="date" name="from" id="from" value="{{ $from }}">
        </div>
        <div>
          <label for="to">To</label>
          <input type="date" name="to" id="to" value="{{ $to }}">
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="primary">Apply</button>
        <a href="{{ route('business.sales') }}" class="secondary" style="text-decoration:none;padding:9px 14px;border-radius:8px;border:1px solid var(--line);font-size:11px;font-weight:800;color:#344054">Reset</a>
      </div>
    </form>

    {{-- Totals --}}
    <div class="totals">
      <article><span>Transactions</span><b>{{ number_format($totals['count']) }}</b></article>
      <article><span>Subtotal</span><b>TSh {{ number_format($totals['subtotal']) }}</b></article>
      <article><span>Discount</span><b>TSh {{ number_format($totals['discount']) }}</b></article>
      <article><span>Total</span><b>TSh {{ number_format($totals['total']) }}</b></article>
      <article><span>Balance due</span><b>TSh {{ number_format($totals['balance']) }}</b></article>
    </div>

    {{-- Export row --}}
    <div class="export-row">
      <button type="button" onclick="exportAs('csv')">📄 CSV</button>
      <button type="button" onclick="exportAs('excel')">📊 Excel</button>
      <button type="button" onclick="exportAs('pdf')">📕 PDF</button>
    </div>

    {{-- Sales table --}}
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Invoice</th><th>Date</th><th>Customer</th><th>Item(s)</th>
            <th>Payment</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($sales as $s)
            <tr>
              <td><b>{{ $s->invoice_no }}</b></td>
              <td>{{ optional($s->created_at)->format('d M Y · H:i') }}</td>
              <td>{{ $s->customer?->name ?? 'Walk-in customer' }}</td>
              <td>
                @php
                  $first = $s->items->first();
                  $more  = max(0, $s->items->count() - 1);
                @endphp
                {{ $first?->product?->name ?? '—' }}{{ $more > 0 ? " × +$more" : '' }}
              </td>
              <td>{{ $s->payment_method }}</td>
              <td><b>TSh {{ number_format($s->total) }}</b></td>
              <td>TSh {{ number_format($s->paid) }}</td>
              <td>{{ $s->balance > 0 ? 'TSh ' . number_format($s->balance) : '—' }}</td>
              <td>
                <span class="pill @if ($s->balance > 0) warn @endif">
                  {{ $s->balance > 0 ? 'Part paid' : 'Paid' }}
                </span>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="empty">No sales found for this filter.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $sales->links() }}</div>
  </section>

  <script>
    function applyFilter() {
      var f = document.getElementById('filter').value;
      document.getElementById('block-day').classList.add('hidden');
      document.getElementById('block-month').classList.add('hidden');
      document.getElementById('block-year').classList.add('hidden');
      document.getElementById('block-range').classList.add('hidden');

      if (f === 'day')   document.getElementById('block-day').classList.remove('hidden');
      if (f === 'month') document.getElementById('block-month').classList.remove('hidden');
      if (f === 'year')  document.getElementById('block-year').classList.remove('hidden');
      if (f === 'range') document.getElementById('block-range').classList.remove('hidden');

      document.getElementById('filterForm').submit();
    }

    function exportAs(kind) {
      var form = document.getElementById('filterForm');
      var params = new URLSearchParams(new FormData(form));
      var route = {
        csv:   "{{ route('business.sales.export.csv') }}",
        excel: "{{ route('business.sales.export.excel') }}",
        pdf:   "{{ route('business.sales.export.pdf') }}",
      }[kind];
      window.location.href = route + '?' + params.toString();
    }
  </script>
@endsection
