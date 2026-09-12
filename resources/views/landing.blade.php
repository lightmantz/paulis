<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pauli's POS Cloud — POS, repairs and stock for computer shops</title>
<meta name="description" content="Complete POS, repairs, purchases, stock taking and financial control for computer shops in Tanzania.">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

<header class="nav">
  <div class="nav-inner">
    <div class="brand">
      <div class="mark">PC</div>
      <div>
        <b>Pauli's POS Cloud</b>
        <small>Sales · Service · Stock</small>
      </div>
    </div>
    <nav class="nav-links">
      <a href="#features">Features</a>
      <a href="#pricing">Pricing</a>
      <a href="#why">Why Pauli's</a>
    </nav>
    <div class="nav-cta">
      @if ($alreadyLoggedIn ?? false)
        <a class="btn ghost" href="{{ route('business.dashboard') }}">Go to dashboard</a>
      @else
        <a class="btn ghost" href="{{ route('business.login') }}">Sign in</a>
      @endif
      <a class="btn primary" href="{{ route('business.register.show') }}">Create business account</a>
    </div>
  </div>
</header>

<section class="hero">
  <div class="hero-inner">
    <div class="hero-copy">
      <p class="eyebrow">BUILT FOR TANZANIAN COMPUTER SHOPS</p>
      <h1>Run your computer shop like a modern business.</h1>
      <p class="lede">
        Pauli's POS Cloud brings point of sale, inventory, repairs & service, purchases,
        stock taking and financial control into one clean workspace — from
        <b>Mwanza</b> to <b>Dar es Salaam</b>.
      </p>
      <div class="hero-actions">
        <a class="btn primary lg" href="{{ route('business.register.show') }}">Create business account →</a>
        <a class="btn ghost lg" href="{{ route('business.login') }}">Sign in to existing shop</a>
      </div>
      <div class="hero-badges">
        <span>✓ 14-day free trial</span>
        <span>✓ No card required</span>
        <span>✓ TZS pricing</span>
      </div>
    </div>
    <div class="hero-art">
      <div class="art-card">
        <div class="art-row"><b>Today's sales</b><span class="up">TSh 3.84M</span></div>
        <div class="art-row"><b>Gross profit</b><span class="up">TSh 1.23M</span></div>
        <div class="art-row"><b>Active repairs</b><span>TSh 12 jobs</span></div>
        <div class="art-row"><b>Cash position</b><span class="up">TSh 2.16M</span></div>
      </div>
      <div class="art-glow"></div>
    </div>
  </div>
</section>

<section class="features" id="features">
  <h2>Everything your shop needs</h2>
  <p class="sub">One login. One workflow. From the front desk to the accounts office.</p>
  <div class="feature-grid">
    @foreach ([
      ['▣', 'Point of Sale', 'Fast cart, barcode scanning, serial capture, receipts, invoices and quotations.'],
      ['▦', 'Inventory', 'Products with categories, subcategories, conditions, photos and automatic stock movements.'],
      ['⚒', 'Repair, Maintenance & Service', 'Job cards, diagnosis, approvals, parts, warranties and per-job profit.'],
      ['↓', 'Purchases', 'Registered suppliers, purchase orders, receiving, landed cost and supplier balances.'],
      ['▧', 'Stock Taking', 'Physical counts, variance reconciliation and full stock valuation.'],
      ['◈', 'Financial Information', 'Real-time profit & loss, receivables, payables, cash position and inventory value.'],
      ['♙', 'Customers & Suppliers', 'Profiles, credit limits, purchase history and balances.'],
      ['◷', 'Activity History', 'Full audit trail of who did what, when.'],
      ['⚙', 'Roles & Access', 'Business Owner, Repair Person and Sales Person with granular permissions.'],
    ] as $f)
      <article class="feature">
        <div class="f-icon">{{ $f[0] }}</div>
        <h3>{{ $f[1] }}</h3>
        <p>{{ $f[2] }}</p>
      </article>
    @endforeach
  </div>
