<?php 
require_once __DIR__ . '/includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nid   = trim($_POST['nid']       ?? '');
    $name  = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '') ?: null;

    if (!preg_match('/^[0-9]{6}$/', $nid)) {
        $err = 'National ID must be exactly 6 digits (numbers only).';
    } elseif (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
        $err = 'Please fill all required fields (password >= 6 chars).';
    } else {
        try {
            register_user($nid, $name, $email, $pw, $phone);
            flash('Account created. Please sign in.', 'success');
            header('Location: login.php'); exit;
        } catch (Throwable $ex) { $err = 'Could not register: ' . $ex->getMessage(); }
    }
}

?>


<!doctype html><html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign up · CitySphere</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="auth-page">
<div class="auth-wrap">
  <div class="auth-brand"><span style="font-size:2.5rem">🏙</span>
    <h1>Create Account</h1><p>Join CitySphere — admins can grant more roles later</p></div>
  <div class="card">
    <?php if ($err): ?><div class="flash flash-error"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <div class="row"><label>National ID <span style="color:#e53e3e">*</span> <small>(exactly 6 digits)</small></label>
        <input type="text" name="nid" required maxlength="6" minlength="6"
               pattern="[0-9]{6}" placeholder="e.g. 123456"
               value="<?= e($_POST['nid'] ?? '') ?>"></div>
      <div class="row"><label>Full Name <span style="color:#e53e3e">*</span></label>
        <input type="text" name="full_name" required
               value="<?= e($_POST['full_name'] ?? '') ?>"></div>
      <div class="row"><label>Email <span style="color:#e53e3e">*</span></label>
        <input type="email" name="email" required autocomplete="email"
               value="<?= e($_POST['email'] ?? '') ?>"></div>
      <div class="row"><label>Password <span style="color:#e53e3e">*</span> <small>(min 6 chars)</small></label>
        <input type="password" name="password" required minlength="6"></div>
      <div class="row"><label>Phone <small>(optional)</small></label>
        <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></div>
      <button class="btn-primary" type="submit" style="width:100%">Sign up</button>
      <p class="muted" style="margin-top:16px;text-align:center">Have an account? <a href="login.php">Login</a></p>
    </form>
  </div>
</div></body></html>