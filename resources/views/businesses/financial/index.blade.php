@extends('layouts.business')
@section('title', 'Financial Information')
@section('page-title', 'Financial Information')

@section('content')
  <div class="head">
    <div><p class="eyebrow">FINANCIALS</p><h1>Financial Information</h1>
      <p>Computed live from your sales, purchases, expenses and stock.</p></div>
  </div>

  <div class="cards">
    <div class="card"><span class="icon">↗</span>
      <div><p>Revenue</p><strong>TSh {{ number_format($productRevenue) }}</strong><small>Total posted sales</small></div></div>
    <div class="card"><span class="icon">▦</span>
      <div><p>Cost of goods sold</p><strong>TSh {{ number_format($cogs) }}</strong><small>From sale line costs</small></div></div>
    <div class="card"><span class="icon">◈</span>
      <div><p>Gross profit</p><strong>TSh {{ number_format($gross) }}</strong><small>Margin {{ number_format($margin,1) }}%</small></div></div>
    <div class="card"><span class="icon">↘</span>
      <div><p>Operating expenses</p><strong>TSh {{ number_format($operating) }}</strong><small>Approved expenses</small></div></div>
    <div class="card"><span class="icon">✓</span>
      <div><p>Net profit</p><strong>TSh {{ number_format($net) }}</strong><small>Revenue − COGS − Expenses</small></div></div>
    <div class="card"><span class="icon">◇</span>
      <div><p>Customer receivables</p><strong>TSh {{ number_format($receivable) }}</strong><small>Owed to you</small></div></div>
    <div class="card"><span class="icon">↓</span>
      <div><p>Supplier payables</p><strong>TSh {{ number_format($payable) }}</strong><small>You owe suppliers</small></div></div>
    <div class="card"><span class="icon">▤</span>
      <div><p>Stock value</p><strong>TSh {{ number_format($stockValue) }}</strong><small>At unit cost</small></div></div>
  </div>
@endsection