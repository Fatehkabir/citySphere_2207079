<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $reportId = (int)trim($_POST['report_id'] ?? 0);
        $title    = trim($_POST['title']       ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $area     = (int)($_POST['area_id']    ?? 0);
        $anon     = isset($_POST['is_anonymous']);
        if (!$reportId) throw new RuntimeException('Report ID is required.');
        if (!$title || !$desc || !$area) throw new RuntimeException('Title, description, and area are required.');
        file_report($reportId, $nid, $anon, $area, $title, $desc);
        flash('Report submitted successfully.', 'success');
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: reports.php'); exit;
}

$areas     = get_area_list();
$canSeeAll = has_role('police') || has_role('admin');
$rows      = get_reports($canSeeAll ? null : $nid);
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar"><h1>Crime Reports</h1></div>

<div class="card">
  <h2>File a New Report</h2>
  <form method="post">
    <div class="row"><label>Report ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="report_id" required min="1" placeholder="e.g. 2001"></div>
    <div class="row"><label>Title <span style="color:#e53e3e">*</span></label>
      <input name="title" required maxlength="200" placeholder="Brief description of the incident"></div>
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
      <label>Description <span style="color:#e53e3e">*</span></label>
      <textarea name="description" required rows="4" placeholder="Describe the incident in detail..."></textarea>
    </div>
    <div class="row" style="flex-direction:row;align-items:center;gap:8px">
      <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
      <label for="is_anonymous" style="margin:0;text-transform:none;font-size:1rem">File anonymously</label>
    </div>
    <p id="anon-hint" class="muted" style="margin-top:4px">Your identity will be hidden from police and admins.</p>
    <button class="btn-primary">Submit Report</button>
  </form>
</div>

<div class="card">
  <h2><?= $canSeeAll ? 'All Reports' : 'My Reports' ?></h2>
  <?php if (!$rows): ?>
    <p class="muted">No reports found.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr>
      <th>ID</th><th>Title</th><th>Area</th>
      <?php if ($canSeeAll): ?><th>Reporter</th><?php endif; ?>
      <th>Status</th><th>When</th>
    </tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>#<?= (int)$r['REPORT_ID'] ?></td>
        <td><?= e($r['TITLE']) ?></td>
        <td><?= e($r['AREA_NAME'] ?? '—') ?></td>
        <?php if ($canSeeAll): ?>
          <td><?= e($r['REPORTER'] ?? '—') ?></td>
        <?php endif; ?>
        <td><span class="badge b-<?= e($r['STATUS']) ?>"><?= e($r['STATUS']) ?></span></td>
        <td class="muted"><?= e($r['TS']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
