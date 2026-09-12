<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Business Login · Pauli's POS</title>
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('business.login.attempt') }}">
    @csrf
    <div class="auth-brand">
      <div class="auth-mark">PC</div>
      <div>
        <h1>Pauli's Computer Shop</h1>
        <p>Sales and service management system</p>
      </div>
    </div>

    <h2>Business login</h2>
    <p>Sign in with your Business Owner, Repair Person or Sales Person account.</p>

    @if ($errors->any())
      <div class="err show">{{ $errors->first() }}</div>
    @endif

    <div class="field">
      <label for="login">Username or email</label>
      <input id="login" name="login" value="{{ old('login') }}" required autofocus>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required>
    </div>

    <button class="btn" type="submit">Sign in →</button>

    <div class="demo">
      <b>Business Owner:</b> owner / 1234<br>
      <b>Sales Person:</b> salesperson / 1234<br>
      <b>Repair Person:</b> repairperson / 1234
    </div>
  </form>
</div>
</body>
</html>