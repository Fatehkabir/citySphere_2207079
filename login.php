<?php 

require_once __DIR__ . '/includes/auth.php';
$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    $rows=db_select(
      'SELECT user_id, full_name, email, password_hash FROM users WHERE email = :em',
      [':em' => $email]
    );
    if($rows && password_verify($pw,$rows[0]['PASSWORD_HASH'])){
      login_user($rows[0]);
    }
    $err='Invalid email or password.';


}


?>




<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login · CitySphere</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-wrap">
  <div class="auth-brand">
    <span style="font-size:2.5rem">🏙</span>
    <h1>Welcome back</h1>
    <p>Sign in to access your CitySphere dashboard</p>
  </div>
  <div class="card">
     <?php if ($err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="row"><label>Email</label><input type="email" name="email" required autocomplete="email"></div>
      <div class="row"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div>
      <button class="btn-primary" type="submit" style="width:100%">Sign in</button>
      <p class="muted" style="margin-top:16px;text-align:center">No account? <a href="signup.php">Sign up</a></p>
    </form>
  </div>
</div>
</body>
</html>
