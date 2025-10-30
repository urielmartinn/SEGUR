<?php
// Login: localhost:81/login
// id formularioa: login_form
// id botoia: login_submit
session_start();
require_once __DIR__.'/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT username, password FROM users WHERE username=?");
    $stmt->bind_param('s',$username);
    $stmt->execute();
    $stmt->bind_result($u, $hash);
    if ($stmt->fetch()) {
        // Konparatu SHA2
        if (hash('sha256', $password) === $hash) {
            $_SESSION['username'] = $u;
            header('Location: /');
            exit;
        } else $error = 'Kredentzialak ez dira baliozkoak .';
    } else $error = 'Erabiltzailea ez da aurkitu.';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>
  <?php if ($error) echo '<p style="color:red">'.$error.'</p>'; ?>
  <form id="login_form" method="post" action="">
    <label>Erabiltzailea: <input name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="login_submit" type="submit">Sartu</button>
  </form>
</body>
</html>
