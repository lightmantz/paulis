<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\JobPart;
use App\Models\JobStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;

        $jobs = JobCard::with(['customer', 'assignee', 'parts'])
            ->orderByDesc('id')
            ->get();

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        $products  = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'selling_price', 'unit_cost', 'stock']);
        $staff     = User::where('business_id', $businessId)
            ->whereIn('role', ['owner', 'repair_person'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        // Aggregate metrics for the header
        $revenue   = $jobs->sum(fn ($j) => (float) $j->revenue);
        $expenses  = $jobs->sum(fn ($j) => $j->total_expense);
        $profit    = $revenue - $expenses;
        $margin    = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        $stats = compact('revenue', 'expenses', 'profit', 'margin');

        return view('business.service.index', compact('jobs', 'customers', 'products', 'staff', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'device'      => ['required', 'string', 'max:160'],
            'serial'      => ['nullable', 'string', 'max:80'],
            'issue'       => ['required', 'string'],
            'priority'    => ['nullable', 'in:Normal,Urgent,Warranty return'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'quoted_amount' => ['nullable', 'numeric', 'min:0'],
            'deposit'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $job = DB::transaction(function () use ($data) {
            $job = JobCard::create([
                'business_id'   => auth()->user()->business_id,
                'job_no'        => $this->nextJobNo(),
                'customer_id'   => $data['customer_id'],
                'device'        => $data['device'],
                'serial'        => $data['serial'] ?? null,
                'issue'         => $data['issue'],
                'status'        => 'received',
                'assigned_to'   => $data['assigned_to'] ?? null,
                'quoted_amount' => $data['quoted_amount'] ?? 0,
                'deposit'       => $data['deposit'] ?? 0,
                'revenue'       => 0,
                'labour_cost'   => 0,
            ]);

            JobStatusHistory::create([
                'job_card_id' => $job->id,
                'from_status' => null,
                'to_status'   => 'received',
                'user_id'     => auth()->id(),
                'notes'       => 'Job card created · priority: ' . ($data['priority'] ?? 'Normal'),
            ]);

            activity('service')->performedOn($job)->log("Created job card {$job->job_no}");

            return $job;
        });

        return redirect()->route('business.service')->with('success', "Job card {$job->job_no} created.");
    }

    public function show(JobCard $job)
    {
        $job->load(['customer', 'assignee', 'parts.product', 'history.user']);

        return response()->json([
            'job'       => $job,
            'parts'     => $job->parts->map(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'qty'        => $p->qty,
                'unit_cost'  => (float) $p->unit_cost,
                'line_total' => (float) ($p->qty * $p->unit_cost),
            ]),
            'history'   => $job->history->map(fn ($h) => [
                'from' => $h->from_status,
                'to'   => $h->to_status,
                'user' => $h->user?->name ?? 'System',
                'notes'=> $h->notes,
                'time' => $h->created_at->format('d M Y · H:i'),
            ]),
            'financials'=> [
                'revenue'   => (float) $job->revenue,
                'parts'     => $job->parts_cost,
                'labour'    => (float) $job->labour_cost,
                'expenses'  => $job->total_expense,
                'profit'    => $job->profit,
            ],
        ]);
    }

    public function updateStatus(Request $request, JobCard $job)
    {
        $data = $request->validate([
            'status' => ['required', 'in:received,diagnosing,awaiting_approval,in_repair,ready,collected'],
            'notes'  => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($job, $data) {
            $from = $job->status;
            $job->update(['status' => $data['status']]);

            JobStatusHistory::create([
                'job_card_id' => $job->id,
                'from_status' => $from,
                'to_status'   => $data['status'],
                'user_id'     => auth()->id(),
                'notes'       => $data['notes'] ?? null,
            ]);

            activity('service')->performedOn($job)->log("Job {$job->job_no}: {$from} → {$data['status']}");
        });

        return response()->json(['ok' => true]);
    }

    public function addPart(Request $request, JobCard $job)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty'        => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['qty'] > $product->stock) {
            return response()->json(['error' => "Only {$product->stock} units available"], 422);
        }

        DB::transaction(function () use ($job, $product, $data) {
            JobPart::create([
                'job_card_id' => $job->id,
                'product_id'  => $product->id,
                'name'        => $product->name,
                'qty'         => $data['qty'],
                'unit_cost'   => $product->unit_cost,
            ]);

            $product->decrement('stock', $data['qty']);

            activity('service')->performedOn($job)->log("Issued {$data['qty']} × {$product->name} to {$job->job_no}");
        });

        return response()->json(['ok' => true]);
    }

    public function updateInvoice(Request $request, JobCard $job)
    {
        $data = $request->validate([
            'diagnosis'   => ['nullable', 'string'],
            'revenue'     => ['nullable', 'numeric', 'min:0'],
            'labour_cost' => ['nullable', 'numeric', 'min:0'],
            'warranty'    => ['nullable', 'string', 'max:60'],
        ]);

        $job->update($data);

        activity('service')->performedOn($job)->log("Updated job {$job->job_no} invoice");

        return response()->json(['ok' => true]);
    }

    protected function nextJobNo(): string
    {
        $last = JobCard::withoutGlobalScopes()
            ->where('business_id', auth()->user()->business_id)
            ->orderByDesc('id')
            ->value('job_no');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'JOB-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
