<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if (!has_role('house_owner') && !has_role('admin')) {
    http_response_code(403); die('Only house owners or admins.');
}
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!has_role('admin')) {
            throw new RuntimeException('Only admins can create buildings. Ask an admin to add yours.');
        }
        $buildingId = (int)trim($_POST['building_id'] ?? 0);
        $name       = trim($_POST['name']    ?? '');
        $address    = trim($_POST['address'] ?? '');
        $areaId     = (int)($_POST['area_id']   ?? 0);
        $ownerNid   = trim($_POST['owner_nid']  ?? '');
        $units      = (int)($_POST['units']     ?? 1);
        if (!$buildingId || !$name || !$address || !$areaId || !$ownerNid) {
            throw new RuntimeException('All fields including Building ID are required.');
        }
        add_building($nid, $buildingId, $name, $address, $areaId, $ownerNid, $units);
        flash('Building added successfully.', 'success');
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: buildings.php'); exit;
}

$areas     = get_area_list();
$owners    = get_house_owners();
$buildings = get_buildings(has_role('admin') ? null : $nid);
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Buildings</h1></div>

<?php if (has_role('admin')): ?>
<div class="card">
  <h2>Add Building</h2>
  <form method="post">
    <div class="row"><label>Building ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="building_id" required min="1" placeholder="e.g. 101"></div>
    <div class="row"><label>Building Name <span style="color:#e53e3e">*</span></label>
      <input type="text" name="name" required></div>
    <div class="row"><label>Address <span style="color:#e53e3e">*</span></label>
      <input type="text" name="address" required></div>
    <div class="row">
      <label>Area <span style="color:#e53e3e">*</span></label>
      <select name="area_id" required>
        <option value="">— Select area —</option>
        <?php foreach ($areas as $a): ?>
          <option value="<?= (int)$a['AREA_ID'] ?>"><?= e($a['NAME']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row">
      <label>Owner <span style="color:#e53e3e">*</span></label>
      <select name="owner_nid" required>
        <option value="">— Select owner —</option>
        <?php foreach ($owners as $o): ?>
          <option value="<?= e($o['NID']) ?>"><?= e($o['FULL_NAME']) ?> — <?= e($o['EMAIL']) ?></option>
        <?php endforeach; ?>
      </select>
      <small class="muted">Only users with the <em>house_owner</em> role appear here. Promote from Users page first.</small>
    </div>
    <div class="row"><label>Total Units</label>
      <input type="number" name="units" min="1" value="1"></div>
    <button class="btn-primary">Add Building</button>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <h2><?= has_role('admin') ? 'All Buildings' : 'My Buildings' ?></h2>
  <?php if (!$buildings): ?>
    <p class="muted">No buildings found.</p>
  <?php else: ?>
  <table>
    <tr><th>ID</th><th>Name</th><th>Address</th><th>Area</th><th>Owner</th><th>Units</th></tr>
    <?php foreach ($buildings as $b): ?>
      <tr>
        <td><?= (int)$b['BUILDING_ID'] ?></td>
        <td><?= e($b['NAME']) ?></td>
        <td><?= e($b['ADDRESS']) ?></td>
        <td><?= e($b['AREA_NAME']) ?></td>
        <td><?= e($b['OWNER_NAME']) ?></td>
        <td><?= (int)$b['TOTAL_UNITS'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
