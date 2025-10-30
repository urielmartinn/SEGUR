<?php
// Home: localhost:81/
// Eguneratua ibilbide "garbiak" erabiltzeko (/register, /login, /items, /add_item)
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Web Sistema - Home</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <h1>Web Sistema - Home</h1>
  <?php if (isset($_SESSION['username'])): ?>
    <p>Bienvenido, <?=htmlspecialchars($_SESSION['username'])?> | <a href="/logout">Atera</a></p>
    <nav>
      <a href="/show_user?user=<?=urlencode($_SESSION['username'])?>">Nire profila</a> |
      <a href="/items">Diskak</a> |
      <a href="/add_item">Gehitu Diska</a>
    </nav>
  <?php else: ?>
    <nav>
      <a href="/register.php">Erregistroa</a> |
      <a href="/login.php">Login</a> |
      <a href="/items.php">Diskak </a>
    </nav>
  <?php endif; ?>
</body>
</html>
