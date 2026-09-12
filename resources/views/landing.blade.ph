<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pauli's POS Cloud — POS for computer shops</title>
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card" style="text-align:center">
    <div class="auth-brand" style="justify-content:center">
      <div class="auth-mark">PC</div>
      <h1>Pauli's POS Cloud</h1>
    </div>
    <h2>Landing page coming in Phase 2</h2>
    <p>For now, sign in to either panel:</p>
    <p style="margin-top:20px">
      <a class="btn" style="display:block;text-decoration:none;text-align:center"
         href="{{ route('business.login') }}">Business Login</a>
    </p>
    <p>
      <a class="btn" style="display:block;text-decoration:none;text-align:center;background:#111827"
         href="{{ route('superadmin.login') }}">Super Admin Login</a>
    </p>
  </div>
</div>
</body>
</html>