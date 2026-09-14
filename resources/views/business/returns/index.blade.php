@extends('layouts.business')
@section('title', 'Returns')
@section('page-title', 'Returns')

@push('head')
<style>
.return-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.return-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.return-stats span{display:block;color:var(--muted);font-size:9px}
.return-stats b{display:block;font-size:16px;margin-top:5px}
.ret-tabs{display:flex;gap:7px;margin-bottom:14px;flex-wrap:wrap}
.ret-tabs a{border:1px solid var(--line);background:#fff;color:#596171;border-radius:8px;padding:9px 13px;font-weight:700;font-size:11px;text-decoration:none}
.ret-tabs a.active{background:var(--p);border-color:var(--p);color:#fff}
.alert-success{background:#e8f7f0;color:#20795c;border:1px solid #c8ecdd;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(680px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
.modal-head{display:flex;justify-content:space-between;align-items:flex-start;padding:18px 20px;border-bottom:1px solid var(--line)}
.modal-head h2{margin:0 0 4px;font-size:17px}
.modal-head p{margin:0;color:var(--muted);font-size:10px}
.modal-head button{border:0;background:#f0f2f6;border-radius:8px;width:34px;height:34px;font-size:18px;cursor:pointer}
.modal-body{padding:18px 20px}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid var(--line)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-grid .field{display:flex;flex-direction:column;gap:5px}
.form-grid .full{grid-column:1/-1}
.form-grid label{font-size:10px;font-weight:800;color:#344054}
.form-grid input,.form-grid select,.form-grid textarea{border:1px solid #dfe3ea;border-radius:8px;padding:10px 11px;font-size:12px;background:#fff;outline:none}
.form-grid input:focus,.form-grid select:focus,.form-grid textarea:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(103,85,217,.09)}
@media(max-width:900px){.return-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.return-stats{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if (session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">RETURNS &amp; REFUNDS</p>
      <h1>Returns</h1>
      <p>Customer returns linked to sales, supplier returns, refunds and replacements.</p>
    </div>
    <button class="primary" onclick="openModal('newReturnModal')">＋ New return</button>
  </div>

  <div class="return-stats">
    <article><span>Customer returns</span><b>{{ $customerReturns->count() }}</b><small>Total records</small></article>
    <article><span>Supplier returns</span><b>{{ $supplierReturns->count() }}</b><small>Total records</small></article>
    <article><span>Approved refunds</span><b>TSh {{ number_format($refunds) }}</b><small>Paid back to customers</small></article>
    <article><span>Pending approval</span><b>{{ $pending }}</b><small>Awaiting Owner review</small></article>
  </div>

  <nav class="ret-tabs">
    <a href="?tab=customer" class="{{ $tab === 'customer' ? 'active' : '' }}">Customer Returns</a>
    <a href="?tab=supplier" class="{{ $tab === 'supplier' ? 'active' : '' }}">Supplier Returns</a>
    <a href="?tab=all"      class="{{ $tab === 'all'      ? 'active' : '' }}">All Returns</a>
  </nav>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Reference</th><th>Type</th><th>Item</th><th>Linked sale</th><th>Resolution</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @php
            $list = $tab === 'customer' ? $customerReturns
                  : ($tab === 'supplier' ? $supplierReturns : $rows);
          @endphp
          @forelse ($list as $r)
            <tr>
              <td><b>{{ $r->reference }}</b></td>
              <td>{{ $r->type }}</td>
              <td>{{ $r->item }}<small>{{ $r->reason }}</small></td>
              <td>{{ $r->sale?->invoice_no ?? '—' }}</td>
              <td>{{ $r->resolution }}</td>
              <td>TSh {{ number_format($r->amount) }}</td>
              <td><span class="pill @if($r->approval_status === 'Approved') @elseif($r->approval_status === 'Rejected') bad @else warn @endif">{{ $r->approval_status }}</span></td>
              <td>
                @if ($r->approval_status === 'Awaiting Owner' && auth()->user()->role === 'owner')
                  <form method="POST" action="{{ route('business.returns.approve', $r) }}" style="display:inline">
                    @csrf
                    <button class="row-action" style="background:#e8f7f0;color:#20795c">Approve</button>
                  </form>
                  <form method="POST" action="{{ route('business.returns.reject', $r) }}" style="display:inline">
                    @csrf
                    <button class="row-action" style="background:#feecec;color:#b13d3d">Reject</button>
                  </form>
                @else
                  <span style="font-size:9px;color:#8d94a0">{{ $r->approval_status }}</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">No returns yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <div class="modal-backdrop" id="newReturnModal">
    <div class="modal-card">
      <form method="POST" action="{{ route('business.returns.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>New return</h2><p>Record a customer or supplier return.</p></div>
          <button type="button" onclick="closeModal('newReturnModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Type *</label>
            <select name="type" required>
              <option>Customer return</option>
              <option>Supplier return</option>
            </select>
          </div>
          <div class="field"><label>Linked sale</label>
            <select name="sale_id">
              <option value="">None</option>
              @foreach ($sales as $s)
                <option value="{{ $s->id }}">{{ $s->invoice_no }} · TSh {{ number_format($s->total) }}</option>
              @endforeach
            </select>
          </div>
          <div class="field full"><label>Item / description *</label><input name="item" required></div>
          <div class="field full"><label>Reason *</label><textarea name="reason" rows="2" required></textarea></div>
          <div class="field"><label>Resolution *</label>
            <select name="resolution" required>
              <option>Refund</option><option>Exchange</option><option>Repair</option><option>Supplier replacement</option>
            </select>
          </div>
          <div class="field"><label>Amount (TSh) *</label><input name="amount" type="number" min="0" required></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('newReturnModal')">Cancel</button>
          <button type="submit" class="primary">Create return</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
  </script>
@endsection
