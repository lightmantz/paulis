@extends('layouts.business')
@section('title', 'Repair, Maintenance & Service')
@section('page-title', 'Repair, Maintenance & Service')

@push('head')
<style>
.service-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:14px}
.service-stats article{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px}
.service-stats span{display:block;color:var(--muted);font-size:9px}
.service-stats b{display:block;font-size:16px;margin-top:5px}
.service-stats small{display:block;margin-top:5px;font-size:8px;color:#2b936e}
.service-kanban{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;overflow-x:auto;padding-bottom:8px}
.kanban-lane{background:#eef1f5;border-radius:9px;padding:9px;min-height:300px;min-width:180px}
.kanban-lane h3{font-size:10px;margin:0 0 9px;padding:0 4px;color:#596171;display:flex;justify-content:space-between;align-items:center}
.kanban-lane h3 span{background:#fff;border-radius:20px;padding:2px 7px;font-size:8px;color:#5c4bc9}
.kanban-card{width:100%;background:#fff;border:1px solid var(--line);border-radius:8px;padding:10px;margin-bottom:7px;text-align:left;cursor:pointer;transition:.15s}
.kanban-card:hover{border-color:var(--p);box-shadow:0 3px 10px #6755d91a}
.kanban-card b{display:block;color:var(--p);font-size:8px}
.kanban-card strong{display:block;font-size:10px;margin:5px 0 3px}
.kanban-card small{display:block;color:var(--muted);font-size:8px;line-height:1.4}
.kanban-card .badge-row{display:flex;justify-content:space-between;margin-top:7px;font-size:8px;color:var(--muted)}
.kanban-empty{color:#8a91a1;font-size:8px;text-align:center;padding:16px 0}
.alert-error{background:#feecec;color:#b13d3d;border:1px solid #f3c8c6;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.alert-success{background:#e8f7f0;color:#20795c;border:1px solid #c8ecdd;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px}
.modal-backdrop{position:fixed;inset:0;background:#11182788;display:none;align-items:center;justify-content:center;padding:20px;z-index:60;overflow:auto}
.modal-backdrop.open{display:flex}
.modal-card{background:#fff;border-radius:15px;width:min(900px,100%);max-height:92dvh;overflow:auto;box-shadow:0 30px 80px #0005}
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
.job-tabs{display:flex;gap:6px;border-bottom:1px solid var(--line);padding:0 20px;background:#fbfcfe}
.job-tabs button{border:0;background:none;color:#596171;padding:12px 14px;font-size:11px;font-weight:700;cursor:pointer;border-bottom:2px solid transparent}
.job-tabs button.active{color:var(--p);border-bottom-color:var(--p)}
.tab-pane{display:none}
.tab-pane.active{display:block}
.job-line{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:11px;align-items:center}
.job-line.header{color:var(--muted);font-size:9px;text-transform:uppercase;letter-spacing:.4px;font-weight:800}
.job-line b{font-weight:700}
.job-line .right{text-align:right}
.history-item{border-left:2px solid var(--p);padding:2px 0 12px 12px;margin-left:4px;font-size:11px}
.history-item b{display:block;font-size:10px}
.history-item small{display:block;color:var(--muted);font-size:9px;margin-top:3px}
.financial-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-bottom:14px}
.financial-grid article{background:#f8f9fc;border-radius:8px;padding:11px}
.financial-grid span{display:block;color:var(--muted);font-size:8px}
.financial-grid b{display:block;font-size:13px;margin-top:5px;color:#2a225e}
.job-status-form{display:flex;gap:8px;margin-top:12px}
.job-status-form select{flex:1;border:1px solid var(--line);border-radius:8px;padding:9px 11px;font-size:11px;background:#fff}
.job-status-form input{flex:2;border:1px solid var(--line);border-radius:8px;padding:9px 11px;font-size:11px;background:#fff}
@media(max-width:900px){.service-stats{grid-template-columns:repeat(2,1fr)}.financial-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:620px){.service-stats{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.job-line{grid-template-columns:1fr 1fr}.job-line .right{text-align:left}}
</style>
@endpush

@section('content')
  @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
  @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif

  <div class="head">
    <div>
      <p class="eyebrow">SERVICE CENTRE</p>
      <h1>Repair, Maintenance &amp; Service</h1>
      <p>Job cards, diagnosis, approvals, parts, warranties and per-job profit.</p>
    </div>
    <button class="primary" onclick="openNewJob()">＋ New job card</button>
  </div>

  <div class="service-stats">
    <article><span>Service revenue</span><b>TSh {{ number_format($stats['revenue']) }}</b><small>From all jobs</small></article>
    <article><span>Parts + labour + costs</span><b>TSh {{ number_format($stats['expenses']) }}</b><small>Job expenses</small></article>
    <article><span>Service net profit</span><b>TSh {{ number_format($stats['profit']) }}</b><small>Revenue − expenses</small></article>
    <article><span>Service margin</span><b>{{ number_format($stats['margin'], 1) }}%</b><small>Profit ÷ revenue</small></article>
  </div>

  <div class="service-kanban">
    @php
      $lanes = [
        'received'          => 'Received',
        'diagnosing'        => 'Diagnosing',
        'awaiting_approval' => 'Awaiting approval',
        'in_repair'         => 'In repair',
        'ready'             => 'Ready for collection',
        'collected'         => 'Collected',
      ];
    @endphp
    @foreach ($lanes as $key => $label)
      @php $laneJobs = $jobs->where('status', $key); @endphp
      <div class="kanban-lane">
        <h3>{{ $label }} <span>{{ $laneJobs->count() }}</span></h3>
        @forelse ($laneJobs as $j)
          <button class="kanban-card" onclick="openJob({{ $j->id }})">
            <b>{{ $j->job_no }}</b>
            <strong>{{ $j->device }}</strong>
            <small>{{ $j->customer?->name ?? '—' }}<br>{{ \Illuminate\Support\Str::limit($j->issue, 50) }}</small>
            <div class="badge-row">
              <span>{{ $j->assignee?->name ?? 'Unassigned' }}</span>
              <span>TSh {{ number_format($j->revenue) }}</span>
            </div>
          </button>
        @empty
          <div class="kanban-empty">No jobs</div>
        @endforelse
      </div>
    @endforeach
  </div>

  {{-- New job modal --}}
  <div class="modal-backdrop" id="newJobModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('business.service.store') }}">
        @csrf
        <header class="modal-head">
          <div><h2>New job card</h2><p>Check in the device and record the customer complaint.</p></div>
          <button type="button" onclick="closeModal('newJobModal')">×</button>
        </header>
        <div class="modal-body"><div class="form-grid">
          <div class="field"><label>Customer *</label>
            <select name="customer_id" required>
              <option value="">Select customer…</option>
              @foreach ($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="field"><label>Priority</label>
            <select name="priority">
              <option>Normal</option><option>Urgent</option><option>Warranty return</option>
            </select>
          </div>
          <div class="field"><label>Device *</label><input name="device" placeholder="e.g. HP Pavilion 15" required></div>
          <div class="field"><label>Serial / service tag</label><input name="serial"></div>
          <div class="field full"><label>Reported issue *</label><textarea name="issue" rows="3" required></textarea></div>
          <div class="field"><label>Assigned to</label>
            <select name="assigned_to">
              <option value="">Unassigned</option>
              @foreach ($staff as $s)
                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->role }})</option>
              @endforeach
            </select>
          </div>
          <div class="field"><label>Quoted amount (TSh)</label><input name="quoted_amount" type="number" min="0" value="0"></div>
          <div class="field"><label>Deposit received (TSh)</label><input name="deposit" type="number" min="0" value="0"></div>
        </div></div>
        <footer class="modal-actions">
          <button type="button" class="secondary" onclick="closeModal('newJobModal')">Cancel</button>
          <button type="submit" class="primary">Create job card</button>
        </footer>
      </form>
    </div>
  </div>

  {{-- Job detail modal --}}
  <div class="modal-backdrop" id="jobModal">
    <div class="modal-card" onclick="event.stopPropagation()">
      <header class="modal-head">
        <div>
          <h2 id="jm-title">Job</h2>
          <p id="jm-subtitle"></p>
        </div>
        <button type="button" onclick="closeModal('jobModal')">×</button>
      </header>

      <nav class="job-tabs" id="jm-tabs">
        <button class="active" onclick="showTab('overview', this)">Overview</button>
        <button onclick="showTab('parts', this)">Parts</button>
        <button onclick="showTab('financials', this)">Financials</button>
        <button onclick="showTab('history', this)">History</button>
      </nav>

      <div class="modal-body">
        <div class="tab-pane active" id="tab-overview">
          <div class="form-grid">
            <div class="field"><label>Diagnosis</label><textarea id="jm-diagnosis" rows="3"></textarea></div>
            <div class="field"><label>Warranty</label><input id="jm-warranty" placeholder="e.g. 3 months"></div>
            <div class="field"><label>Revenue charged (TSh)</label><input id="jm-revenue" type="number" min="0" step="1"></div>
            <div class="field"><label>Labour cost (TSh)</label><input id="jm-labour" type="number" min="0" step="1"></div>
          </div>
          <div class="job-status-form">
            <select id="jm-status">
              @foreach ($lanes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
            <input id="jm-status-notes" placeholder="Optional note about this change">
            <button class="primary" onclick="saveJob()">Save changes</button>
          </div>
        </div>

        <div class="tab-pane" id="tab-parts">
          <div class="job-line header"><div>Part</div><div class="right">Qty</div><div class="right">Unit cost</div><div class="right">Line total</div></div>
          <div id="jm-parts-list"></div>

          <div class="form-grid" style="margin-top:14px">
            <div class="field"><label>Add part from inventory</label>
              <select id="jm-part-product">
                <option value="">Select product…</option>
                @foreach ($products as $p)
                  <option value="{{ $p->id }}" data-cost="{{ $p->unit_cost }}" data-stock="{{ $p->stock }}">
                    {{ $p->name }} · TSh {{ number_format($p->unit_cost) }} · {{ $p->stock }} in stock
                  </option>
                @endforeach
              </select>
            </div>
            <div class="field"><label>Quantity</label><input id="jm-part-qty" type="number" min="1" value="1"></div>
          </div>
          <button class="primary" style="margin-top:10px" onclick="addPart()">Issue part to job</button>
        </div>

        <div class="tab-pane" id="tab-financials">
          <div class="financial-grid">
            <article><span>Revenue</span><b id="jf-revenue">—</b></article>
            <article><span>Parts cost</span><b id="jf-parts">—</b></article>
            <article><span>Labour + other</span><b id="jf-labour">—</b></article>
            <article><span>Net profit</span><b id="jf-profit">—</b></article>
          </div>
          <p style="font-size:11px;color:var(--muted);margin:0">
            Revenue − parts − labour − other costs. Parts are valued at unit cost, not selling price.
          </p>
        </div>

        <div class="tab-pane" id="tab-history">
          <div id="jm-history"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
  const CSRF = '{{ csrf_token() }}';
  let activeJob = null;

  function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
  function openNewJob() { openModal('newJobModal'); }

  async function openJob(id) {
    const res = await fetch(`/app/service/${id}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) { alert('Could not load job'); return; }
    const data = await res.json();
    activeJob = data.job;

    document.getElementById('jm-title').textContent = data.job.job_no + ' · ' + data.job.device;
    document.getElementById('jm-subtitle').textContent =
      (data.job.customer?.name || '—') + ' · ' + (data.job.serial || 'No serial');

    document.getElementById('jm-diagnosis').value = data.job.diagnosis || '';
    document.getElementById('jm-warranty').value  = data.job.warranty || '';
    document.getElementById('jm-revenue').value   = Math.round(data.job.revenue || 0);
    document.getElementById('jm-labour').value    = Math.round(data.job.labour_cost || 0);
    document.getElementById('jm-status').value    = data.job.status;
    document.getElementById('jm-status-notes').value = '';

    // Parts
    document.getElementById('jm-parts-list').innerHTML = data.parts.length
      ? data.parts.map(p => `<div class="job-line">
          <div><b>${escapeHtml(p.name)}</b></div>
          <div class="right">${p.qty}</div>
          <div class="right">TSh ${Math.round(p.unit_cost).toLocaleString()}</div>
          <div class="right">TSh ${Math.round(p.line_total).toLocaleString()}</div>
        </div>`).join('')
      : '<div class="kanban-empty">No parts issued yet</div>';

    // Financials
    document.getElementById('jf-revenue').textContent = 'TSh ' + Math.round(data.financials.revenue).toLocaleString();
    document.getElementById('jf-parts').textContent   = 'TSh ' + Math.round(data.financials.parts).toLocaleString();
    document.getElementById('jf-labour').textContent  = 'TSh ' + Math.round(data.financials.labour).toLocaleString();
    document.getElementById('jf-profit').textContent  = 'TSh ' + Math.round(data.financials.profit).toLocaleString();

    // History
    document.getElementById('jm-history').innerHTML = data.history.length
      ? data.history.map(h => `<div class="history-item">
          <b>${(h.from || 'created')} → ${h.to}</b>
          <small>${h.user} · ${h.time}</small>
          ${h.notes ? `<small>${escapeHtml(h.notes)}</small>` : ''}
        </div>`).join('')
      : '<div class="kanban-empty">No history yet</div>';

    openModal('jobModal');
    showTab('overview', document.querySelector('#jm-tabs button'));
  }

  function showTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('#jm-tabs button').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
  }

  async function saveJob() {
    if (!activeJob) return;
    const body = {
      diagnosis:   document.getElementById('jm-diagnosis').value,
      warranty:    document.getElementById('jm-warranty').value,
      revenue:     document.getElementById('jm-revenue').value,
      labour_cost: document.getElementById('jm-labour').value,
    };
    await fetch(`/app/service/${activeJob.id}/invoice`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(body),
    });
    const statusBody = {
      status: document.getElementById('jm-status').value,
      notes:  document.getElementById('jm-status-notes').value,
    };
    await fetch(`/app/service/${activeJob.id}/status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify(statusBody),
    });
    closeModal('jobModal');
    location.reload();
  }

  async function addPart() {
    if (!activeJob) return;
    const productId = document.getElementById('jm-part-product').value;
    const qty = Number(document.getElementById('jm-part-qty').value || 1);
    if (!productId) return alert('Select a product first');

    const res = await fetch(`/app/service/${activeJob.id}/parts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ product_id: productId, qty }),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      alert(err.error || 'Could not add part');
      return;
    }
    await openJob(activeJob.id);
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  </script>
@endsection
