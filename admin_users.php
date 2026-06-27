<DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Users & Roles · CitySphere</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="toolbar"><h1>USERS & ROLES</h1></div>
<div class="card">
   <table>
     <tr><th>ID</th><th>Name</th><th>Email</th><th>Roles</th><th>Manage</th><tr>
       <tr>
          <td>user_id</td>
          <td>Full name</td>
          <td>email</td>
          <td>roles</td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
               <input type="hidden" name="user_id" value="userID">
               <select name="role">
                 <option value="citizen">ADMIN</option>
               </select>
                <button class="btn-primary" name="grant" value="1">Grant</button>
               <button class="btn-danger"  name="revoke" value="1"
                    data-confirm="Revoke this role?">Revoke</button>
            </form>
         </td>
       </tr>
   </table>    



</div>

</body>
</html>