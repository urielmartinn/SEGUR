<?php
// Modificar usuario: localhost:81/modify_user?user={x}
// id formulario: user_modify_form
// id boton: user_modify_submit
session_start();
require_once __DIR__.'/db.php';

if (!isset($_GET['user'])) {
    http_response_code(400);
    die('Usuario no especificado.');
}

$user = $_GET['user'];

// Sólo permitimos modificar si está identificado y coincide
if (!isset($_SESSION['username']) || $_SESSION['username'] !== $user) {
    http_response_code(403);
    die('No autorizado. Debes estar identificado y ser el usuario.');
}

$errors = [];

// Obtener datos actuales del usuario
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email FROM users WHERE username=?");
if (!$stmt) {
    die('Error en consulta: '.$mysqli->error);
}
$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->bind_result($fullname, $dni, $phone, $birthdate, $email);
if (!$stmt->fetch()) {
    die('Usuario no encontrado.');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname_new = trim($_POST['fullname'] ?? '');
    $dni_new = strtoupper(trim($_POST['dni'] ?? ''));
    $phone_new = trim($_POST['phone'] ?? '');
    $birthdate_input = trim($_POST['birthdate'] ?? '');
    $email_new = trim($_POST['email'] ?? '');

    // Revalidar campos básicos
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname_new)) $errors[] = 'Nombre inválido.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni_new) || !check_nif($dni_new)) $errors[] = 'NAN inválido.';
    if (!preg_match('/^[0-9]{9}$/', $phone_new)) $errors[] = 'Teléfono inválido.';
    if (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    // Fecha: validar y normalizar a YYYY-MM-DD usando DateTime (acepta YYYY-MM-DD y DD-MM-YYYY)
    $birthdate_new = null;
    if ($birthdate_input !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $birthdate_input);
        $errs = DateTime::getLastErrors();
        if ($d && $errs['warning_count'] === 0 && $errs['error_count'] === 0) {
            $birthdate_new = $d->format('Y-m-d');
        } else {
            $d2 = DateTime::createFromFormat('d-m-Y', $birthdate_input);
            $errs2 = DateTime::getLastErrors();
            if ($d2 && $errs2['warning_count'] === 0 && $errs2['error_count'] === 0) {
                $birthdate_new = $d2->format('Y-m-d');
            } else {
                $errors[] = 'Fecha inválida. Usa aaaa-mm-dd o dd-mm-aaaa.';
            }
        }
    } else {
        $errors[] = 'Fecha de nacimiento requerida.';
    }

    if (empty($errors)) {
        $upd = $mysqli->prepare("UPDATE users SET fullname=?, dni=?, phone=?, birthdate=?, email=? WHERE username=?");
        if (!$upd) {
            $errors[] = 'Error al preparar actualización: '.$mysqli->error;
        } else {
            $upd->bind_param('ssssss', $fullname_new, $dni_new, $phone_new, $birthdate_new, $email_new, $user);
            if ($upd->execute()) {
                header('Location: /show_user?user=' . urlencode($user));
                exit;
            } else {
                $errors[] = 'Error al actualizar: '.$upd->error;
            }
            $upd->close();
        }
    }
}

function check_nif($dni) {
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
  <title>Modificar usuario</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Modificar usuario</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach ($errors as $e): ?>
        <li><?=htmlspecialchars($e)?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form id="user_modify_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Nombre y apellidos: <input name="fullname" value="<?=htmlspecialchars($fullname)?>" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" value="<?=htmlspecialchars($dni)?>" required></label><br>
    <label>Teléfono: <input name="phone" value="<?=htmlspecialchars($phone)?>" required></label><br>
    <label>Fecha nacimiento (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" value="<?=htmlspecialchars($birthdate)?>" required placeholder="aaaa-mm-dd"></label><br>
    <label>Email: <input name="email" value="<?=htmlspecialchars($email)?>" required></label><br>
    <button id="user_modify_submit" type="submit">Guardar</button>
  </form>
</body>
</html>
