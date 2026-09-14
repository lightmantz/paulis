<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $business = auth()->user()->business;
        $settings = BusinessSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'display_name' => $business->name,
                'tin' => $business->tin,
                'address' => $business->address,
                'city' => $business->city,
                'phone' => $business->phone,
                'email' => $business->email,
                'payment_accounts' => ['Cash register', 'M-Pesa', 'Bank'],
            ]
        );

        return view('business.settings.index', compact('business', 'settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return back()->with('error', 'Only the Business Owner can change settings.');
        }

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'tin' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:200'],
            'city' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'receipt_footer' => ['nullable', 'string', 'max:200'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'quotation_prefix' => ['required', 'string', 'max:10'],
            'currency' => ['required', 'string', 'max:8'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_reorder_level' => ['required', 'integer', 'min:0'],
            'max_sales_discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'require_owner_discount_approval' => ['boolean'],
            'payment_accounts' => ['array'],
        ]);

        $settings = BusinessSetting::firstOrCreate(['business_id' => auth()->user()->business_id]);
        $settings->update($data);

        activity('settings')->log('Updated business settings');
        return back()->with('success', 'Settings saved.');
    }
}
