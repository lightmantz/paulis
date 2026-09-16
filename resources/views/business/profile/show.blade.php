@extends('layouts.business')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('head')
<style>
  .profile-grid{display:grid;grid-template-columns:300px 1fr;gap:16px}
  .profile-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px;text-align:center}
  .profile-avatar-big{width:96px;height:96px;border-radius:50%;margin:0 auto 12px;background:linear-gradient(135deg,#7767ef,#a057d6);color:#fff;display:grid;place-items:center;font-weight:800;font-size:28px;overflow:hidden}
  .profile-avatar-big img{width:100%;height:100%;object-fit:cover;display:block}
  .profile-card h2{margin:0 0 4px;font-size:16px}
  .profile-card p{margin:0;color:var(--muted);font-size:11px}
  .profile-card .meta{margin-top:16px;text-align:left;border-top:1px solid var(--line);padding-top:12px}
  .profile-card .meta div{display:flex;justify-content:space-between;padding:6px 0;font-size:11px}
  .profile-card .meta span{color:var(--muted)}

  .form-panel{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px}
  .form-panel h2{margin:0 0 4px;font-size:14px}
  .form-panel>p{margin:0 0 18px;color:var(--muted);font-size:10px}
  .profile-fields{display:grid;grid-template-columns:1fr 1fr;gap:13px}
  .profile-fields .full{grid-column:1/-1}
  .profile-fields .field{display:flex;flex-direction:column;gap:5px}
  .profile-fields label{font-size:10px;font-weight:800;color:#344054}
  .profile-fields input{border:1px solid #dfe3ea;border-radius:8px;padding:10px 11px;background:#fff;outline:none;font-size:12px}
  .profile-fields input:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(103,85,217,.09)}
  .form-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px;padding-top:14px;border-top:1px solid var(--line)}
  .photo-preview{width:80px;height:80px;border-radius:50%;background:#f2f4f8 center/cover no-repeat;border:1px solid var(--line);display:inline-block}

  @media(max-width:820px){.profile-grid{grid-template-columns:1fr}.profile-fields{grid-template-columns:1fr}.profile-fields .full{grid-column:auto}}
</style>
@endpush

@section('content')
  @if (session('success'))
    <div class="created-banner"><b>✓ {{ session('success') }}</b></div>
  @endif
  @if ($errors->any())
    <div class="created-banner" style="background:#feecec;border-color:#f3c8c6;color:#b13d3d">
      <b>✕ {{ $errors->first() }}</b>
    </div>
  @endif

  <div class="head">
    <div>
      <p class="eyebrow">ACCOUNT</p>
      <h1>My Profile</h1>
      <p>Update your personal information, avatar and password.</p>
    </div>
  </div>

  <div class="profile-grid">
    {{-- Left: profile card --}}
    <aside class="profile-card">
      <div class="profile-avatar-big">
        @if ($user->avatar)
          <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
        @else
          {{ strtoupper(collect(explode(' ', $user->name))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('')) }}
        @endif
      </div>
      <h2>{{ $user->name }}</h2>
      <p>{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>

      <div class="meta">
        <div><span>Email</span><b>{{ $user->email }}</b></div>
        <div><span>Phone</span><b>{{ $user->phone ?? '—' }}</b></div>
        <div><span>Business</span><b>{{ $user->business->name }}</b></div>
        <div><span>Joined</span><b>{{ $user->created_at->format('d M Y') }}</b></div>
      </div>
    </aside>

    {{-- Right: edit form --}}
    <section>
      <form class="form-panel" method="POST" action="{{ route('business.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2>Personal information</h2>
        <p>Your details appear on receipts and documents you generate.</p>

        <div class="profile-fields">
          <div class="field"><label>Full name *</label><input name="name" value="{{ old('name', $user->name) }}" required></div>
          <div class="field"><label>Phone</label><input name="phone" value="{{ old('phone', $user->phone) }}"></div>
          <div class="field full"><label>Email *</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
          <div class="field full">
            <label>Profile picture</label>
            <div style="display:flex;align-items:center;gap:14px">
              <span class="photo-preview" id="avatarPreview"
                    style="{{ $user->avatar ? "background-image:url('".asset('storage/'.$user->avatar)."')" : '' }}"></span>
              <input type="file" name="avatar" accept="image/*"
                     onchange="previewAvatar(this)" style="flex:1">
            </div>
            <small style="color:var(--muted);font-size:9px;margin-top:6px">JPG/PNG/WebP · max 2 MB</small>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="primary">Save changes</button>
        </div>
      </form>

      {{-- Password --}}
      <form class="form-panel" method="POST" action="{{ route('business.profile.password') }}" style="margin-top:16px">
        @csrf
        <h2>Change password</h2>
        <p>Use at least 6 characters. You'll stay signed in on this device.</p>

        <div class="profile-fields">
          <div class="field full"><label>Current password *</label><input type="password" name="current_password" required></div>
          <div class="field"><label>New password *</label><input type="password" name="password" minlength="6" required></div>
          <div class="field"><label>Confirm password *</label><input type="password" name="password_confirmation" minlength="6" required></div>
        </div>

        <div class="form-actions">
          <button type="submit" class="primary">Update password</button>
        </div>
      </form>
    </section>
  </div>

  <script>
    function previewAvatar(input) {
      var preview = document.getElementById('avatarPreview');
      var file = input.files && input.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.style.backgroundImage = 'url(' + JSON.stringify(e.target.result) + ')';
      };
      reader.readAsDataURL(file);
    }
  </script>
@endsection
