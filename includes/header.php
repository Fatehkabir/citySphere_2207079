<?php
require_once __DIR__ . '/auth.php';
$user  = current_user();
$roles = current_roles();
$page  = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>CitySphere</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="dashboard.php"><span>🏙</span> CitySphere</a>
  <?php if ($user): ?>
    <nav class="nav">
      <a href="dashboard.php"     class="<?= $page==='dashboard.php'?'active':'' ?>">Dashboard</a>
      <a href="announcements.php" class="<?= $page==='announcements.php'?'active':'' ?>">Announcements</a>
      <a href="reports.php"       class="<?= $page==='reports.php'?'active':'' ?>">Reports</a>
      <a href="rentals.php"       class="<?= $page==='rentals.php'?'active':'' ?>">Rentals</a>
      <?php if (has_role('house_owner') || has_role('admin')): ?>
        <a href="buildings.php"   class="<?= $page==='buildings.php'?'active':'' ?>">Buildings</a>
      <?php endif; ?>
      <?php if (has_role('police') || has_role('admin')): ?>
        <a href="police.php"      class="<?= $page==='police.php'?'active':'' ?>">Police Queue</a>
        <a href="criminals.php"   class="<?= $page==='criminals.php'?'active':'' ?>">Records</a>
      <?php endif; ?>
      <?php if (has_role('admin')): ?>
        <a href="areas.php"       class="<?= $page==='areas.php'?'active':'' ?>">Areas</a>
        <a href="admin_users.php" class="<?= $page==='admin_users.php'?'active':'' ?>">Users</a>
      <?php endif; ?>
    </nav>
    <div class="userbox">
      <a href="profile.php" class="who" style="display:flex;align-items:center;gap:6px;text-decoration:none">
        <span style="font-size:1.2rem;line-height:1">👤</span>
        <?= e($user['FULL_NAME']) ?>
      </a>
      <?php foreach ($roles as $r): ?>
        <span class="role role-<?= e($r) ?>"><?= e($r) ?></span>
      <?php endforeach; ?>
      <a class="btn-ghost" href="logout.php" style="font-size:.85rem;padding:4px 10px">Logout</a>
    </div>
  <?php endif; ?>
</header>
<main class="container">
<?php foreach (pop_flashes() as $f): ?>
  <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
