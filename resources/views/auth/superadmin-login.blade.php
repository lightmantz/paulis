<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Super Admin Login · Pauli's POS Cloud</title>
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('superadmin.login.attempt') }}">
    @csrf
    <div class="auth-brand">
      <div class="auth-mark">SA</div>
      <div><h1>Pauli's POS Cloud</h1><p>Super Admin control centre</p></div>
    </div>

    <h2>Super Admin login</h2>
    <p>This panel is separate from business accounts.</p>

    @if ($errors->any())
      <div class="err show">{{ $errors->first() }}</div>
    @endif

    <div class="field">
      <label for="email">Email address</label>
      <input id="email" name="email" type="email"
             value="{{ old('email', 'admin@paulispos.co.tz') }}" required autofocus>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
    </div>

    <button class="btn" type="submit">Sign in securely →</button>

    <div class="demo">
      <b>Prototype credentials</b><br>
      admin@paulispos.co.tz · Admin@2026
    </div>
  </form>
</div>
</body>
</html>
