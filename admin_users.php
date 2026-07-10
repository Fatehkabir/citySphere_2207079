<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');
$nid   = current_user_nid();
$ROLES = ['user', 'house_owner', 'police', 'admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = trim($_POST['user_nid'] ?? '');
    $role   = $_POST['role'] ?? '';
    try {
        if (!in_array($role, $ROLES)) throw new RuntimeException('Invalid role.');
        
        if (isset($_POST['grant']) && ($role === 'house_owner' || $role === 'police')) {
            $targetRoles = load_roles_for($target);
            if (in_array('admin', $targetRoles, true)) {
                throw new RuntimeException('Admins cannot have house_owner or police roles.');
            }
        }
        
        if (isset($_POST['grant'])) {
            grant_role($nid, $target, $role);
            flash("Role '$role' granted.", 'success');
        } elseif (isset($_POST['revoke'])) {
            revoke_role($nid, $target, $role);
            flash("Role '$role' revoked.", 'success');
        }
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: admin_users.php'); exit;
}

$users = get_all_users();
include __DIR__ . '/includes/header.php';
?>


<div class="toolbar"><h1>Users &amp; Roles</h1></div>

<div class="card">
  <table>
    <tr><th>NID</th><th>Name</th><th>Email</th><th>Current Roles</th><th>Manage</th></tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['NID']) ?></td>
        <td><?= e($u['FULL_NAME']) ?></td>
        <td><?= e($u['EMAIL']) ?></td>
        <td>
          <?php
          $userRoles = array_filter(explode(', ', $u['ROLES'] ?? ''));
          foreach ($userRoles as $r):
          ?>
            <span class="role role-<?= e($r) ?>"><?= e($r) ?></span>
          <?php endforeach; ?>
          <?php if (!$userRoles): ?><span class="muted">—</span><?php endif; ?>
        </td>
        <td>
          <form method="post" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
            <input type="hidden" name="user_nid" value="<?= e($u['NID']) ?>">
            <select name="role">
              <?php foreach ($ROLES as $r): ?>
                <option value="<?= $r ?>"><?= $r ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn-primary" name="grant"  value="1">Grant</button>
            <button class="btn-danger"  name="revoke" value="1"
                    onclick="return confirm('Revoke this role?')">Revoke</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>