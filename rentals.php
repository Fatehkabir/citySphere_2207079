<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$nid = current_user_nid();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['assign'])) {
            $rentalId   = (int)trim($_POST['rental_id']  ?? 0);
            $buildingId = (int)$_POST['building_id'];
            $renterNid  = trim($_POST['renter_nid'] ?? '');
            $unitNo     = trim($_POST['unit_no']    ?? '');
            $amount     = (float)$_POST['amount'];
            if (!$rentalId) throw new RuntimeException('Rental ID is required.');
            assign_renter($nid, $rentalId, $buildingId, $renterNid, $unitNo, $amount);
            flash('Renter assigned successfully.', 'success');

        } elseif (isset($_POST['pay'])) {
            update_payment_status($nid, (int)$_POST['rental_id'], $_POST['status']);
            flash('Payment status updated.', 'success');

        } elseif (isset($_POST['end_rental'])) {
            end_rental((int)$_POST['rental_id']);
            flash('Rental ended.', 'success');

        } elseif (isset($_POST['audit_rentals']) && has_role('admin')) {
            $count = audit_pending_rentals($nid);
            flash("PL/SQL cursor audit complete. Processed $count pending rentals.", 'success');
        }
    } catch (Throwable $e) { flash($e->getMessage(), 'error'); }
    header('Location: rentals.php'); exit;
}

$myBuildings = get_building_list(has_role('admin') ? null : $nid);
$users       = get_users_list();
$rentals     = get_rentals(has_role('admin') ? null : $nid);
include __DIR__ . '/includes/header.php';
?>
<div class="toolbar">
  <h1>Rentals</h1>
  <?php if (has_role('admin')): ?>
    <form method="post" class="inline-form" style="margin:0">
      <button type="submit" name="audit_rentals" value="1" class="btn">
        ▶ Run PL/SQL Cursor Audit
      </button>
    </form>
  <?php endif; ?>
</div>

<?php if (has_role('house_owner') || has_role('admin')): ?>
<div class="card">
  <h2>Assign Renter to a Unit</h2>
  <?php if (!$myBuildings): ?>
    <p class="muted">No buildings available. Add a building first.</p>
  <?php else: ?>
  <form method="post">
    <input type="hidden" name="assign" value="1">
    <div class="row"><label>Rental ID <span style="color:#e53e3e">*</span> <small>(unique number you choose)</small></label>
      <input type="number" name="rental_id" required min="1" placeholder="e.g. 1001"></div>
    <div class="row">
      <label>Building <span style="color:#e53e3e">*</span></label>
      <select name="building_id" required>
        <?php foreach ($myBuildings as $b): ?>
          <option value="<?= (int)$b['BUILDING_ID'] ?>"><?= e($b['NAME']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row">
      <label>Renter <span style="color:#e53e3e">*</span></label>
      <select name="renter_nid" required>
        <option value="">— Select renter —</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= e($u['NID']) ?>"><?= e($u['FULL_NAME']) ?> — <?= e($u['EMAIL']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row"><label>Unit No.</label><input type="text" name="unit_no" placeholder="e.g. 3A" required></div>
    <div class="row"><label>Monthly Rent (৳)</label><input type="number" step="0.01" name="amount" required></div>
    <button class="btn-primary">Assign Renter</button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
  <h2>Rental List</h2>
  <?php if (!$rentals): ?>
    <p class="muted">No rentals found.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Building</th><th>Unit</th><th>Owner</th><th>Renter</th>
        <th>Amount</th><th>Start</th><th>Payment</th><th>Action</th></tr>
    <?php foreach ($rentals as $r): ?>
      <tr>
        <td>#<?= (int)$r['RENTAL_ID'] ?></td>
        <td><?= e($r['BNAME']) ?></td>
        <td><?= e($r['UNIT_NO'] ?? '') ?></td>
        <td><?= e($r['OWNER']) ?></td>
        <td><?= e($r['RENTER']) ?></td>
        <td>৳<?= number_format((float)$r['RENT_AMOUNT'], 2) ?></td>
        <td class="muted"><?= e($r['SD'] ?? '—') ?></td>
        <td>
          <span class="badge b-<?= e($r['PAYMENT_STATUS']) ?>"><?= e($r['PAYMENT_STATUS']) ?></span>
          <?php if ($r['R_STATUS'] === 'ended'): ?>
            <span class="badge" style="background:#e2e8f0;color:#475569">ENDED</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($r['R_STATUS'] === 'active'): ?>
          <form method="post" class="inline-form" style="display:flex;gap:6px;align-items:center">
            <input type="hidden" name="rental_id" value="<?= (int)$r['RENTAL_ID'] ?>">
            <input type="hidden" name="pay" value="1">
            <select name="status" onchange="this.form.submit()">
              <?php foreach (['pending','paid','overdue'] as $s): ?>
                <option value="<?= $s ?>" <?= $s===$r['PAYMENT_STATUS']?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (has_role('house_owner') || has_role('admin')): ?>
              <button type="submit" name="end_rental" value="1" class="btn-danger"
                      style="padding:4px 8px;font-size:.75rem"
                      onclick="return confirm('End this rental?')">End</button>
            <?php endif; ?>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
