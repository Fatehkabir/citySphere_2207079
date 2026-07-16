<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
if (!has_role('police') && !has_role('admin')) {
    http_response_code(403); die('Only police/admin can access this page.');
}
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $reportId = (int)($_POST['report_id'] ?? 0);
        $action   = $_POST['action'] ?? '';
        if (!$reportId || !in_array($action, ['verified','rejected','solved'])) {
            throw new RuntimeException('Invalid request.');
        }
        update_report_status($nid, $reportId, $action);
        flash('Report status updated to "' . $action . '".', 'success');
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: police.php'); exit;
}

$rows = get_police_queue();
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Police Queue</h1></div>

<div class="card">
  <?php if (!$rows): ?>
    <p class="muted">No reports in the queue.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Title &amp; Description</th><th>Area</th><th>Reporter</th><th>Status</th><th>When</th><th>Actions</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>#<?= (int)$r['REPORT_ID'] ?></td>
        <td>
          <strong><?= e($r['TITLE']) ?></strong>
          <?php if (!empty($r['DESCRIPTION'])): ?>
            <div class="muted" style="margin-top:4px;max-width:360px;font-size:.85rem">
              <?= e(mb_strimwidth($r['DESCRIPTION'], 0, 200, '…')) ?>
            </div>
          <?php endif; ?>
        </td>
        <td><?= e($r['AREA_NAME'] ?? '—') ?></td>
        <td><?= e($r['REPORTER']) ?></td>
        <td><span class="badge b-<?= e($r['STATUS']) ?>"><?= e($r['STATUS']) ?></span></td>
        <td class="muted"><?= e($r['TS']) ?></td>
        <td>
          <div class="row-actions">
            <?php foreach (['verified' => 'Verify', 'rejected' => 'Reject', 'solved' => 'Solve'] as $act => $lbl): ?>
              <form method="post" class="inline-form">
                <input type="hidden" name="report_id" value="<?= (int)$r['REPORT_ID'] ?>">
                <input type="hidden" name="action"    value="<?= $act ?>">
                <button class="btn"><?= $lbl ?></button>
              </form>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
