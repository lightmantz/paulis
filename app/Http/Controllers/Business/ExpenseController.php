<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderByDesc('expense_date')->paginate(20);
        return view('business.expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description'    => ['required', 'string', 'max:200'],
            'category'       => ['required', 'string', 'max:80'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'payment_account'=> ['required', 'in:Cash register,M-Pesa,Bank'],
            'expense_date'   => ['required', 'date'],
            'reference'      => ['nullable', 'string', 'max:80'],
            'notes'          => ['nullable', 'string'],
        ]);

        $data['business_id'] = auth()->user()->business_id;
        $data['created_by']  = auth()->id();
        $data['approval_status'] = 'Pending Owner approval';

        Expense::create($data);

        activity('expenses')->log("Recorded expense: {$data['description']}");

        return back()->with('success', "Expense recorded.");
    }
}