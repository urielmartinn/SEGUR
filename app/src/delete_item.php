<?php
// Ezabatu diska: localhost:81/delete_item?item={x}
// id botoia: item_delete_submit
require_once __DIR__.'/db.php';
$id = intval($_GET['item'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Jasotako berrespena
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $del = $mysqli->prepare("DELETE FROM items WHERE id=?");
        $del->bind_param('i',$id);
        if ($del->execute()) header('Location: /items');
        else $error = 'Errorea borratzean: '.$mysqli->error;
    } else {
        header('Location: /items');
    }
}
$stmt = $mysqli->prepare("SELECT title FROM items WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($title);
$found = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Diska ezabatu</title></head>
<body>
  <?php if (!$found): ?>
    <p>Diska ez aurkitua</p>
  <?php else: ?>
    <h2>¿Seguro ezabatu nahi duzula "<?=htmlspecialchars($title)?>"?</h2>
    <?php if (!empty($error)) echo "<p style='color:red'>".htmlspecialchars($error)."</p>"; ?>
    <form method="post" action="">
      <button id="item_delete_submit" name="confirm" value="yes" type="submit">Ezabatu</button>
      <button type="submit" name="confirm" value="no">Ezeztatu</button>
    </form>
  <?php endif; ?>
</body>
</html>
