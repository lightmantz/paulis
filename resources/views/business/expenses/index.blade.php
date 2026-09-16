@extends('layouts.business')
@section('title', 'Expenses')
@section('page-title', 'Expenses')

@push('head')
<style>
.expense-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.expense-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.expense-stats span{display:block;color:var(--muted);font-size:9px}
.expense-stats b{display:block;font-size:16px;margin-top:5px}
.expense-stats small{display:block;margin-top:5px;font-size:8px;color:#2b936e}

.row-action-icon{border:1px solid var(--line);background:#fff;border-radius:6px;padding:6px 8px;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap}
.row-action-icon.view{border-color:#d7d1ff;background:#f5f2ff;color:#5947ca}
.row-action-icon.approve{border-color:#c8ecdd;background:#eaf8f2;color:#20795c}
.row-action-icon.edit{border-color:#c8e4ff;background:#eff7ff;color:#2458a3}
.row-action-icon.del{border-color:#f5c9c5;background:#feecec;color:#b13d3d}
.row-actions-cell{white-space:nowrap;display:flex;gap:5px;flex-wrap:wrap}

.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(680px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
.modal-head{display:flex;justify-content:space-between;align-items:flex-start;padding:18px 20px;border-bottom:1px solid var(--line);position:sticky;top:0;background:#fff;z-index:2}
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

.view-list{display:grid;gap:9px}
.view-list div{display:grid;grid-template-columns:140px 1fr;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:11px}
.view-list div:last-child{border:0}
.view-list span{color:var(--muted);font-size:10px;text-transform:uppercase;font-weight:800}
.view-list b{font-weight:700;overflow-wrap:anywhere}

.alert-success{background:#e8f7f0;color:#20795c;border:1px solid #c8ecdd;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}

@media(max-width:650px){.expense-stats{grid-template-columns:1fr 1fr}.form-grid{grid-template-columns:1fr}.modal-backdrop{padding:0;align-items:flex-end}.modal-card{border-radius:15px 15px 0 0;max-height:95dvh}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if (session('error'))<div class="alert-error">{{ session('error') }}</div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">EXPENSES</p>
      <h1>Expenses</h1>
      <p>Operational spending with receipts and approvals.</p>
    </div>
    <button class="primary" onclick="openModal('addModal')">＋ Record expense</button>
  </div>

  <section class="panel">
    <form class="filters" method="GET">
      <input name="q" value="{{ request('q') }}" placeholder="Search description, category or reference…">
      <select name="status" onchange="this.form.submit()">
        @foreach (['All','Approved','Pending Owner approval','Rejected'] as $s)
          <option @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
      </select>
    </form>

    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th>Date</th><th>Description</th><th>Category</th>
            <th>Account</th><th>Amount</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($expenses as $e)
            <tr>
              <td>{{ $e->expense_date?->format('d M Y') }}</td>
              <td><b>{{ $e->description }}</b><small>{{ $e->reference }}</small></td>
              <td>{{ $e->category }}</td>
              <td>{{ $e->payment_account }}</td>
              <td>TSh {{ number_format($e->amount) }}</td>
              <td>
                @php
                  $cls = $e->approval_status === 'Approved' ? '' : ($e->approval_status === 'Rejected' ? 'bad' : 'warn');
                @endphp
                <span class="pill {{ $cls }}">{{ $e->approval_status }}</span>
              </td>
              <td class="row-actions-cell">
                <button class="row-action-icon view" onclick="viewExpense({{ $e->id }})">View</button>
                @if ($e->approval_status !== 'Approved' && auth()->user()->role === 'owner')
                  <form method="POST" action="{{ route('business.expenses.approve', $e) }}" style="display:inline">
                    @csrf
                    <button class="row-action-icon approve" type="submit">Approve</button>
                  </form>
                @endif
                <button class="row-action-icon edit" onclick='editExpense(@json($e->toArray()))'>Edit</button>
                <form method="POST" action="{{ route('business.expenses.destroy', $e) }}" style="display:inline"
                      onsubmit="return confirm('Delete this expense? This cannot be undone.')">
                  @csrf
                  @method('DELETE')
                  <button class="row-action-icon del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty">No expenses yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pager">{{ $expenses->links() }}</div>
  </section>

  {{-- Add modal --}}
  <div class="modal-backdrop" id="addModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.expenses.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>Record expense</h2><p>Capture the spending and its payment details.</p></div>
          <button type="button" onclick="closeModal('addModal')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field full"><label>Description *</label><input name="description" required></div>
            <div class="field"><label>Category *</label>
              <select name="category">
                <option>Rent</option><option>Utilities</option><option>Transport</option>
                <option>Salaries</option><option>Marketing</option>
                <option>Shop operations</option><option>Other</option>
              </select>
            </div>
            <div class="field"><label>Amount (TSh) *</label><input name="amount" type="number" min="0" required></div>
            <div class="field"><label>Payment account *</label>
              <select name="payment_account"><option>Cash register</option><option>M-Pesa</option><option>Bank</option></select>
            </div>
            <div class="field"><label>Date *</label><input name="expense_date" type="date" value="{{ date('Y-m-d') }}" required></div>
            <div class="field full"><label>Reference</label><input name="reference"></div>
            <div class="field full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('addModal')">Cancel</button>
          <button type="submit" class="primary">Save expense</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- View modal --}}
  <div class="modal-backdrop" id="viewModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div><h2 id="vTitle">Expense</h2><p id="vSubtitle"></p></div>
        <button type="button" onclick="closeModal('viewModal')">×</button>
      </header>
      <div class="modal-body">
        <div class="view-list" id="vBody"></div>
      </div>
      <footer class="modal-actions">
        <button type="button" class="secondary" onclick="closeModal('viewModal')">Close</button>
      </footer>
    </div>
  </div>

  {{-- Edit modal --}}
  <div class="modal-backdrop" id="editModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" id="editForm">
        @csrf
        @method('PUT')
        <header class="modal-head">
          <div><h2>Edit expense</h2><p id="editSubtitle"></p></div>
          <button type="button" onclick="closeModal('editModal')">×</button>
        </header>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field full"><label>Description *</label><input name="description" id="e_description" required></div>
            <div class="field"><label>Category *</label>
              <select name="category" id="e_category">
                <option>Rent</option><option>Utilities</option><option>Transport</option>
                <option>Salaries</option><option>Marketing</option>
                <option>Shop operations</option><option>Other</option>
              </select>
            </div>
            <div class="field"><label>Amount (TSh) *</label><input name="amount" id="e_amount" type="number" min="0" required></div>
            <div class="field"><label>Payment account *</label>
              <select name="payment_account" id="e_account">
                <option>Cash register</option><option>M-Pesa</option><option>Bank</option>
              </select>
            </div>
            <div class="field"><label>Date *</label><input name="expense_date" id="e_date" type="date" required></div>
            <div class="field full"><label>Reference</label><input name="reference" id="e_reference"></div>
            <div class="field full"><label>Notes</label><textarea name="notes" id="e_notes" rows="3"></textarea></div>
            @if (auth()->user()->role === 'owner')
              <div class="field full"><label>Approval status</label>
                <select name="approval_status" id="e_approval">
                  <option>Pending Owner approval</option>
                  <option>Approved</option>
                  <option>Rejected</option>
                </select>
              </div>
            @endif
          </div>
        </div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" class="primary">Save changes</button>
        </footer>
      </form>
    </div>
  </div>

  <script>
  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

  async function viewExpense(id) {
    const res = await fetch('/app/expenses/' + id, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Could not load expense');
    const x = await res.json();

    document.getElementById('vTitle').textContent = x.description;
    document.getElementById('vSubtitle').textContent = x.category + ' · ' + x.expense_date;

    document.getElementById('vBody').innerHTML = `
      <div><span>Amount</span><b>TSh ${Math.round(x.amount).toLocaleString()}</b></div>
      <div><span>Category</span><b>${x.category}</b></div>
      <div><span>Paid via</span><b>${x.payment_account}</b></div>
      <div><span>Date</span><b>${x.expense_date}</b></div>
      <div><span>Reference</span><b>${x.reference || '—'}</b></div>
      <div><span>Status</span><b>${x.approval_status}</b></div>
      <div><span>Notes</span><b>${x.notes || '—'}</b></div>
    `;
    openModal('viewModal');
  }

  function editExpense(x) {
    document.getElementById('editSubtitle').textContent = x.description;
    document.getElementById('editForm').action = '/app/expenses/' + x.id;

    document.getElementById('e_description').value = x.description;
    document.getElementById('e_category').value    = x.category;
    document.getElementById('e_amount').value      = Math.round(x.amount);
    document.getElementById('e_account').value     = x.payment_account;
    document.getElementById('e_date').value        = (x.expense_date || '').slice(0, 10);
    document.getElementById('e_reference').value   = x.reference || '';
    document.getElementById('e_notes').value       = x.notes || '';

    var approval = document.getElementById('e_approval');
    if (approval) approval.value = x.approval_status;

    openModal('editModal');
  }
  </script>
@endsection
