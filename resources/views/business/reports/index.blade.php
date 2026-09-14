@extends('layouts.business')
@section('title', 'Reports')
@section('page-title', 'Reports')

@push('head')
<style>
.report-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.report-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.report-stats span{display:block;color:var(--muted);font-size:9px}
.report-stats b{display:block;font-size:16px;margin-top:5px}
.report-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.report-card{background:#fff;border:1px solid var(--line);border-radius:11px;padding:17px;transition:.15s}
.report-card:hover{border-color:#d6cfff;box-shadow:0 10px 28px rgba(90,70,200,.08);transform:translateY(-2px)}
.report-card span{font-size:23px;display:inline-block;margin-bottom:8px;color:var(--p)}
.report-card h3{font-size:13px;margin:0 0 6px}
.report-card p{font-size:10px;color:var(--muted);margin:0 0 14px;line-height:1.5}
.report-card a{display:inline-block;border:1px solid #d7d1ff;background:#f5f2ff;color:#5947ca;border-radius:7px;padding:7px 11px;font-weight:800;font-size:10px;text-decoration:none}
@media(max-width:900px){.report-stats,.report-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.report-stats,.report-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">REPORTING</p>
      <h1>Reports</h1>
      <p>Printable reports for sales, profitability, stock, customers and service.</p>
    </div>
  </div>

  <div class="report-stats">
    <article><span>Revenue (all time)</span><b>TSh {{ number_format($stats['revenue']) }}</b><small>From posted sales</small></article>
    <article><span>Gross profit</span><b>TSh {{ number_format($stats['gross']) }}</b><small>Revenue − COGS</small></article>
    <article><span>Stock at cost</span><b>TSh {{ number_format($stats['stock_cost']) }}</b><small>Current inventory</small></article>
    <article><span>Stock at retail</span><b>TSh {{ number_format($stats['stock_retail']) }}</b><small>Potential sales value</small></article>
  </div>

  <div class="report-grid">
    @foreach ($catalog as [$key, $icon, $title, $desc])
      <article class="report-card">
        <span>{{ $icon }}</span>
        <h3>{{ $title }}</h3>
        <p>{{ $desc }}</p>
        <a href="{{ route('business.reports.show', $key) }}">Open report →</a>
      </article>
    @endforeach
  </div>
@endsection
