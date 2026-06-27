<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Buildings</title>
</head>
<body>
    
<div class="toolbar"><h1>Buildings</h1></div>
<div class="card">
<h2>Add Building</h2>
  <form method="post">
    <div class="row"><label>Building Name</label><input type="text" name="name" required></div>
    <div class="row"><label>Address</label><input type="text" name="address" required></div>
<div class="row">
      <label>Area</label>
      <select name="area_id" required>
        <option value="">— Select area —</option>
        
          <option value=""></option>
      
      </select>
    </div>
 <div class="row">
      <label>Owner</label>
      <select name="owner_id" required>
        <option value="">— Select owner —</option>
      
          <option value=""></option>
      </select>
      <small class="muted">Only users with the <em>house_owner</em> role appear here. Promote from Users page first.</small>
    </div>
    <div class="row"><label>Total Units</label><input type="number" name="units" min="1" value="1"></div>
    <button class="btn-primary">Add Building</button>
  </form>
</div>


<div class="card">
  <h2>Building list</h2>
    <p class="muted">No buildings found.</p>
  <table>
    <tr><th>ID</th><th>Name</th><th>Address</th><th>Area</th><th>Owner</th><th>Units</th></tr>
      <tr>
        <td>building ID</td>
        <td>Name</td>
        <td>Address</td>
        <td>Area_name</td>
        <td>Owner_name</td>
        <td>Total_units</td>
      </tr>
  </table>
</div>

</body>
</html>