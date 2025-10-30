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
    $birthdate_input = trim($_POST['birthdate'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones servidor
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname)) $errors[] = 'Nombre y apellidos sólo texto.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni) || !check_nif($dni)) $errors[] = 'NAN inválido.';
    if (!preg_match('/^[0-9]{9}$/', $phone)) $errors[] = 'Teléfono inválido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
    if (strlen($username) < 3) $errors[] = 'Usuario demasiado corto.';
    if (strlen($password) < 6) $errors[] = 'Password demasiado corta.';

    // Fecha: validar y normalizar a YYYY-MM-DD usando DateTime (acepta YYYY-MM-DD y DD-MM-YYYY)
    $birthdate = null;
    if ($birthdate_input !== '') {
        // Intentar parsear con DateTime en formatos aceptados
        $d = DateTime::createFromFormat('Y-m-d', $birthdate_input);
        $errors_dt = DateTime::getLastErrors();
        if ($d && $errors_dt['warning_count'] === 0 && $errors_dt['error_count'] === 0) {
            $birthdate = $d->format('Y-m-d');
        } else {
            // Intentar formato alternativo dd-mm-YYYY
            $d2 = DateTime::createFromFormat('d-m-Y', $birthdate_input);
            $errors_dt2 = DateTime::getLastErrors();
            if ($d2 && $errors_dt2['warning_count'] === 0 && $errors_dt2['error_count'] === 0) {
                $birthdate = $d2->format('Y-m-d');
            } else {
                $errors[] = 'Fecha inválida. Usa el formato aaaa-mm-dd o dd-mm-aaaa.';
            }
        }
    } else {
        $errors[] = 'Fecha de nacimiento requerida.';
    }

    if (empty($errors)) {
        try {
            $stmt = $mysqli->prepare("INSERT INTO users (fullname, dni, phone, birthdate, email, username, password) VALUES (?,?,?,?,?,?,SHA2(?,256))");
            if (!$stmt) throw new Exception('Error prepare: '.$mysqli->error);
            $stmt->bind_param('sssssss', $fullname, $dni, $phone, $birthdate, $email, $username, $password);
            if ($stmt->execute()) {
                // Redirigir a la ruta limpia /login
                header('Location: /login');
                exit;
            } else {
                $errors[] = 'Error al registrar: '.$stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = 'Excepción al insertar: '.$e->getMessage();
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
    <label>Fecha nacimiento (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" required placeholder="aaaa-mm-dd"></label><br>
    <label>Email: <input name="email" required></label><br>
    <label>Usuario: <input name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="register_submit" type="submit">Registrar</button>
  </form>
</body>
</html>
