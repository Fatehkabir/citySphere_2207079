<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Document</title>
</head>
<body>
    <div class="page-header"><h1>Your Profile</h1></div>
<div class="card" style="max-width:600px;margin:0 auto">
  <form method="post" enctype="multipart/form-data">
    <div style="text-align:center;margin-bottom:24px">
     
        <img src="public/uploads/" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--primary)">

        <div style="width:120px;height:120px;border-radius:50%;background:var(--gradient);color:#fff;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto">

        </div>

    </div>
    <div class="row"><label>Profile Photo</label><input type="file" name="profile_photo" accept="image/*" style="padding:8px"></div>
    <div class="row"><label>Full Name</label><input type="text" name="full_name" value="" required></div>
    <div class="row"><label>Email</label><input type="email" value="" disabled></div>
    <div class="row"><label>Phone</label><input type="text" name="phone" value=""></div>
    <div class="row"><label>National ID</label><input type="text" name="nid" value=""></div>
    <div class="row" style="margin-top:24px"><button type="submit" class="btn-primary" style="width:100%">Save Changes</button></div>
  </form>
</div>
</body>
</html>