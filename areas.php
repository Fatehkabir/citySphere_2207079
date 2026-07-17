<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_area'])) {
        $areaId = (int)trim($_POST['area_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $city   = trim($_POST['city'] ?? '');
        try {
            if (!$areaId || !$name || !$city) throw new RuntimeException('Area ID, name, and city are all required.');
            add_area($nid, $areaId, $name, $city);
            flash('Area added successfully.', 'success');
        } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    } elseif (isset($_POST['delete_area'])) {
        $areaId = (int)trim($_POST['area_id'] ?? 0);
        try {
            if (!$areaId) throw new RuntimeException('Area ID is required.');
            delete_area($nid, $areaId);
            flash('Area deleted successfully.', 'success');
        } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    }
    header('Location: areas.php'); exit;
}

$areas = get_areas();
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Areas</h1></div>

<div class="card">
  <h2>Add New Area</h2>
  <form method="post">
    <input type="hidden" name="add_area" value="1">
    <div class="row"><label>Area ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="area_id" required min="1" placeholder="e.g. 10"></div>
    <div class="row"><label>Area Name <span style="color:#e53e3e">*</span></label>
      <input type="text" name="name" required placeholder="e.g. Gulshan"></div>
    <div class="row"><label>City <span style="color:#e53e3e">*</span></label>
      <input type="text" name="city" required placeholder="e.g. Dhaka"></div>
    <button class="btn-primary" name="add_area" value="1">Add Area</button>
  </form>
</div>

<div class="card">
  <h2>All Areas</h2>
  <?php if (!$areas): ?>
    <p class="muted">No areas found. Add one above.</p>
  <?php else: ?>
  <table>
    <tr><th>ID</th><th>Name</th><th>City</th><th>Buildings</th><th>Action</th></tr>
    <?php foreach ($areas as $a): ?>
      <tr>
        <td><?= (int)$a['AREA_ID'] ?></td>
        <td><?= e($a['NAME']) ?></td>
        <td><?= e($a['CITY']) ?></td>
        <td><?= (int)$a['BCOUNT'] ?></td>
        <td>
          <form method="post" class="inline-form" style="margin:0"
                onsubmit="return confirm('Delete area &quot;<?= e($a['NAME']) ?>&quot;? This cannot be undone.')">
            <input type="hidden" name="delete_area" value="1">
            <input type="hidden" name="area_id" value="<?= (int)$a['AREA_ID'] ?>">
            <button type="submit" class="btn-danger" style="padding:4px 10px;font-size:.8rem">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
