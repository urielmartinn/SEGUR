<?php

require_once __DIR__.'/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $artist = trim($_POST['artist'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($title === '') {
        $errors[] = 'Izenburua behar.';
    }
    if ($year < 0 || $year > intval(date('Y')) + 1) {
        $errors[] = 'Urtea txarto';
    }
    if ($artist === '') {
        $errors[] = 'Abezlaria behar da.';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO items (title,year,artist,genre,description) VALUES (?,?,?,?,?)");
        if (!$stmt) {
            $errors[] = 'Errorea: ' . $mysqli->error;
        } else {
            $stmt->bind_param('sisss', $title, $year, $artist, $genre, $desc);
            if ($stmt->execute()) {
                header('Location: /items');
                exit;
            } else {
                $errors[] = 'Errorea: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Gehitu Diska</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Gehitu Diska</h2>

  <?php
  if (!empty($errors)) {
      echo '<ul style="color:red;">';
      foreach ($errors as $e) {
          echo '<li>' . htmlspecialchars($e) . '</li>';
      }
      echo '</ul>';
  }
  ?>

  <form id="item_add_form" method="post" action="">
    <label>Izenburua: <input name="title" required value="<?= isset($title) ? htmlspecialchars($title) : '' ?>"></label><br>
    <label>Urtea: <input name="year" type="number" required value="<?= isset($year) ? htmlspecialchars($year) : '' ?>"></label><br>
    <label>Abezlaria: <input name="artist" required value="<?= isset($artist) ? htmlspecialchars($artist) : '' ?>"></label><br>
    <label>Generoa: <input name="genre" value="<?= isset($genre) ? htmlspecialchars($genre) : '' ?>"></label><br>
    <label>Deskribapena: <textarea name="description"><?= isset($desc) ? htmlspecialchars($desc) : '' ?></textarea></label><br>
    <button id="item_add_submit" type="submit">Gorde</button>
  </form>
</body>
</html>
