@extends('layouts.business')

@section('title', $page ?? 'Coming soon')
@section('page-title', $page ?? 'Coming soon')

@section('content')
  <div class="head">
    <div>
      <p class="eyebrow">COMING IN A LATER PHASE</p>
      <h1>{{ $page ?? 'This module' }}</h1>
      <p>This workspace is scheduled for a later phase of the build.</p>
    </div>
  </div>

  <section class="panel" style="min-height:420px;background:#fff;border:1px solid var(--line);
           border-radius:10px;display:grid;place-items:center;text-align:center;padding:40px">
    <div>
      <div style="width:70px;height:70px;border-radius:18px;background:#eeebff;color:#6755d9;
                  display:grid;place-items:center;font-size:32px;margin:0 auto 18px">⌛</div>
      <h2 style="font-size:20px;margin:0 0 8px">{{ $page ?? 'This module' }}</h2>
      <p style="max-width:520px;color:#667085;font-size:12px;line-height:1.65;margin:0 auto">
        This workspace will be delivered in a future phase.
      </p>
      <a href="{{ route('business.dashboard') }}"
         style="display:inline-block;margin-top:22px;background:#6755d9;color:#fff;
                text-decoration:none;padding:11px 18px;border-radius:9px;font-weight:700;font-size:11px">
        ← Back to dashboard
      </a>
    </div>
  </section>
@endsection
