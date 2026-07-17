<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$nid  = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name) {
        try {
            update_profile($nid, $name, $phone, null);
            $_SESSION['user'] = get_user($nid);
            flash('Profile updated.', 'success');
            header('Location: profile.php'); exit;
        } catch (Throwable $e) { flash('Error: ' . $e->getMessage(), 'error'); }
    } else { flash('Full name is required.', 'error'); }
}
$user = current_user();
include __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1>Your Profile</h1></div>
<div class="card" style="max-width:600px;margin:0 auto">
  <form method="post">
    <div style="text-align:center;margin-bottom:24px">
      <div style="width:120px;height:120px;border-radius:50%;background:#e0f2fe;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:4rem;margin:0 auto;border:2px solid var(--primary)">👤</div>
    </div>
    <div class="row"><label>National ID</label><input type="text" value="<?= e($user['NID']) ?>" disabled></div>
    <div class="row"><label>Full Name</label><input type="text" name="full_name" value="<?= e($user['FULL_NAME']) ?>" required></div>
    <div class="row"><label>Email</label><input type="email" value="<?= e($user['EMAIL']) ?>" disabled></div>
    <div class="row"><label>Phone</label><input type="text" name="phone" value="<?= e($user['PHONE'] ?? '') ?>"></div>
    <div class="row" style="margin-top:24px"><button type="submit" class="btn-primary" style="width:100%">Save Changes</button></div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
