<?php
// Listado: localhost:81/items
require_once __DIR__.'/db.php';
$result = $mysqli->query("SELECT id,title,artist FROM items");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Items</title></head>
<body>
  <h2>Listado de Items</h2>
  <a href="/src/add_item.php">Añadir item</a>
  <ul>
  <?php while($row = $result->fetch_assoc()): ?>
    <li>
      <?=htmlspecialchars($row['title'])?> - <?=htmlspecialchars($row['artist'])?>
      [<a href="/src/show_item.php?item=<?=urlencode($row['id'])?>">Ver</a>]
      [<a href="/src/modify_item.php?item=<?=urlencode($row['id'])?>">Modificar</a>]
      [<a href="/src/delete_item.php?item=<?=urlencode($row['id'])?>">Eliminar</a>]
    </li>
  <?php endwhile; ?>
  </ul>
</body>
</html>
