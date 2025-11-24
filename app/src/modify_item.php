<?php

require_once __DIR__.'/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    die('Forbidden: acceso denegado.');
}

$id = intval($_GET['item'] ?? 0);
$stmt = $mysqli->prepare("SELECT title,year,artist,genre,description FROM items WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($title,$year,$artist,$genre,$description);
if (!$stmt->fetch()) die('Item ez da aurkitu.');
$stmt->close();

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_validate_request()) {
        http_response_code(400);
        $errors[] = 'CSRF token invalid.';
    } else {
        $title_n = trim($_POST['title'] ?? '');
        $year_n = intval($_POST['year'] ?? 0);
        $artist_n = trim($_POST['artist'] ?? '');
        $genre_n = trim($_POST['genre'] ?? '');
        $desc_n = trim($_POST['description'] ?? '');
        if ($title_n==='') $errors[]='Izenburua behar.';
        if (empty($errors)) {
            $upd = $mysqli->prepare("UPDATE items SET title=?,year=?,artist=?,genre=?,description=? WHERE id=?");
            $upd->bind_param('sisssi', $title_n,$year_n,$artist_n,$genre_n,$desc_n,$id);
            if ($upd->execute()) header('Location: /items');
            else $errors[]='Error: '.$mysqli->error;
            $upd->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title> Diska aldatu</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2> Diska aldatu</h2>
  <?php if ($errors) foreach($errors as $e) echo "<p style='color:red'>".htmlspecialchars($e, ENT_QUOTES, 'UTF-8')."</p>"; ?>
  <form id="item_modify_form" method="post" action="">
    <?= csrf_token_input() ?>
    <label>Izenburua: <input name="title" value="<?=htmlspecialchars($title, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>Urtea: <input name="year" type="number" value="<?=htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>Artista: <input name="artist" value="<?=htmlspecialchars($artist, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>Generoa: <input name="genre" value="<?=htmlspecialchars($genre, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>Deskripzioa: <textarea name="description"><?=htmlspecialchars($description, ENT_QUOTES, 'UTF-8')?></textarea></label><br>
    <button id="item_modify_submit" type="submit">Gorde</button>
  </form>
</body>
</html>