@extends('layouts.business')
@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('content')
  @if (session('success'))<div class="created-banner"><b>✓ {{ session('success') }}</b></div>@endif

  <div class="head">
    <div><p class="eyebrow">EXPENSES</p><h1>Expenses</h1>
      <p>Operational spending with receipts and approvals.</p></div>
    <button class="primary" onclick="openAdd()">＋ Record expense</button>
  </div>

  <section class="panel">
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Account</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          @forelse ($expenses as $e)
            <tr>
              <td>{{ $e->expense_date?->format('d M Y') }}</td>
              <td><b>{{ $e->description }}</b><small>{{ $e->reference }}</small></td>
              <td>{{ $e->category }}</td>
              <td>{{ $e->payment_account }}</td>
              <td>TSh {{ number_format($e->amount) }}</td>
              <td><span class="pill @if($e->approval_status !== 'Approved') warn @endif">{{ $e->approval_status }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">No expenses yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pager">{{ $expenses->links() }}</div>
  </section>

  <div class="modal-backdrop" id="addModal">
    <div class="modal-card">
      <form method="POST" action="{{ route('business.expenses.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Record expense</h2><p>Capture spending and approval details.</p></div>
          <button type="button" onclick="closeAdd()">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field full"><label>Description *</label><input name="description" required></div>
          <div class="field"><label>Category *</label>
            <select name="category">
              <option>Rent</option><option>Utilities</option><option>Transport</option>
              <option>Salaries</option><option>Marketing</option>
              <option>Shop operations</option><option>Other</option>
            </select></div>
          <div class="field"><label>Amount (TSh) *</label><input name="amount" type="number" min="0" required></div>
          <div class="field"><label>Payment account *</label>
            <select name="payment_account"><option>Cash register</option><option>M-Pesa</option><option>Bank</option></select></div>
          <div class="field"><label>Date *</label><input name="expense_date" type="date" value="{{ date('Y-m-d') }}" required></div>
          <div class="field full"><label>Reference</label><input name="reference"></div>
          <div class="field full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeAdd()">Cancel</button>
          <button class="primary">Save expense</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
    function openAdd(){document.getElementById('addModal').classList.add('open')}
    function closeAdd(){document.getElementById('addModal').classList.remove('open')}
  </script>
@endsection