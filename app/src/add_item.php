


<?php
// Añadir: localhost:81/add_item
// id formulario: item_add_form
// id boton: item_add_submit
require_once __DIR__.'/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $artist = trim($_POST['artist'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($title === '') {
        $errors[] = 'Título requerido.';
    }
    if ($year < 0 || $year > intval(date('Y')) + 1) {
        $errors[] = 'Año inválido.';
    }
    if ($artist === '') {
        $errors[] = 'Artista requerido.';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO items (title,year,artist,genre,description) VALUES (?,?,?,?,?)");
        if (!$stmt) {
            $errors[] = 'Error al preparar la consulta: ' . $mysqli->error;
        } else {
            $stmt->bind_param('sisss', $title, $year, $artist, $genre, $desc);
            if ($stmt->execute()) {
                header('Location: /items');
                exit;
            } else {
                $errors[] = 'Error al insertar: ' . $stmt->error;
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
  <title>Añadir item</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Añadir item</h2>

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
    <label>Título: <input name="title" required value="<?= isset($title) ? htmlspecialchars($title) : '' ?>"></label><br>
    <label>Año: <input name="year" type="number" required value="<?= isset($year) ? htmlspecialchars($year) : '' ?>"></label><br>
    <label>Artista: <input name="artist" required value="<?= isset($artist) ? htmlspecialchars($artist) : '' ?>"></label><br>
    <label>Género: <input name="genre" value="<?= isset($genre) ? htmlspecialchars($genre) : '' ?>"></label><br>
    <label>Descripción: <textarea name="description"><?= isset($desc) ? htmlspecialchars($desc) : '' ?></textarea></label><br>
    <button id="item_add_submit" type="submit">Añadir</button>
  </form>
</body>
</html>
