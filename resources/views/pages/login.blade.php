<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
  <title>Gracimor LMS — Sign In</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>

  <style>
    :root {
      --navy:      #0D1B2A;
      --navy-mid:  #112236;
      --navy-card: #16293D;
      --navy-line: #1E3450;
      --teal:      #0B8FAC;
      --teal-lt:   #13AECF;
      --amber:     #F5A623;
      --green:     #22C55E;
      --red:       #EF4444;
      --slate:     #94A3B8;
      --white:     #F0F6FF;
      --text:      #E2EAF4;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      background: var(--navy-mid);
      border: 1px solid var(--navy-line);
      border-radius: 16px;
      width: calc(100% - 32px);
      padding: 48px 40px;
      width: 100%;
      max-width: 420px;
    }

    .brand {
      text-align: center;
      margin-bottom: 36px;
    }

    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      color: var(--white);
      letter-spacing: 0.02em;
    }

    .brand-sub {
      font-size: 11px;
      font-weight: 500;
      color: var(--teal);
      letter-spacing: 0.15em;
      text-transform: uppercase;
      margin-top: 4px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--slate);
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      background: var(--navy-card);
      border: 1px solid var(--navy-line);
      border-radius: 8px;
      color: var(--text);
      font-family: inherit;
      font-size: 14px;
      padding: 12px 16px;
      outline: none;
      transition: border-color .2s;
    }

    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: var(--teal);
    }

    input::placeholder { color: var(--slate); opacity: .6; }

    .btn-signin {
      width: 100%;
      padding: 13px;
      background: var(--teal);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-family: inherit;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, opacity .2s;
      margin-top: 8px;
    }

    .btn-signin:hover { background: var(--teal-lt); }
    .btn-signin:disabled { opacity: .6; cursor: not-allowed; }

    .error-box {
      background: rgba(239,68,68,.1);
      border: 1px solid rgba(239,68,68,.3);
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 13px;
      color: #fca5a5;
      margin-bottom: 20px;
    }

    .footer-note {
      text-align: center;
      font-size: 12px;
      color: var(--slate);
      margin-top: 28px;
    }

    @media (max-width: 480px) {
      .login-card { padding: 32px 20px; border-radius: 12px; }
      .brand-name { font-size: 24px; }
      body { align-items: flex-start; padding-top: 40px; }
    }
  </style>
</head>
<body>

  <div class="login-card" x-data="loginApp()">
    <div class="brand">
      <div class="brand-name">Gracimor</div>
      <div class="brand-sub">Loans Management System</div>
    </div>

    <div class="error-box" x-show="errorMsg" x-text="errorMsg" style="display:none"></div>

    <form @submit.prevent="signIn">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          id="email"
          type="email"
          x-model="email"
          placeholder="you@gracimor.co.zm"
          autocomplete="email"
          required
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          type="password"
          x-model="password"
          placeholder="••••••••"
          autocomplete="current-password"
          required
        />
      </div>

      <button type="submit" class="btn-signin" :disabled="loading">
        <span x-show="!loading">Sign In</span>
        <span x-show="loading" style="display:none">Signing in…</span>
      </button>
    </form>

    <div class="footer-note">Gracimor Microfinance Ltd &mdash; Internal Portal</div>
  </div>

  <script>
    const API = '{{ env("API_BASE_URL", "http://localhost:8001/api") }}';

    function loginApp() {
      return {
        email:    '',
        password: '',
        loading:  false,
        errorMsg: '',

        async init() {
          // Verify existing token is still valid before redirecting
          const token = localStorage.getItem('lms_token');
          if (!token) return;
          try {
            const r = await fetch(`${API}/auth/me`, {
              headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (r.ok) {
              window.location.href = '/dashboard';
            } else {
              localStorage.removeItem('lms_token');
              localStorage.removeItem('lms_user');
            }
          } catch (_) {}
        },

        async signIn() {
          this.loading  = true;
          this.errorMsg = '';

          try {
            const res = await fetch(`${API}/auth/login`, {
              method:  'POST',
              headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
              body:    JSON.stringify({ email: this.email, password: this.password }),
            });

            const data = await res.json();

            if (!res.ok) {
              this.errorMsg = data.message || 'Invalid credentials. Please try again.';
              return;
            }

            // Persist token and basic user info
            localStorage.setItem('lms_token', data.token);
            localStorage.setItem('lms_user',  JSON.stringify(data.user));

            window.location.href = '/dashboard';
          } catch (err) {
            this.errorMsg = 'Cannot reach the server. Check your connection.';
          } finally {
            this.loading = false;
          }
        },
      };
    }
  </script>
</body>
</html>
