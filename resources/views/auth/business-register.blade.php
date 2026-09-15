<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create business account · Pauli's POS Cloud</title>
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
<style>
  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .field select{width:100%;border:1px solid #dfe3ea;border-radius:9px;padding:11px 12px;background:#fff;font-size:13px;outline:none}
  .field select:focus{border-color:#6755d9;box-shadow:0 0 0 3px rgba(103,85,217,.09)}
  .trial-note{background:#f6f5ff;border:1px solid #ded9ff;border-radius:8px;padding:10px 12px;color:#5a4ac2;font-size:11px;line-height:1.55;margin-bottom:14px}
  .trial-note b{color:#4534b8}
  @media(max-width:520px){.two-col{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('business.register') }}" style="width:min(560px,100%)">
    @csrf

    <div class="auth-brand">
      <div class="auth-mark">PC</div>
      <div>
        <h1>Pauli's POS Cloud</h1>
        <p>Start your 14-day free trial</p>
      </div>
    </div>

    <h2>Create your business account</h2>
    <p>You'll be the Business Owner. You can add more users after signing in.</p>

    @if ($errors->any())
      <div class="err show">
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <div class="trial-note">
      <b>14-day free trial</b> · No credit card required. Your account starts on the plan you choose and you'll be billed after the trial ends.
    </div>

    <div class="two-col">
      <div class="field">
        <label for="business_name">Business name *</label>
        <input id="business_name" name="business_name" value="{{ old('business_name') }}"
               placeholder="e.g. Kibo Technology Centre" required autofocus>
      </div>
      <div class="field">
        <label for="city">City / region *</label>
        <input id="city" name="city" value="{{ old('city', 'Mwanza') }}" required>
      </div>
    </div>

    <div class="two-col">
      <div class="field">
        <label for="owner_name">Your full name *</label>
        <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
      </div>
      <div class="field">
        <label for="phone">Phone number *</label>
        <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+255 7XX XXX XXX" required>
      </div>
    </div>

    <div class="field">
      <label for="email">Email address *</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}" required>
    </div>

    <div class="field">
      <label for="plan">Choose your plan *</label>
      <select id="plan" name="plan" required>
        <option value="Starter"      @selected(old('plan') === 'Starter')>Starter — TSh 95,000 / month</option>
        <option value="Growth"       @selected(old('plan') === 'Growth')>Growth — TSh 265,000 / month</option>
        <option value="Professional" @selected(old('plan') === 'Professional')>Professional — TSh 185,000 / month</option>
      </select>
    </div>

    <div class="two-col">
      <div class="field">
        <label for="password">Password *</label>
        <input id="password" name="password" type="password" minlength="8" required>
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm password *</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
      </div>
    </div>

    <button class="btn" type="submit">Create account and start trial →</button>

    <div class="demo">
      Already have an account? <a href="{{ route('business.login') }}" style="color:#5a4ac2;font-weight:800">Sign in</a>
      &nbsp;·&nbsp; <a href="{{ route('landing') }}" style="color:#5a4ac2">Back to home</a>
    </div>
  </form>
</div>
</body>
</html>
