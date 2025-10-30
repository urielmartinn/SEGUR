<?php
// Añadir: localhost:81/add_item
// id formulario: item_add_form
// id boton: item_add_submit
require_once __DIR__.'/db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title = trim($_POST['title'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $artist = trim($_POST['artist'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($title==='') $errors[] = 'Título requerido.';
    if ($year < 0 || $year > intval(date('Y'))+1) $errors[] = 'Año inválido.';
    if ($artist==='') $errors[] = 'Artista requerido.';

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO items (title,year,artist,genre,description) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sisss', $title, $year, $artist, $genre, $desc);
        if ($stmt->execute()) header('Location: /items');
        else $errors[] = 'Error: '.$mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Añadir item</title><script src="/js/validation.js"></script></head>
<body>
  <h2>Añadir item</h2>
  <?php if ($errors): foreach($errors as $e) echo "<p style='color:red'>".htmlspecialchars($e)."</p>"; endforeach; ?>
  <form id="item_add_form" method="post" action="">
    <label>Título: <input name="title" required></label><br>
    <label>Año: <input name="year" type="number" required></label><br>
    <label>Artista: <input name="artist" required></label><br>
    <label>Género: <input name="genre"></label><br>
    <label>Descripción: <textarea name="description"></textarea></label><br>
    <button id="item_add_submit" type="submit">Añadir</button>
  </form>
</body>
</html>
