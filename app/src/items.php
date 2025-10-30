<?php

require_once __DIR__.'/db.php';
$result = $mysqli->query("SELECT id,title,artist FROM items");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Diskak</title></head>
<body>
  <h2>Disken lista</h2>
  <a href="/add_item">Diska berria ipini</a>
  <ul>
  <?php while($row = $result->fetch_assoc()): ?>
    <li>
      <?=htmlspecialchars($row['title'])?> - <?=htmlspecialchars($row['artist'])?>
      [<a href="/show_item?item=<?=urlencode($row['id'])?>">Ikusi</a>]
      [<a href="/modify_item?item=<?=urlencode($row['id'])?>">Aldatu</a>]
      [<a href="/delete_item?item=<?=urlencode($row['id'])?>">Kendu</a>]
    </li>
  <?php endwhile; ?>
  </ul>
</body>
</html>
