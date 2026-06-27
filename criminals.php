<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Criminals</title>
</head>
<body>
    <div class="card">
  <h2>Add Criminal Record</h2>
  <form method="post">
    <div class="row">
      <label>Citizen</label>
      <select name="citizen_id" required>
        <option value="">— Select citizen —</option>
       
          <option value="UserID"></option>
      
      </select>
    </div>
    <div class="row">
      <label>Linked Report (optional)</label>
      <select name="report_id">
        <option value="">— None —</option>
      
          <option value="ReportID"></option>
       
      </select>
    </div>
    <div class="row"><label>Offense</label><input type="text" name="offense" required placeholder="Describe the offense"></div>
    <div class="row"><label>Description</label><textarea name="description" rows="3" placeholder="Additional details..."></textarea></div>
    <button class="btn-primary">Add Record</button>
  </form>
</div>

<div class="card">
  <h2>All criminal records</h2>

    <p class="muted">No records found.</p>

  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Citizen</th><th>Offense</th><th>Description</th><th>Report</th><th>Officer</th><th>When</th></tr>
   
      <tr>
        <td>ReportID</td>
        <td>Citizen</td>
        <td>Offense</td>
        <td class="muted"></td>
        <td></td>
        <td>Officer</td>
        <td class="muted"></td>
      </tr>

  </table></div>
</div>


</body>
</html>