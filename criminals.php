<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$nid       = current_user_nid();
$canManage = has_role('police') || has_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManage) {
    try {
        $recordId   = (int)trim($_POST['record_id']  ?? 0);
        $citizenNid = trim($_POST['citizen_nid'] ?? '');
        $offense    = trim($_POST['offense']     ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $reportId   = ($_POST['report_id'] !== '') ? (int)$_POST['report_id'] : null;
        if (!$recordId) throw new RuntimeException('Record ID is required.');
        if (!$citizenNid || !$offense) throw new RuntimeException('Citizen and offense are required.');
        add_criminal_record($nid, $recordId, $citizenNid, $reportId, $offense, $desc);
        flash('Criminal record added.', 'success');
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: criminals.php'); exit;
}

if ($canManage) {
    $rows  = get_criminal_records();
    $users = get_users_list();
    $reps  = get_verified_reports();
} else {
    $rows = get_criminal_records($nid);
}
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Criminal Records</h1></div>

<?php if ($canManage): ?>
<div class="card">
  <h2>Add Criminal Record</h2>
  <form method="post">
    <div class="row"><label>Record ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="record_id" required min="1" placeholder="e.g. 3001"></div>
    <div class="row">
      <label>Citizen <span style="color:#e53e3e">*</span></label>
      <select name="citizen_nid" required>
        <option value="">— Select citizen —</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= e($u['NID']) ?>"><?= e($u['FULL_NAME']) ?> — <?= e($u['EMAIL']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row">
      <label>Linked Report <small>(optional)</small></label>
      <select name="report_id">
        <option value="">— None —</option>
        <?php foreach ($reps as $rep): ?>
          <option value="<?= (int)$rep['REPORT_ID'] ?>">#<?= (int)$rep['REPORT_ID'] ?> — <?= e($rep['TITLE']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row"><label>Offense <span style="color:#e53e3e">*</span></label>
      <input name="offense" required placeholder="Describe the offense"></div>
    <div class="row"><label>Description</label>
      <textarea name="description" rows="3" placeholder="Additional details..."></textarea></div>
    <button class="btn-primary">Add Record</button>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <h2><?= $canManage ? 'All Criminal Records' : 'Your Records' ?></h2>
  <?php if (!$rows): ?>
    <p class="muted">No records found.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Citizen</th><th>Offense</th><th>Description</th><th>Report</th><th>Officer</th><th>When</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>#<?= (int)$r['RECORD_ID'] ?></td>
        <td><?= e($r['CITIZEN']) ?></td>
        <td><?= e($r['OFFENSE']) ?></td>
        <td class="muted"><?= e(mb_strimwidth($r['DESCRIPTION'] ?? '', 0, 120, '…')) ?></td>
        <td><?= $r['REPORT_ID'] ? '#' . (int)$r['REPORT_ID'] : '—' ?></td>
        <td><?= e($r['OFFICER']) ?></td>
        <td class="muted"><?= e($r['TS']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
