<?php

require_once __DIR__ . '/db.php';
error_log('DEBUG_CSP_FORZADA');
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
    <p>Ongi etorri, <?=htmlspecialchars($_SESSION['username'])?> | <a href="/logout">Atera</a></p>
    <nav>
      <a href="/show_user?user=<?=urlencode($_SESSION['username'])?>">Nire profila</a> |
      <a href="/items">Diskak</a>
      <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        | <a href="/add_item">Diska berria jarri</a>
      <?php endif; ?>
    </nav>
  <?php else: ?>
    <nav>
      <a href="/register.php">Erregistratu</a> |
      <a href="/login.php">Login</a> |
      <a href="/items.php">Diskak</a>
    </nav>
  <?php endif; ?>
</body>
</html> 
