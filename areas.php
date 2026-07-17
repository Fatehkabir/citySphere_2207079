<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $areaId = (int)trim($_POST['area_id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $city   = trim($_POST['city'] ?? '');
    try {
        if (!$areaId || !$name || !$city) throw new RuntimeException('Area ID, name, and city are all required.');
        add_area($nid, $areaId, $name, $city);
        flash('Area added successfully.', 'success');
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: areas.php'); exit;
}

$areas = get_areas();
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Areas</h1></div>

<div class="card">
  <h2>Add New Area</h2>
  <form method="post">
    <div class="row"><label>Area ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="area_id" required min="1" placeholder="e.g. 10"></div>
    <div class="row"><label>Area Name <span style="color:#e53e3e">*</span></label>
      <input type="text" name="name" required placeholder="e.g. Gulshan"></div>
    <div class="row"><label>City <span style="color:#e53e3e">*</span></label>
      <input type="text" name="city" required placeholder="e.g. Dhaka"></div>
    <button class="btn-primary">Add Area</button>
  </form>
</div>

<div class="card">
  <h2>All Areas</h2>
  <?php if (!$areas): ?>
    <p class="muted">No areas found. Add one above.</p>
  <?php else: ?>
  <table>
    <tr><th>ID</th><th>Name</th><th>City</th><th>Buildings</th></tr>
    <?php foreach ($areas as $a): ?>
      <tr>
        <td><?= (int)$a['AREA_ID'] ?></td>
        <td><?= e($a['NAME']) ?></td>
        <td><?= e($a['CITY']) ?></td>
        <td><?= (int)$a['BCOUNT'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
