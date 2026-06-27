<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Document</title>
</head>
<body>
   <div class="toolbar"><h1>Crime Reports</h1></div>

<div class="card">
  <h2>File a New Report</h2>
  <form method="post">
    <div class="row"><label>Title</label><input type="text" name="title" required maxlength="200" placeholder="Brief description of the incident"></div>
    <div class="row">
      <label>Area</label>
      <select name="area_id" required>
        <option value="">— Select area —</option>
       
          <option value=""></option>

      </select>
    </div>
    <div class="row">
      <label>Description</label>
      <textarea name="description" required rows="4" placeholder="Describe the incident in detail..."></textarea>
    </div>
    <div class="row" style="flex-direction:row;align-items:center;gap:8px">
      <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1"
             onchange="document.getElementById('anon-hint').hidden=!this.checked">
      <label for="is_anonymous" style="margin:0">File anonymously</label>
    </div>
    <p id="anon-hint" class="muted" hidden>Your identity will be hidden from police and admins.</p>
    <button class="btn-primary">Submit Report</button>
  </form>
</div>

<div class="card">
  <h2>All reports</h2>
 
    <p class="muted">No reports found.</p>

  <div class="table-wrap"><table>
    <tr>
      <th>ID</th><th>Title</th><th>Area</th>
   
      <th>Status</th><th>When</th>
    </tr>
   
      <tr>
        <td>ReportID</td>
        <td>Title</td>
        <td>Area_name</td>
       
          <td>Reporter</td>
   
        <td><span class="badge b-"></span></td>
        <td class="muted"></td>
      </tr>

  </table></div>

</div>
 
</body>
</html>