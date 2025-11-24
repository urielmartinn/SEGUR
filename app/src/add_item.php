<?php

require_once __DIR__.'/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    die('Forbidden: acceso denegado.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_validate_request()) {
        http_response_code(400);
        $errors[] = 'CSRF token invalid.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $artist = trim($_POST['artist'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if ($title === '') {
            $errors[] = 'Izenburua beharrezkoa .';
        }
        if ($year < 0 || $year > intval(date('Y')) + 1) {
            $errors[] = 'Urtea ez du balio.';
        }
        if ($artist === '') {
            $errors[] = 'Artista beharrezkoa.';
        }

        if (empty($errors)) {
            $stmt = $mysqli->prepare("INSERT INTO items (title,year,artist,genre,description) VALUES (?,?,?,?,?)");
            if (!$stmt) {
                $errors[] = 'Kontsulta prestatazean errorea: ' . $mysqli->error;
            } else {
                $stmt->bind_param('sisss', $title, $year, $artist, $genre, $desc);
                if ($stmt->execute()) {
                    header('Location: /items');
                    exit;
                } else {
                    $errors[] = 'Errorea gehitzea: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Diska gehitu</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Diska gehitu</h2>


  <?php
  if (!empty($errors)) {
      echo '<ul style="color:red;">';
      foreach ($errors as $e) {
          echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
      }
      echo '</ul>';
  }
  ?>

    <form id="item_add_form" method="post" action="">
    <?= csrf_token_input() ?>
    <label>Izenburua: <input name="title" required value="<?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : '' ?>"></label><br>
    <label>Urtea: <input name="year" type="number" required value="<?= isset($year) ? htmlspecialchars($year, ENT_QUOTES, 'UTF-8') : '' ?>"></label><br>
    <label>Artista: <input name="artist" required value="<?= isset($artist) ? htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') : '' ?>"></label><br>
    <label>Generoa: <input name="genre" value="<?= isset($genre) ? htmlspecialchars($genre, ENT_QUOTES, 'UTF-8') : '' ?>"></label><br>
    <label>Deskripzioa: <textarea name="description"><?= isset($desc) ? htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') : '' ?></textarea></label><br>
    <button id="item_add_submit" type="submit">Gehitu</button>
    <a href="/" class="back-btn">Hasierara bueltatu</a>

  </form>
</body>
</html>
