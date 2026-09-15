@extends('layouts.business')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
  @if (session('success'))
    <div class="created-banner"><b>✓ {{ session('success') }}</b></div>
  @endif
  @if (session('error'))
    <div class="created-banner" style="background:#feecec;border-color:#f3c8c6;color:#b13d3d"><b>✕ {{ session('error') }}</b></div>
  @endif

  <div class="head">
    <div>
      <p class="eyebrow">BUSINESS CONFIGURATION</p>
      <h1>Settings</h1>
      <p>Business profile, documents, tax and stock rules.</p>
    </div>
  </div>

  <form method="POST" action="{{ route('business.settings.update') }}">
    @csrf
    @method('PUT')

    {{-- Business profile --}}
    <section class="settings-section">
      <header>
        <h2>Business profile</h2>
        <p>Shown on receipts, invoices and quotations.</p>
      </header>
      <div class="settings-body">
        <div class="form-grid">
          <div class="field">
            <label>Display name</label>
            <input name="display_name" value="{{ old('display_name', $settings->display_name) }}" required>
          </div>
          <div class="field">
            <label>TIN</label>
            <input name="tin" value="{{ old('tin', $settings->tin) }}">
          </div>
          <div class="field">
            <label>Phone</label>
            <input name="phone" value="{{ old('phone', $settings->phone) }}">
          </div>
          <div class="field">
            <label>Email</label>
            <input name="email" type="email" value="{{ old('email', $settings->email) }}">
          </div>
          <div class="field full">
            <label>Address</label>
            <input name="address" value="{{ old('address', $settings->address) }}">
          </div>
          <div class="field">
            <label>City</label>
            <input name="city" value="{{ old('city', $settings->city) }}">
          </div>
          <div class="field">
            <label>Receipt footer note</label>
            <input name="receipt_footer" value="{{ old('receipt_footer', $settings->receipt_footer) }}">
          </div>
        </div>
      </div>
    </section>

    {{-- Documents & currency --}}
    <section class="settings-section">
      <header>
        <h2>Documents &amp; currency</h2>
        <p>Prefixes used for auto-generated document numbers.</p>
      </header>
      <div class="settings-body">
        <div class="form-grid">
          <div class="field">
            <label>Invoice prefix</label>
            <input name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" required>
          </div>
          <div class="field">
            <label>Receipt prefix</label>
            <input name="receipt_prefix" value="{{ old('receipt_prefix', $settings->receipt_prefix) }}" required>
          </div>
          <div class="field">
            <label>Quotation prefix</label>
            <input name="quotation_prefix" value="{{ old('quotation_prefix', $settings->quotation_prefix) }}" required>
          </div>
          <div class="field">
            <label>Currency</label>
            <input name="currency" value="{{ old('currency', $settings->currency) }}" required>
          </div>
          <div class="field">
            <label>VAT rate (%)</label>
            <input name="vat_rate" type="number" step="0.01" min="0" max="100"
                   value="{{ old('vat_rate', $settings->vat_rate) }}" required>
          </div>
        </div>
      </div>
    </section>

    {{-- Stock & sales rules --}}
    <section class="settings-section">
      <header>
        <h2>Stock &amp; sales rules</h2>
        <p>Control low stock warnings and Sales Person discount limits.</p>
      </header>
      <div class="settings-body">
        <div class="form-grid">
          <div class="field">
            <label>Default reorder level</label>
            <input name="default_reorder_level" type="number" min="0"
                   value="{{ old('default_reorder_level', $settings->default_reorder_level) }}" required>
          </div>
          <div class="field">
            <label>Max Sales Person discount (%)</label>
            <input name="max_sales_discount_percent" type="number" step="0.01" min="0" max="100"
                   value="{{ old('max_sales_discount_percent', $settings->max_sales_discount_percent) }}" required>
          </div>
          <div class="field full">
            <label style="display:flex;align-items:center;gap:9px;font-weight:600">
              <input type="hidden" name="require_owner_discount_approval" value="0">
              <input type="checkbox" name="require_owner_discount_approval" value="1"
                     @checked(old('require_owner_discount_approval', $settings->require_owner_discount_approval))>
              Require Business Owner approval for every Sales Person discount
            </label>
          </div>
        </div>
      </div>
    </section>

    {{-- Payment accounts --}}
    <section class="settings-section">
      <header>
        <h2>Payment accounts</h2>
        <p>Which accounts are available at POS.</p>
      </header>
      <div class="settings-body">
        @php $accounts = old('payment_accounts', $settings->payment_accounts ?? ['Cash register','M-Pesa','Bank']); @endphp
        @foreach (['Cash register','M-Pesa','Bank'] as $acc)
          <label style="display:flex;align-items:center;gap:9px;padding:8px 0;font-size:12px">
            <input type="checkbox" name="payment_accounts[]" value="{{ $acc }}" @checked(in_array($acc, $accounts))>
            <span>{{ $acc }}</span>
          </label>
        @endforeach
      </div>
    </section>

    <div class="form-actions-row">
      <button type="submit" class="primary">Save settings</button>
    </div>
  </form>
@endsection
