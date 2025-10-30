<?php
// Home: localhost:81/
// Actualizado para usar rutas "limpias" (/register, /login, /items, /add_item)
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
    <p>Bienvenido, <?=htmlspecialchars($_SESSION['username'])?> | <a href="/logout">Salir</a></p>
    <nav>
      <a href="/show_user?user=<?=urlencode($_SESSION['username'])?>">Mi perfil</a> |
      <a href="/items">Items</a> |
      <a href="/add_item">Añadir Item</a>
    </nav>
  <?php else: ?>
    <nav>
      <a href="/register.php">Registro</a> |
      <a href="/login.php">Login</a> |
      <a href="/items.php">Items (no identificado)</a>
    </nav>
  <?php endif; ?>
</body>
</html>
