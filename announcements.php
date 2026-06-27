<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Announcements</title>
</head>
<body>

<div class="page-header">
    <h1>Announcements</h1>
    <p>Stay updated with the latest news and information.</p>
</div>
<div class="card">
   <h2>Post New Announcement</h2>
   <form method="POST" action="announcements.php">
      <div class="row">
        <label>Title</label>
        <input type="text" name="title" required placeholder="Announcement Title."> 
      </div>
      <div class="row">
         <label>Content</label>
         <textarea name="content" required placeholder="Announcement Details..."></textarea>
      </div>
      <div class="row">
      <label>Target Audience</label>
      <select name="target_role">
        <option value="all">Everyone</option>
        <option value="user">Citizens only</option>
        <option value="house_owner">House Owners only</option>
        <option value="police">Police only</option>
        <option value="admin">Admins only</option>
        </select>
       </div>
    <div class="row" style="margin-top: 16px;">
      <button type="submit" class="btn-primary">Post Announcement</button>
    </div>  
   </form>
</div>

<div class="grid grid-3" style="grid-template-columns: 1fr;">
    <div class="card">
      <p class="muted">No announcements yet.</p>
  </div>
      <div class="card announcement-card">
        <div class="announcement-header">
          <h3></h3>
          <span class="badge b"></span>
        </div>
        <p class="announcement-meta muted">Posted by</p>
        <div class="announcement-content" style="margin-top: 12px; white-space: pre-wrap;"></div>
      </div>

</div>
</body>
</html>