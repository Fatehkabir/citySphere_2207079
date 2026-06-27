<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Areas</title>
</head>
<body>
    <div class="toolbar"><h1>Areas</h1></div>
    <div>
        <h2>Add area</h2>
        <form method="POST">
        <div class="row"><label>Name</label><input type="text" name="name" required placeholder="Enter Area name"></div>
        <div class="row"><label>City</label><input type="text" name="city" required placeholder="Enter City name"></div>    
        <button class="btn-primary">Add area</button>
    </form>
    </div>
<br>

<div class="card">
  <h2>All areas</h2>
  <table>
    <tr><th>ID</th><th>Name</th><th>City</th><th>Buildings</th></tr>
  
      <tr><td></td>
          <td></td></tr>
  
  </table>
</div>


</body>
</html>