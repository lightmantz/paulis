<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Sales Export</title>
<style>
  * { box-sizing: border-box; }
  body { font: 11px DejaVu Sans, Arial, sans-serif; color: #1f2937; margin: 0; padding: 20px; }
  header { border-bottom: 2px solid #6755d9; padding-bottom: 12px; margin-bottom: 16px; }
  header h1 { margin: 0 0 4px; font-size: 18px; }
  header p { margin: 2px 0; font-size: 10px; color: #667085; }
  .meta { display: flex; justify-content: space-between; margin-bottom: 12px; }
  .meta .block { font-size: 10px; color: #667085; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #28213f; color: #fff; text-align: left; padding: 7px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: .4px; }
  td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9.5px; vertical-align: top; }
  tr:nth-child(even) td { background: #fafbfc; }
  .right { text-align: right; }
  tfoot td { font-weight: 800; background: #f5f3ff; color: #2a225e; }
  footer { margin-top: 18px; font-size: 9px; color: #667085; text-align: center; }
</style>
</head>
<body>
  <header>
    <h1>{{ auth()->user()->business->name }}</h1>
    <p>{{ $range }}</p>
    <p>Generated: {{ now()->format('d M Y · H:i') }} · {{ $rows->count() }} transaction(s)</p>
  </header>

  <div class="meta">
    <div class="block"><b>Subtotal:</b> TSh {{ number_format($totals['subtotal']) }}</div>
    <div class="block"><b>Discount:</b> TSh {{ number_format($totals['discount']) }}</div>
    <div class="block"><b>Total:</b> TSh {{ number_format($totals['total']) }}</div>
    <div class="block"><b>Balance due:</b> TSh {{ number_format($totals['balance']) }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Invoice</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Item(s)</th>
        <th>Payment</th>
        <th class="right">Total</th>
        <th class="right">Paid</th>
        <th class="right">Balance</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $s)
        <tr>
          <td><b>{{ $s->invoice_no }}</b></td>
          <td>{{ optional($s->created_at)->format('d M Y') }}</td>
          <td>{{ $s->customer?->name ?? 'Walk-in' }}</td>
          <td>
            @php
              $items = $s->items->map(fn ($i) => ($i->product?->name ?? '—') . ' ×' . $i->qty)->implode(', ');
            @endphp
            {{ \Illuminate\Support\Str::limit($items, 60) }}
          </td>
          <td>{{ $s->payment_method }}</td>
          <td class="right">{{ number_format($s->total) }}</td>
          <td class="right">{{ number_format($s->paid) }}</td>
          <td class="right">{{ number_format($s->balance) }}</td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:#667085;padding:20px">No sales found for this filter.</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5" class="right">Totals</td>
        <td class="right">{{ number_format($totals['total']) }}</td>
        <td class="right">{{ number_format($totals['paid']) }}</td>
        <td class="right">{{ number_format($totals['balance']) }}</td>
      </tr>
    </tfoot>
  </table>

  <footer>
    {{ auth()->user()->business->name }} · Sales report · Confidential
  </footer>
</body>
</html>
