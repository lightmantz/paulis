<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SaleController extends Controller
{
    /**
     * Build the filtered query based on the request.
     */
    protected function filteredQuery(Request $request)
    {
        $q = Sale::with(['customer', 'items.product'])->orderByDesc('created_at');

        $filter = $request->get('filter', 'all'); // all | day | month | year
        $date   = $request->get('date');           // YYYY-MM-DD
        $month  = $request->get('month');          // YYYY-MM
        $year   = $request->get('year');           // YYYY
        $from   = $request->get('from');           // YYYY-MM-DD
        $to     = $request->get('to');             // YYYY-MM-DD

        switch ($filter) {
            case 'day':
                if ($date) $q->whereDate('created_at', $date);
                break;

            case 'month':
                if ($month) {
                    [$y, $m] = explode('-', $month);
                    $q->whereYear('created_at', $y)->whereMonth('created_at', $m);
                }
                break;

            case 'year':
                if ($year) $q->whereYear('created_at', $year);
                break;

            case 'range':
                if ($from && $to) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($from)->startOfDay(),
                        Carbon::parse($to)->endOfDay(),
                    ]);
                }
                break;

            case 'all':
            default:
                // no date constraint
                break;
        }

        return $q;
    }

    public function index(Request $request)
    {
        $sales   = $this->filteredQuery($request)->paginate(25)->withQueryString();
        $totals  = $this->totals($request);
        $filter  = $request->get('filter', 'all');
        $date    = $request->get('date');
        $month   = $request->get('month');
        $year    = $request->get('year');
        $from    = $request->get('from');
        $to      = $request->get('to');

        return view('business.sales.index', compact(
            'sales', 'totals', 'filter', 'date', 'month', 'year', 'from', 'to'
        ));
    }

    /**
     * Compute totals for the current filter (independent of pagination).
     */
    protected function totals(Request $request): array
    {
        $base = $this->filteredQuery($request)->reorder()->getQuery();
        $rows = $base->get();

        return [
            'count'     => $rows->count(),
            'subtotal'  => (float) $rows->sum('subtotal'),
            'discount'  => (float) $rows->sum('discount'),
            'total'     => (float) $rows->sum('total'),
            'paid'      => (float) $rows->sum('paid'),
            'balance'   => (float) $rows->sum('balance'),
        ];
    }

    /* ── Export endpoints ──────────────────────────────────── */

    public function exportCsv(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();

        $filename = 'sales-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'Invoice', 'Date', 'Customer', 'Item(s)', 'Payment',
            'Subtotal', 'Discount', 'Total', 'Paid', 'Balance', 'Status',
        ];

        $callback = function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $s) {
                fputcsv($out, [
                    $s->invoice_no,
                    optional($s->created_at)->format('Y-m-d H:i'),
                    $s->customer?->name ?? 'Walk-in',
                    $s->items->map(fn ($i) => ($i->product?->name ?? '—') . ' ×' . $i->qty)->implode(', '),
                    $s->payment_method,
                    number_format($s->subtotal, 2, '.', ''),
                    number_format($s->discount, 2, '.', ''),
                    number_format($s->total, 2, '.', ''),
                    number_format($s->paid, 2, '.', ''),
                    number_format($s->balance, 2, '.', ''),
                    $s->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();

        $filename = 'sales-' . now()->format('Y-m-d-His') . '.csv';

        // Simple "Excel-compatible" CSV with BOM so Excel opens it correctly.
        // (Avoids pulling maatwebsite/excel into this screen.)
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'Invoice', 'Date', 'Customer', 'Item(s)', 'Payment',
            'Subtotal', 'Discount', 'Total', 'Paid', 'Balance', 'Status',
        ];

        $callback = function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens it as UTF-8
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns);
            foreach ($rows as $s) {
                fputcsv($out, [
                    $s->invoice_no,
                    optional($s->created_at)->format('Y-m-d H:i'),
                    $s->customer?->name ?? 'Walk-in',
                    $s->items->map(fn ($i) => ($i->product?->name ?? '—') . ' ×' . $i->qty)->implode(', '),
                    $s->payment_method,
                    $s->subtotal,
                    $s->discount,
                    $s->total,
                    $s->paid,
                    $s->balance,
                    $s->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $rows    = $this->filteredQuery($request)->get();
        $totals  = $this->totals($request);
        $filter  = $request->get('filter', 'all');
        $range   = $this->rangeLabel($request);

        $html = view('business.sales.pdf', compact('rows', 'totals', 'range'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape');

        $filename = 'sales-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function rangeLabel(Request $request): string
    {
        $filter = $request->get('filter', 'all');

        return match ($filter) {
            'day'   => 'Day: '   . ($request->get('date') ?: '—'),
            'month' => 'Month: ' . ($request->get('month') ?: '—'),
            'year'  => 'Year: '  . ($request->get('year') ?: '—'),
            'range' => 'Range: ' . ($request->get('from') ?: '—') . ' → ' . ($request->get('to') ?: '—'),
            default => 'All sales',
        };
    }
}
