@extends('layouts.superadmin')
@section('title', 'Audit History')
@section('page-title', 'Audit History')

@section('content')
  <div class="page-head">
    <div>
      <p class="eyebrow">ACCOUNTABILITY</p>
      <h1>Audit History</h1>
      <p>{{ $events->total() }} recorded events across all tenants.</p>
    </div>
  </div>

  <div class="audit-list">
    @forelse ($events as $e)
      <article class="audit-row">
        <b>{{ $e->created_at->format('d M Y · H:i') }}</b>
        <span>{{ $e->causer?->name ?? 'System' }}</span>
        <div>{{ $e->description }}</div>
        <span>{{ class_basename($e->subject_type ?? '—') }} {{ $e->subject_id ?? '' }}</span>
      </article>
    @empty
      <div class="empty">No audit events yet.</div>
    @endforelse
  </div>

  @if ($events->hasPages())
    <div class="pager">{{ $events->links() }}</div>
  @endif
@endsection
