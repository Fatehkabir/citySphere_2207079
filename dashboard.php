<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Document</title>
</head>
<body>
<div class="page-header">
  <h1>Welcome,</h1>
  <p>Here's what's happening across the city today.</p>
</div>
<div class="grid grid-4">
  <div class="stat"><div class="n">Reports</div><div class="l">Total Reports</div></div>
  <div class="stat"><div class="n">Pending</div><div class="l">Pending</div></div>
  <div class="stat"><div class="n">Areas</div><div class="l">Areas</div></div>
  <div class="stat"><div class="n">Buildings</div><div class="l">Buildings</div></div>
</div>

<div class="card" style="margin-top:24px">
  <h2>Your recent reports</h2>
    <p class="muted">You haven't filed any reports yet. <a href="reports.php">File one →</a></p>
  <div class="table-wrap"><table>
    <tr><th>ID</th><th>Title</th><th>Status</th><th>When</th></tr>
      <tr>
        <td>ReportID</td>
        <td>Title</td>
        <td><span class="badge b-">Status</span></td>
        <td class="muted"></td>
      </tr>
  </table></div>
</div>
</body>
</html>