<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Signing Out…</title>
  <style>
    body { background: #0D1B2A; color: #94A3B8; font-family: sans-serif;
           display:flex; align-items:center; justify-content:center; min-height:100vh; }
  </style>
</head>
<body>
  <p>Signing out…</p>
  <script>
    const API = '{{ env("API_BASE_URL", "http://localhost:8001/api") }}';
    const token = localStorage.getItem('lms_token');

    (async () => {
      if (token) {
        try {
          await fetch(`${API}/auth/logout`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
          });
        } catch (_) {}
      }
      localStorage.removeItem('lms_token');
      localStorage.removeItem('lms_user');
      window.location.href = '/login';
    })();
  </script>
</body>
</html>
