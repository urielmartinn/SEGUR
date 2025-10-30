<?php
// Registro: localhost:81/register
// id formulario: register_form
// id boton: register_submit
session_start();
require_once __DIR__.'/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar
    $fullname = trim($_POST['fullname'] ?? '');
    $dni = strtoupper(trim($_POST['dni'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones servidor
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname)) $errors[] = 'Nombre y apellidos sólo texto.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni) || !check_nif($dni)) $errors[] = 'NAN inválido.';
    if (!preg_match('/^[0-9]{9}$/', $phone)) $errors[] = 'Teléfono inválido.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) $errors[] = 'Fecha inválida. Formato uuuu-mm-dd.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
    if (strlen($username) < 3) $errors[] = 'Usuario demasiado corto.';
    if (strlen($password) < 6) $errors[] = 'Password demasiado corta.';

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO users (fullname, dni, phone, birthdate, email, username, password) VALUES (?,?,?,?,?,?,SHA2(?,256))");
        $stmt->bind_param('sssssss', $fullname, $dni, $phone, $birthdate, $email, $username, $password);
        if ($stmt->execute()) {
            header('Location: /src/login.php');
            exit;
        } else {
            $errors[] = 'Error al registrar: '.$mysqli->error;
        }
    }
}

function check_nif($dni) {
    // Formato 11111111-Z -> algoritmo estándar
    $map = "TRWAGMYFPDXBNJZSQVHLCKE";
    if (!preg_match('/^([0-9]{8})-([A-Z])$/', $dni, $m)) return false;
    $num = intval($m[1]);
    $letter = $m[2];
    $expected = $map[$num % 23];
    return $letter === $expected;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Registro</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Registro</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form id="register_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Nombre y apellidos: <input name="fullname" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" required></label><br>
    <label>Teléfono: <input name="phone" required></label><br>
    <label>Fecha nacimiento (aaaa-mm-dd): <input name="birthdate" required></label><br>
    <label>Email: <input name="email" required></label><br>
    <label>Usuario: <input name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="register_submit" type="submit">Registrar</button>
  </form>
</body>
</html>
