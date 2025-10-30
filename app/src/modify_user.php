<?php
// Modificar usuario: localhost:81/modify_user?user={x}
// id formulario: user_modify_form
// id boton: user_modify_submit
session_start();
require_once __DIR__.'/db.php';
if (!isset($_GET['user'])) die('Usuario no especificado.');
$user = $_GET['user'];
// Sólo permitimos modificar si está identificado y coincide
$allowed = (isset($_SESSION['username']) && $_SESSION['username'] === $user);
if (!$allowed) die('No autorizado. Debes estar identificado y ser el usuario.');

$errors = [];
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email FROM users WHERE username=?");
$stmt->bind_param('s',$user);
$stmt->execute();
$stmt->bind_result($fullname,$dni,$phone,$birthdate,$email);
if (!$stmt->fetch()) die('Usuario no encontrado.');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fullname_new = trim($_POST['fullname'] ?? '');
    $dni_new = strtoupper(trim($_POST['dni'] ?? ''));
    $phone_new = trim($_POST['phone'] ?? '');
    $birthdate_new = trim($_POST['birthdate'] ?? '');
    $email_new = trim($_POST['email'] ?? '');

    // Revalidar
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname_new)) $errors[] = 'Nombre inválido.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni_new) || !check_nif($dni_new)) $errors[] = 'NAN inválido.';
    if (!preg_match('/^[0-9]{9}$/', $phone_new)) $errors[] = 'Teléfono inválido.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate_new)) $errors[] = 'Fecha inválida.';
    if (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    if (empty($errors)) {
        $u = $user;
        $upd = $mysqli->prepare("UPDATE users SET fullname=?, dni=?, phone=?, birthdate=?, email=? WHERE username=?");
        $upd->bind_param('ssssss', $fullname_new, $dni_new, $phone_new, $birthdate_new, $email_new, $u);
        if ($upd->execute()) {
            header('Location: /src/show_user.php?user='.$u);
            exit;
        } else $errors[] = 'Error al actualizar: '.$mysqli->error;
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
<head><meta charset="utf-8"><title>Modificar usuario</title><script src="/js/validation.js"></script></head>
<body>
  <h2>Modificar usuario</h2>
  <?php if ($errors): ?><ul style="color:red;"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul><?php endif; ?>
  <form id="user_modify_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Nombre y apellidos: <input name="fullname" value="<?=htmlspecialchars($fullname)?>" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" value="<?=htmlspecialchars($dni)?>" required></label><br>
    <label>Teléfono: <input name="phone" value="<?=htmlspecialchars($phone)?>" required></label><br>
    <label>Fecha nacimiento: <input name="birthdate" value="<?=htmlspecialchars($birthdate)?>" required></label><br>
    <label>Email: <input name="email" value="<?=htmlspecialchars($email)?>" required></label><br>
    <button id="user_modify_submit" type="submit">Guardar</button>
  </form>
</body>
</html>
