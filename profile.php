<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$nid  = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $photo = $user['PROFILE_PHOTO'] ?? '';
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $new = 'user_' . $nid . '_' . time() . '.' . $ext;
            $dst = __DIR__ . '/public/uploads/' . $new;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dst)) $photo = $new;
        }
    }
    if ($name) {
        try {
            update_profile($nid, $name, $phone, $photo);
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
  <form method="post" enctype="multipart/form-data">
    <div style="text-align:center;margin-bottom:24px">
      <?php if (!empty($user['PROFILE_PHOTO'])): ?>
        <img src="public/uploads/<?= e($user['PROFILE_PHOTO']) ?>" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:2px solid var(--primary)">
      <?php else: ?>
        <div style="width:120px;height:120px;border-radius:50%;background:#e0f2fe;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto;border:1px solid var(--border)">
          <?= strtoupper(substr($user['FULL_NAME'], 0, 1)) ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="row"><label>Profile Photo</label><input type="file" name="profile_photo" accept="image/*" style="padding:8px"></div>
    <div class="row"><label>National ID</label><input type="text" value="<?= e($user['NID']) ?>" disabled></div>
    <div class="row"><label>Full Name</label><input type="text" name="full_name" value="<?= e($user['FULL_NAME']) ?>" required></div>
    <div class="row"><label>Email</label><input type="email" value="<?= e($user['EMAIL']) ?>" disabled></div>
    <div class="row"><label>Phone</label><input type="text" name="phone" value="<?= e($user['PHONE'] ?? '') ?>"></div>
    <div class="row" style="margin-top:24px"><button type="submit" class="btn-primary" style="width:100%">Save Changes</button></div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
