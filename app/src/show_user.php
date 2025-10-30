<?php
// Mostrar usuario: localhost:81/show_user?user={x}
session_start();
require_once __DIR__.'/db.php';
$user = $_GET['user'] ?? '';
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email,username FROM users WHERE username=?");
$stmt->bind_param('s',$user);
$stmt->execute();
$stmt->bind_result($fullname,$dni,$phone,$birthdate,$email,$username);
$found = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Mostrar usuario</title></head>
<body>
  <h2>Mostrar usuario</h2>
  <?php if (!$found): ?>
    <p>Usuario no encontrado.</p>
  <?php else: ?>
    <p>Nombre: <?=htmlspecialchars($fullname)?></p>
    <p>NAN: <?=htmlspecialchars($dni)?></p>
    <p>Teléfono: <?=htmlspecialchars($phone)?></p>
    <p>Fecha nacimiento: <?=htmlspecialchars($birthdate)?></p>
    <p>Email: <?=htmlspecialchars($email)?></p>
    <p>Usuario: <?=htmlspecialchars($username)?></p>
    <?php if (isset($_SESSION['username']) && $_SESSION['username']===$username): ?>
      <a href="/src/modify_user.php?user=<?=urlencode($username)?>">Modificar mis datos</a>
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>
