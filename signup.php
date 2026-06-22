<?php 

require_once __DIR__ . '/includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name  = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '') ?: null;
    $nid   = trim($_POST['nid'] ?? '') ?: null;

    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
        $err = 'Please fill all fields (password >= 6 chars).';
    } else {
        try {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            db_exec(
              'CALL Add_User(:n, :e, :h, :p, :nid, @uid)',
              [':n'=>$name, ':e'=>$email, ':h'=>$hash, ':p'=>$phone, ':nid'=>$nid],
              [':uid'=>'@uid']
            );
            flash('Account created. Please sign in.', 'success');
            header('Location: login.php'); exit;
        } catch (Throwable $ex) {
            $err = 'Could not register: ' . $ex->getMessage();
        }
    }

}


?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign up · CitySphere</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-wrap">
  <div class="auth-brand">
    <span style="font-size:2.5rem">🏙</span>
    <h1>Create account</h1>
    <p>Join CitySphere as a citizen — admins can grant more roles later</p>
  </div>
  <div class="card">
    <?php if ($err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="row"><label>Full name</label><input type="text" name="full_name" required autocomplete="name"></div>
      <div class="row"><label>Email</label><input type="email" name="email" required autocomplete="email"></div>
      <div class="row"><label>Password</label><input type="password" name="password" required minlength="6" autocomplete="new-password"></div>
      <div class="row"><label>Phone (optional)</label><input type="text" name="phone" autocomplete="tel"></div>
      <div class="row"><label>National ID (optional)</label><input type="text" name="nid"></div>
      <button class="btn-primary" type="submit" style="width:100%">Sign up</button>
      <p class="muted" style="margin-top:16px;text-align:center">Have an account? <a href="login.php">Login</a></p>
    </form>
  </div>
</div>
</body>
</html>
