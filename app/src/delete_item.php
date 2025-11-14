<?php

require_once __DIR__.'/db.php';
$id = intval($_GET['item'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate_request()) {
        http_response_code(400);
        $error = 'CSRF token invalid.';
    } else {
        // Balioztatua emanda
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $del = $mysqli->prepare("DELETE FROM items WHERE id=?");
            $del->bind_param('i',$id);
            if ($del->execute()) header('Location: /items');
            else $error = 'Errorea borratzean: '.$mysqli->error;
            $del->close();
        } else {
            header('Location: /items');
        }
    }
}
$stmt = $mysqli->prepare("SELECT title FROM items WHERE id=?");
$stmt->bind_param('i',$id);
$stmt->execute();
$stmt->bind_result($title);
$found = $stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Ezabatu</title></head>
<body>
  <?php if (!$found): ?>
    <p>Ez aurkitua</p>
  <?php else: ?>
    <h2>Ezabatu nahi duzu"<?=htmlspecialchars($title, ENT_QUOTES, 'UTF-8')?>"?</h2>
    <?php if (!empty($error)) echo "<p style='color:red'>".htmlspecialchars($error, ENT_QUOTES, 'UTF-8')."</p>"; ?>
    <form method="post" action="">
      <?= csrf_token_input() ?>
      <button id="item_delete_submit" name="confirm" value="yes" type="submit">Ezabatu</button>
      <button type="submit" name="confirm" value="no">Baliogabetu</button>
    </form>
  <?php endif; ?>

</body>
</html> 
