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
  <a href="/add_item">Añadir item</a>
  <ul>
  <?php while($row = $result->fetch_assoc()): ?>
    <li>
      <?=htmlspecialchars($row['title'])?> - <?=htmlspecialchars($row['artist'])?>
      [<a href="/show_item?item=<?=urlencode($row['id'])?>">Ver</a>]
      [<a href="/modify_item?item=<?=urlencode($row['id'])?>">Modificar</a>]
      [<a href="/delete_item?item=<?=urlencode($row['id'])?>">Eliminar</a>]
    </li>
  <?php endwhile; ?>
  </ul>
</body>
</html>
