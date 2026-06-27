<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Document</title>
</head>
<body>
    <div class="toolbar">
  <h1>Rentals</h1>
    <form method="post" class="inline-form" style="margin:0">
      <button type="submit" name="audit_rentals" value="1" class="btn">
        ▶ Run PL/SQL Cursor Audit
      </button>
    </form>
</div>

<div class="card">
  <h2>Assign Renter to a Unit</h2>
    <p class="muted">No buildings available. Add a building first.</p>
  <form method="post">
    <input type="hidden" name="assign" value="1">
    <div class="row">
      <label>Building</label>
      <select name="building_id" required>
          <option value=""></option>
      </select>
    </div>
    <div class="row">
      <label>Renter</label>
      <select name="renter_id" required>
        <option value="">— Select renter —</option>
          <option value=""></option>
      </select>
    </div>
    <div class="row"><label>Unit No.</label><input name="unit_no" placeholder="e.g. 3A" required></div>
    <div class="row"><label>Monthly Rent (৳)</label><input type="number" step="0.01" name="amount" required></div>
    <button class="btn-primary">Assign Renter</button>
  </form>
</div>

<div class="card">
  <h2>Rental List</h2>
    <p class="muted">No rentals found.</p>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Building</th><th>Unit</th><th>Owner</th><th>Renter</th>
        <th>Amount</th><th>Start</th><th>Payment</th><th>Action</th></tr>
      <tr>
        <td>RentalID</td>
        <td>name</td>
        <td>UnitID</td>
        <td>Owner</td>
        <td>Renter</td>
        <td>৳Rent_amount</td>
        <td class="muted"></td>
        <td>
          <span class="badge b-</span>
          
            <span class="badge style="background:#e2e8f0;color:#475569">ENDED</span>

        </td>
        <td>
          
          <form method="post" class="inline-form" style="display:flex;gap:6px;align-items:center">
            <input type="hidden" name="rental_id" value="">
            <select name="status" onchange="">
             
                <option value="<?= $s ?>" ></option>
        
            </select>
            <input type="hidden" name="pay" value="">
         
              <button type="submit" name="end_rental" value="1" class="btn-danger"
                      style="padding:4px 8px;font-size:.75rem"
                      onclick="return confirm('End this rental?')">End</button>
   
          </form>
   
        </td>
      </tr>

  </table></div>

</div>
</body>
</html>