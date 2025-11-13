<?php

session_start();
require_once __DIR__.'/db.php';
$result = $mysqli->query("SELECT id,title,artist FROM items");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Diskak</title></head>
<body>
  <h2>Disken lista</h2>
  <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <a href="/add_item">Diska berria ipini</a>
  <?php endif; ?>
  <ul>
  <?php while($row = $result->fetch_assoc()): ?>
    <li>
      <?=htmlspecialchars($row['title'])?> - <?=htmlspecialchars($row['artist'])?>
      [<a href="/show_item?item=<?=urlencode($row['id'])?>">Ikusi</a>]
      <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        [<a href="/modify_item?item=<?=urlencode($row['id'])?>">Aldatu</a>]
        [<a href="/delete_item?item=<?=urlencode($row['id'])?>">Kendu</a>]
      <?php endif; ?>
    </li>
  <?php endwhile; ?>
  </ul>
  <a href="/" class="back-btn">Hasierara bueltatu</a>

</body>
</html> 
