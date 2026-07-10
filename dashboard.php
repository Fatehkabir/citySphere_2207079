<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$nid   = current_user_nid();
$stats = get_dashboard_stats();
$mine  = get_my_recent_reports($nid);
?>
<?php include __DIR__ . '/includes/header.php'; ?>


<div class="page-header">
  <h1>Welcome, <?= e(current_user()['FULL_NAME']) ?></h1>
  <p>Here's what's happening across the city today.</p>
</div>
<div class="grid grid-4">
  <div class="stat"><div class="n"><?= (int)$stats['reports'] ?></div><div class="l">Total Reports</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['pending'] ?></div><div class="l">Pending</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['areas'] ?></div><div class="l">Areas</div></div>
  <div class="stat"><div class="n"><?= (int)$stats['buildings'] ?></div><div class="l">Buildings</div></div>
</div>
<div class="card" style="margin-top:24px">
  <h2>Your recent reports</h2>
  <?php if (!$mine): ?>
    <p class="muted">You haven't filed any reports yet. <a href="reports.php">File one →</a></p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Title</th><th>Status</th><th>When</th></tr>
    <?php foreach ($mine as $r): ?>
      <tr>
        <td>#<?= (int)$r['REPORT_ID'] ?></td>
        <td><?= e($r['TITLE']) ?></td>
        <td><span class="badge b-<?= e($r['STATUS']) ?>"><?= e($r['STATUS']) ?></span></td>
        <td class="muted"><?= e($r['TS']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
