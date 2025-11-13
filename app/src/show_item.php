<?php
// Item erakutsi: localhost:81/show_item?item={x}
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
<head><meta charset="utf-8"><title> Diska erakutsi </title></head>
<body>
  <?php if (!$found): ?>
    <p>Diska ez da aurkitu</p>
  <?php else: ?>
    <h2><?=htmlspecialchars($title)?></h2>
    <p>Urtea: <?=htmlspecialchars($year)?></p>
    <p>Artista: <?=htmlspecialchars($artist)?></p>
    <p>Genero: <?=htmlspecialchars($genre)?></p>
    <p>Deskripzioa: <?=nl2br(htmlspecialchars($description))?></p>
    <p><a href="/items">Listaketara bueltatu</a></p>
  <?php endif; ?>
</body>
</html>
