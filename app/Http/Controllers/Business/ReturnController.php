<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Return_;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'customer');

        $rows = Return_::orderByDesc('id')->get();
        $sales = Sale::orderByDesc('id')->limit(50)->get(['id', 'invoice_no', 'total']);

        $customerReturns = $rows->where('type', 'Customer return');
        $supplierReturns = $rows->where('type', 'Supplier return');
        $refunds = $customerReturns->where('approval_status', 'Approved')->sum('amount');
        $pending = $rows->whereIn('approval_status', ['Awaiting Owner', 'Pending'])->count();

        return view('business.returns.index', compact(
            'tab', 'rows', 'sales', 'customerReturns', 'supplierReturns', 'refunds', 'pending'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'       => ['required', 'in:Customer return,Supplier return'],
            'sale_id'    => ['nullable', 'integer', 'exists:sales,id'],
            'item'       => ['required', 'string', 'max:200'],
            'reason'     => ['required', 'string'],
            'resolution' => ['required', 'in:Refund,Exchange,Repair,Supplier replacement'],
            'amount'     => ['required', 'numeric', 'min:0'],
        ]);

        $data['business_id']    = auth()->user()->business_id;
        $data['reference']      = $this->nextReference();
        $data['approval_status']= 'Awaiting Owner';

        $return = Return_::create($data);

        activity('returns')->performedOn($return)->log("Created {$return->type} {$return->reference}");

        return redirect()->route('business.returns')
            ->with('success', "Return {$return->reference} created.");
    }

    public function approve(Return_ $return)
    {
        if (auth()->user()->role !== 'owner') {
            return back()->with('error', 'Only the Business Owner can approve returns.');
        }

        $return->update(['approval_status' => 'Approved']);

        activity('returns')->performedOn($return)->log("Approved return {$return->reference}");

        return back()->with('success', "Return {$return->reference} approved.");
    }

    public function reject(Return_ $return)
    {
        if (auth()->user()->role !== 'owner') {
            return back()->with('error', 'Only the Business Owner can reject returns.');
        }

        $return->update(['approval_status' => 'Rejected']);

        activity('returns')->performedOn($return)->log("Rejected return {$return->reference}");

        return back()->with('success', "Return {$return->reference} rejected.");
    }

    protected function nextReference(): string
    {
        $last = Return_::withoutGlobalScopes()
            ->where('business_id', auth()->user()->business_id)
            ->orderByDesc('id')
            ->value('reference');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'RET-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
