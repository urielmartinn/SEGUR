<?php
// Erabiltzailea erakutsi: localhost:81/show_user?user={x}
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
<head><meta charset="utf-8"><title>Erabiltzailea erakutsi</title></head>
<body>
  <h2>Erabiltzailea erakutsi</h2>
  <?php if (!$found): ?>
    <p>Erabiltzailea ez da aurkitu</p>
  <?php else: ?>
    <p>Izena: <?=htmlspecialchars($fullname)?></p>
    <p>NAN: <?=htmlspecialchars($dni)?></p>
    <p>Telefonoa: <?=htmlspecialchars($phone)?></p>
    <p>Jaiotze da: <?=htmlspecialchars($birthdate)?></p>
    <p>Email: <?=htmlspecialchars($email)?></p>
    <p>Erabiltzailea: <?=htmlspecialchars($username)?></p>
    <?php if (isset($_SESSION['username']) && $_SESSION['username']===$username): ?>
      <a href="/modify_user?user=<?=urlencode($username)?>">Nire datuak aldatu</a>
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>
