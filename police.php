<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Police</title>
</head>
<body>
    <div class="toolbar"><h1>Police Queue</h1></div>

<div class="card">
 
    <p class="muted">No reports in the queue.</p>

  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Title &amp; Description</th><th>Area</th><th>Reporter</th><th>Status</th><th>When</th><th>Actions</th></tr>
    
      <tr>
        <td>ReportID</td>
        <td>
          <strong>Title</strong>
    
            <div class="muted" style="margin-top:4px;max-width:360px;font-size:.85rem">
           
            </div>

        </td>
        <td>Area_name</td>
        <td>Reporter</td>
        <td><span class="badge b-</span></td>
        <td class="muted"></td>
        <td>
          <div class="row-actions">
    
              <form method="post" class="inline-form">
                <input type="hidden" name="report_id" value="">
                <input type="hidden" name="action"    value="">
                <button class="btn"></button>
              </form>
        
          </div>
        </td>
      </tr>

  </table></div>

</div>
</body>
</html>