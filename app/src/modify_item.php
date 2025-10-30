<?php
// Modificar item: localhost:81/modify_item?item={x}
// id formulario: item_modify_form
// id boton: item_modify_submit
require_once __DIR__.'/db.php';
$id = intval($_GET['item'] ?? 0);
$stmt = $mysqli->prepare("SELECT title,year,artist,genre,description FROM items WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($title,$year,$artist,$genre,$description);
if (!$stmt->fetch()) die('Item no encontrado.');
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title_n = trim($_POST['title'] ?? '');
    $year_n = intval($_POST['year'] ?? 0);
    $artist_n = trim($_POST['artist'] ?? '');
    $genre_n = trim($_POST['genre'] ?? '');
    $desc_n = trim($_POST['description'] ?? '');
    if ($title_n==='') $errors[]='Título requerido.';
    if (empty($errors)) {
        $upd = $mysqli->prepare("UPDATE items SET title=?,year=?,artist=?,genre=?,description=? WHERE id=?");
        $upd->bind_param('sisssi', $title_n,$year_n,$artist_n,$genre_n,$desc_n,$id);
        if ($upd->execute()) header('Location: /items');
        else $errors[]='Error: '.$mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Modificar item</title><script src="/js/validation.js"></script></head>
<body>
  <h2>Modificar item</h2>
  <?php if ($errors) foreach($errors as $e) echo "<p style='color:red'>".htmlspecialchars($e)."</p>"; ?>
  <form id="item_modify_form" method="post" action="" onsubmit="return validateItemForm();">
    <label>Título: <input name="title" value="<?=htmlspecialchars($title)?>" required></label><br>
    <label>Año: <input name="year" type="number" value="<?=htmlspecialchars($year)?>" required></label><br>
    <label>Artista: <input name="artist" value="<?=htmlspecialchars($artist)?>" required></label><br>
    <label>Género: <input name="genre" value="<?=htmlspecialchars($genre)?>"></label><br>
    <label>Descripción: <textarea name="description"><?=htmlspecialchars($description)?></textarea></label><br>
    <button id="item_modify_submit" type="submit">Guardar</button>
  </form>
</body>
</html>
