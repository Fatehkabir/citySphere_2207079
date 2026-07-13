<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
?>
<!doctype html><html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CitySphere</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body style="background:#f8fafc">
<div style="max-width:700px;margin:0 auto;padding:60px 20px;text-align:center">
  <div style="font-size:3rem;margin-bottom:12px">🏙</div>
  <h1 style="font-size:2rem;font-weight:700;margin:0 0 12px;color:#1e293b">CitySphere</h1>
  <p style="color:#64748b;font-size:1rem;margin:0 0 32px;line-height:1.7">
    Citizens file reports, property owners manage rentals, and police review cases —<br>
    all connected through a secure role-based platform.
  </p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:48px">
    <a class="btn-primary" href="login.php" style="padding:10px 24px;border-radius:4px;font-weight:600;text-decoration:none;display:inline-block">Sign in</a>
    <a class="btn" href="signup.php" style="padding:10px 24px;border-radius:4px;font-weight:600;text-decoration:none;display:inline-block">Create account</a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;text-align:left">
    <div class="card">
      <div style="font-size:1.8rem;margin-bottom:8px">📋</div>
      <h3 style="margin:0 0 6px;font-size:1rem;font-weight:600">Crime Reports</h3>
      <p style="margin:0;color:#64748b;font-size:.9rem">File reports anonymously or with your account. Police verify and track status.</p>
    </div>
    <div class="card">
      <div style="font-size:1.8rem;margin-bottom:8px">🏠</div>
      <h3 style="margin:0 0 6px;font-size:1rem;font-weight:600">Property &amp; Rentals</h3>
      <p style="margin:0;color:#64748b;font-size:.9rem">House owners assign renters, track units, and update payment status.</p>
    </div>
    <div class="card">
      <div style="font-size:1.8rem;margin-bottom:8px">👮</div>
      <h3 style="margin:0 0 6px;font-size:1rem;font-weight:600">Role-Based Access</h3>
      <p style="margin:0;color:#64748b;font-size:.9rem">Admins, police, owners, and citizens each see only what they need.</p>
    </div>
  </div>

  <footer style="margin-top:48px;color:#94a3b8;font-size:.85rem">
    &copy; <?= date('Y') ?> CitySphere
  </footer>
</div>
</body></html>
