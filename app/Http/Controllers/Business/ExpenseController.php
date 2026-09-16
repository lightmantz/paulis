<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expense::query();

        if ($s = trim((string) $request->get('q'))) {
            $q->where(function ($w) use ($s) {
                $w->where('description', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }
        if ($cat = $request->get('category')) {
            if ($cat !== 'All') $q->where('category', $cat);
        }
        if ($status = $request->get('status')) {
            if ($status !== 'All') $q->where('approval_status', $status);
        }

        $expenses = $q->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('business.expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description'     => ['required', 'string', 'max:200'],
            'category'        => ['required', 'string', 'max:80'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'payment_account' => ['required', 'in:Cash register,M-Pesa,Bank'],
            'expense_date'    => ['required', 'date'],
            'reference'       => ['nullable', 'string', 'max:80'],
            'notes'           => ['nullable', 'string'],
        ]);

        $data['business_id'] = auth()->user()->business_id;
        $data['created_by']  = auth()->id();
        $data['approval_status'] = auth()->user()->role === 'owner' ? 'Approved' : 'Pending Owner approval';

        Expense::create($data);

        activity('expenses')->log("Recorded expense: {$data['description']}");

        return back()->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense)
    {
        return response()->json([
            'id'              => $expense->id,
            'description'     => $expense->description,
            'category'        => $expense->category,
            'amount'          => (float) $expense->amount,
            'payment_account' => $expense->payment_account,
            'expense_date'    => optional($expense->expense_date)->format('Y-m-d'),
            'reference'       => $expense->reference,
            'notes'           => $expense->notes,
            'approval_status' => $expense->approval_status,
        ]);
    }

    public function approve(Expense $expense)
    {
        if (auth()->user()->role !== 'owner') {
            return back()->with('error', 'Only the Business Owner can approve expenses.');
        }

        $expense->update(['approval_status' => 'Approved']);

        activity('expenses')->performedOn($expense)->log("Approved expense: {$expense->description}");

        return back()->with('success', "{$expense->description} approved.");
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'description'     => ['required', 'string', 'max:200'],
            'category'        => ['required', 'string', 'max:80'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'payment_account' => ['required', 'in:Cash register,M-Pesa,Bank'],
            'expense_date'    => ['required', 'date'],
            'reference'       => ['nullable', 'string', 'max:80'],
            'notes'           => ['nullable', 'string'],
            'approval_status' => ['required', Rule::in(['Pending Owner approval', 'Approved', 'Rejected'])],
        ]);

        if (auth()->user()->role !== 'owner') {
            // Salespersons cannot change approval status
            unset($data['approval_status']);
        }

        $expense->update($data);

        activity('expenses')->performedOn($expense)->log("Updated expense: {$expense->description}");

        return back()->with('success', "{$expense->description} updated.");
    }

    public function destroy(Expense $expense)
    {
        $description = $expense->description;

        if ($expense->approval_status === 'Approved' && auth()->user()->role !== 'owner') {
            return back()->with('error', 'Approved expenses can only be deleted by the Business Owner.');
        }

        $expense->delete();

        activity('expenses')->log("Deleted expense: {$description}");

        return back()->with('success', "{$description} deleted.");
    }
}