</section>

<section class="why" id="why">
  <div class="why-inner">
    <div>
      <h2>Why shops choose Pauli's</h2>
      <ul class="why-list">
        <li><b>Multi-business ready.</b> Each shop is a completely separate account with its own users, data and settings.</li>
        <li><b>Owner approval flow.</b> Discounts, battery replacements and returns require Business Owner sign-off — audited end to end.</li>
        <li><b>Wired to real numbers.</b> Every dashboard figure comes from posted sales, purchases, expenses and stock movements. No guesses.</li>
        <li><b>Built for repair workflows.</b> Job cards, customer approvals, parts issued from inventory, warranty tracking.</li>
        <li><b>Tanzanian pricing in TZS.</b> No currency conversion headaches.</li>
      </ul>
    </div>
    <div class="why-art">
      <div class="why-card">
        <div class="mini-row"><span>Subscription</span><b>Monthly</b></div>
        <div class="mini-row"><span>Setup</span><b>Instant</b></div>
        <div class="mini-row"><span>Trial</span><b>14 days</b></div>
        <div class="mini-row"><span>Data</span><b>Yours</b></div>
      </div>
    </div>
  </div>
</section>

<section class="pricing" id="pricing">
  <h2>Simple, honest pricing</h2>
  <p class="sub">Start with a 14-day free trial. Upgrade any time from the Super Admin dashboard.</p>
  <div class="price-grid">
    <article class="price">
      <h3>Starter</h3>
      <div class="amount">TSh 95,000<small>/month</small></div>
      <ul>
        <li>1 business account</li>
        <li>Up to 3 users</li>
        <li>POS + Inventory</li>
        <li>Repairs & Service</li>
        <li>Email support</li>
      </ul>
      <a class="btn ghost" href="{{ route('business.register.show') }}">Start free trial</a>
    </article>
    <article class="price popular">
      <span class="pop-badge">MOST POPULAR</span>
      <h3>Growth</h3>
      <div class="amount">TSh 265,000<small>/month</small></div>
      <ul>
        <li>1 business account</li>
        <li>Up to 10 users</li>
        <li>Everything in Starter</li>
        <li>Purchases & Suppliers</li>
        <li>Stock Taking &amp; Reports</li>
        <li>Priority support</li>
      </ul>
      <a class="btn primary" href="{{ route('business.register.show') }}">Start free trial</a>
    </article>
    <article class="price">
      <h3>Professional</h3>
      <div class="amount">TSh 185,000<small>/month</small></div>
      <ul>
        <li>1 business account</li>
        <li>Up to 6 users</li>
        <li>Everything in Growth</li>
        <li>Full financial workspace</li>
        <li>Audit history</li>
        <li>Phone + email support</li>
      </ul>
      <a class="btn ghost" href="{{ route('business.register.show') }}">Start free trial</a>
    </article>
  </div>
</section>

<section class="cta">
  <div class="cta-inner">
    <h2>Ready to modernise your shop?</h2>
    <p>Create your business account in under two minutes. No credit card required.</p>
    <a class="btn primary lg" href="{{ route('business.register.show') }}">Create business account →</a>
  </div>
</section>

<footer class="foot">
  <div class="foot-inner">
    <div>
      <b>Pauli's POS Cloud</b>
      <p>Computer sales, accessories, repairs, maintenance & service.</p>
    </div>
    <div>
      <b>Sign in</b>
      <p><a href="{{ route('business.login') }}">Business login</a></p>
      <p><a href="{{ route('superadmin.login') }}">Super Admin login</a></p>
    </div>
    <div>
      <b>Office</b>
      <p>Mwanza, Tanzania</p>
      <p>Tanzanian Shilling (TSh)</p>
    </div>
    <div class="copy">
      © {{ date('Y') }} Pauli's Computer Shop. All rights reserved.
    </div>
  </div>
</footer>

</body>
</html>
