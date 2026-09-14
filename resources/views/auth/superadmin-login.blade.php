<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Super Admin · Pauli's POS Cloud</title>
<link rel="stylesheet" href="{{ asset('css/superadmin.css') }}">
</head>
<body>
<section class="login">
  <div class="login-art">
    <div class="login-brand">
      <div class="mark">SA</div>
      <div>
        <b>Pauli's POS Cloud</b>
        <small style="display:block;color:#c7c5dc;margin-top:3px">Multivendor control centre</small>
      </div>
    </div>
    <div>
      <h1>Every business account, under one secure view.</h1>
      <p>Manage vendors, users, subscriptions, platform revenue and account security from a dedicated Super Admin workspace.</p>
    </div>
    <div class="login-points">
      <div class="login-point"><b>Tenant separation</b><span>Every shop remains in its own business account.</span></div>
      <div class="login-point"><b>Controlled access</b><span>Roles and permissions stay business-specific.</span></div>
    </div>
  </div>

  <div class="login-form-wrap">
    <form class="login-card" method="POST" action="{{ route('superadmin.login.attempt') }}">
      @csrf
      <div class="mobile-brand">
        <div class="mark">SA</div>
        <b>Pauli's POS Cloud</b>
      </div>
      <h2>Super Admin login</h2>
      <p>This panel is separate from Business Owner, Repair Person and Sales Person accounts.</p>

      @if ($errors->any())
        <div class="login-error show">{{ $errors->first() }}</div>
      @endif

      <div class="field">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', 'admin@paulispos.co.tz') }}" autocomplete="username" required autofocus>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="pass">
          <input id="password" name="password" type="password" autocomplete="current-password" required>
          <button type="button" onclick="togglePassword()" aria-label="Show password">◉</button>
        </div>
      </div>

      <button class="login-submit" type="submit">Sign in securely →</button>

      <div class="demo">
        <b>Prototype credentials</b><br>
        admin@paulispos.co.tz<br>
        Admin@2026
      </div>
    </form>
  </div>
</section>

<script>
function togglePassword() {
  var p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
