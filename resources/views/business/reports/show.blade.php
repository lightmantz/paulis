@extends('layouts.business')
@section('title', $title)
@section('page-title', $title)

@push('head')
<style>
.report-toolbar{display:flex;justify-content:space-between;align-items:end;gap:12px;padding:14px 16px;border-bottom:1px solid var(--line);background:#fbfcfe}
.report-toolbar form{display:flex;gap:8px;align-items:end}
.report-toolbar label{display:block;font-size:9px;font-weight:800;color:#667085;margin-bottom:4px;text-transform:uppercase;letter-spacing:.3px}
.report-toolbar input{height:38px;border:1px solid var(--line);border-radius:8px;padding:8px 10px;font-size:12px;background:#fff}
.report-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:14px 16px;border-bottom:1px solid var(--line)}
.report-stats article{background:#fafbff;border:1px solid var(--line);border-radius:9px;padding:12px}
.report-stats span{display:block;color:var(--muted);font-size:9px}
.report-stats b{display:block;font-size:15px;margin-top:4px;color:#2a225e}
@media(max-width:900px){.report-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.report-stats{grid-template-columns:1fr}.report-toolbar{display:block}.report-toolbar form{margin-top:10px;flex-wrap:wrap}}
@media print{.side,.top,.report-toolbar{display:none!important}.main{margin:0!important}.content{padding:0!important}}
</style>
@endpush

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">REPORT</p>
      <h1>{{ $title }}</h1>
      <p>Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} → {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    </div>
    <button class="secondary" onclick="window.print()">Print / save PDF</button>
  </div>

  <section class="panel">
    <div class="report-toolbar">
      <div>
        <b style="font-size:12px">{{ auth()->user()->business->name }}</b>
        <p style="margin:3px 0 0;font-size:10px;color:var(--muted)">{{ count($rows) }} records</p>
      </div>
      <form method="GET">
        <div><label>From</label><input name="from" type="date" value="{{ $from }}"></div>
        <div><label>To</label><input name="to" type="date" value="{{ $to }}"></div>
        <button class="primary" type="submit">Apply</button>
      </form>
    </div>

    <div class="report-stats">
      @foreach ($stats as $label => $value)
        <article><span>{{ $label }}</span><b>{{ $value }}</b></article>
      @endforeach
    </div>

    <div class="table-wrap">
      <table class="data">
        <thead><tr>@foreach ($heads as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>@foreach ($row as $i => $cell)<td>@if($i === 0)<b>{{ $cell }}</b>@else{{ $cell }}@endif</td>@endforeach</tr>
          @empty
            <tr><td colspan="{{ count($heads) }}" class="empty">No data in this period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <div style="text-align:right;margin-top:12px">
    <a href="{{ route('business.reports') }}" style="font-size:11px;color:var(--p);font-weight:800;text-decoration:none">← Back to report catalog</a>
  </div>
@endsection
