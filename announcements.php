<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user     = current_user();
$nid      = current_user_nid();
$is_admin = has_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $allowed = ['all','user','house_owner','police','admin'];

    if (isset($_POST['post_announcement'])) {
        $annId   = (int)trim($_POST['announcement_id'] ?? 0);
        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        $role    = $_POST['target_role']  ?? 'all';
        if (!$annId) {
            flash('Announcement ID is required.', 'error');
        } elseif (!$title || !$content) {
            flash('Title and content are required.', 'error');
        } elseif (!in_array($role, $allowed)) {
            flash('Invalid target role.', 'error');
        } else {
            try {
                post_announcement($annId, $title, $content, $role, $nid);
                flash('Announcement posted.', 'success');
            } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
        }

    } elseif (isset($_POST['update_announcement'])) {
        $annId   = (int)trim($_POST['announcement_id'] ?? 0);
        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        $role    = $_POST['target_role']  ?? 'all';
        if (!$annId) {
            flash('Announcement ID is required.', 'error');
        } elseif (!$title || !$content) {
            flash('Title and content are required.', 'error');
        } elseif (!in_array($role, $allowed)) {
            flash('Invalid target role.', 'error');
        } else {
            try {
                update_announcement($nid, $annId, $title, $content, $role);
                flash('Announcement updated.', 'success');
            } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
        }

    } elseif (isset($_POST['delete_announcement'])) {
        $annId = (int)trim($_POST['announcement_id'] ?? 0);
        try {
            if (!$annId) throw new RuntimeException('Announcement ID is required.');
            delete_announcement($nid, $annId);
            flash('Announcement deleted.', 'success');
        } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    }
    header('Location: announcements.php'); exit;
}

$announcements = get_announcements_for_user($nid);

$editAnn = null;
if ($is_admin && isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($announcements as $a) {
        if ((int)$a['ID'] === $editId) { $editAnn = $a; break; }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <h1>Announcements</h1>
  <p>Stay updated with the latest city news.</p>
</div>

<?php if ($is_admin): ?>
<div class="card">
  <h2>Post New Announcement</h2>
  <form method="post">
    <input type="hidden" name="post_announcement" value="1">
    <div class="row"><label>Announcement ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="announcement_id" required min="1" placeholder="e.g. 5001"></div>
    <div class="row"><label>Title <span style="color:#e53e3e">*</span></label>
      <input type="text" name="title" required placeholder="Announcement title"></div>
    <div class="row"><label>Content <span style="color:#e53e3e">*</span></label>
      <textarea name="content" required rows="4" placeholder="Announcement details..."></textarea></div>
    <div class="row">
      <label>Target Audience</label>
      <select name="target_role">
        <option value="all">Everyone</option>
        <option value="user">Citizens only</option>
        <option value="house_owner">House Owners only</option>
        <option value="police">Police only</option>
        <option value="admin">Admins only</option>
      </select>
    </div>
    <div class="row" style="margin-top:16px">
      <button type="submit" class="btn-primary">Post Announcement</button>
    </div>
  </form>
</div>

<?php if ($editAnn): ?>
<div class="card" style="border:2px solid var(--primary)">
  <h2>✏️ Edit Announcement #<?= (int)$editAnn['ID'] ?></h2>
  <form method="post">
    <input type="hidden" name="update_announcement" value="1">
    <input type="hidden" name="announcement_id" value="<?= (int)$editAnn['ID'] ?>">
    <div class="row"><label>Title <span style="color:#e53e3e">*</span></label>
      <input type="text" name="title" required value="<?= e($editAnn['TITLE']) ?>"></div>
    <div class="row"><label>Content <span style="color:#e53e3e">*</span></label>
      <textarea name="content" required rows="4"><?= e($editAnn['CONTENT']) ?></textarea></div>
    <div class="row">
      <label>Target Audience</label>
      <select name="target_role">
        <?php foreach (['all','user','house_owner','police','admin'] as $r): ?>
          <option value="<?= $r ?>" <?= $editAnn['TARGET_ROLE'] === $r ? 'selected' : '' ?>>
            <?= ucfirst($r === 'house_owner' ? 'House Owners only' : ($r === 'all' ? 'Everyone' : ucfirst($r) . ' only')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="display:flex;gap:10px;margin-top:8px">
      <button type="submit" class="btn-primary">Save Changes</button>
      <a href="announcements.php" class="btn" style="padding:8px 16px;text-decoration:none">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!$announcements): ?>
  <div class="card"><p class="muted">No announcements yet.</p></div>
<?php else: ?>
  <?php foreach ($announcements as $a): ?>
    <div class="card announcement-card" style="margin-bottom:16px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <h3 style="margin:0"><?= e($a['TITLE']) ?></h3>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge b-<?= $a['TARGET_ROLE'] === 'all' ? 'verified' : 'pending' ?>">
            <?= e(ucfirst($a['TARGET_ROLE'])) ?>
          </span>
          <?php if ($is_admin): ?>
            <a href="announcements.php?edit=<?= (int)$a['ID'] ?>"
               class="btn" style="padding:3px 10px;font-size:.78rem;text-decoration:none">Edit</a>
            <form method="post" class="inline-form" style="margin:0"
                  onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
              <input type="hidden" name="delete_announcement" value="1">
              <input type="hidden" name="announcement_id" value="<?= (int)$a['ID'] ?>">
              <button type="submit" class="btn-danger" style="padding:3px 10px;font-size:.78rem">Delete</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <p class="muted" style="font-size:.85rem;margin-bottom:10px">
        Posted by <strong><?= e($a['AUTHOR']) ?></strong> on <?= e($a['TS']) ?>
      </p>
      <div style="white-space:pre-wrap;line-height:1.6"><?= e($a['CONTENT']) ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
