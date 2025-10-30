<?php
// Home: localhost:81/
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
    <p>Bienvenido, <?=htmlspecialchars($_SESSION['username'])?> | <a href="/src/logout.php">Salir</a></p>
    <nav>
      <a href="/src/show_user.php?user=<?=urlencode($_SESSION['username'])?>">Mi perfil</a> |
      <a href="/src/items.php">Items</a> |
      <a href="/src/add_item.php">Añadir Item</a>
    </nav>
  <?php else: ?>
    <nav>
      <a href="/src/register.php">Registro</a> |
      <a href="/src/login.php">Login</a> |
      <a href="/src/items.php">Items (no identificado)</a>
    </nav>
  <?php endif; ?>
</body>
</html>
