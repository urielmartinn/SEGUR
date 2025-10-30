<?php
// Mostrar item: localhost:81/show_item?item={x}
require_once __DIR__.'/db.php';
$id = intval($_GET['item'] ?? 0);
$stmt = $mysqli->prepare("SELECT title,year,artist,genre,description FROM items WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($title,$year,$artist,$genre,$description);
$found = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Mostrar item</title></head>
<body>
  <?php if (!$found): ?>
    <p>Item no encontrado.</p>
  <?php else: ?>
    <h2><?=htmlspecialchars($title)?></h2>
    <p>Año: <?=htmlspecialchars($year)?></p>
    <p>Artista: <?=htmlspecialchars($artist)?></p>
    <p>Género: <?=htmlspecialchars($genre)?></p>
    <p>Descripción: <?=nl2br(htmlspecialchars($description))?></p>
  <?php endif; ?>
</body>
</html>
