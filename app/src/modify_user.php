<?php
// Editatu erabiltzailea: localhost:81/modify_user?user={x}
// id formularioa: user_modify_form
// id botoia: user_modify_submit
session_start();
require_once __DIR__.'/db.php';

if (!isset($_GET['user'])) {
    http_response_code(400);
    die('Erabiltzailea ez da espezifikatu.');
}

$user = $_GET['user'];

// Identifikatuta badago eta bat badator bakarrik uzten dugu aldatzen
if (!isset($_SESSION['username']) || $_SESSION['username'] !== $user) {
    http_response_code(403);
    die('Baimenik gabea. Identifikatuta egon behar duzu eta erabiltzailea izan behar duzu.');
}

$errors = [];

// Erabiltzailearen uneko datuak lortzea
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email FROM users WHERE username=?");
if (!$stmt) {
    die('Kontsultan errorea: '.$mysqli->error);
}
$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->bind_result($fullname, $dni, $phone, $birthdate, $email);
if (!$stmt->fetch()) {
    die('Erabiltzailea ez da aurkitu.');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname_new = trim($_POST['fullname'] ?? '');
    $dni_new = strtoupper(trim($_POST['dni'] ?? ''));
    $phone_new = trim($_POST['phone'] ?? '');
    $birthdate_input = trim($_POST['birthdate'] ?? '');
    $email_new = trim($_POST['email'] ?? '');

    // Oinarrizko eremuak berretsi
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname_new)) $errors[] = 'Izena ez da baliozkoa.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni_new) || !check_nif($dni_new)) $errors[] = 'NAN ez da baliozkoa.';
    if (!preg_match('/^[0-9]{9}$/', $phone_new)) $errors[] = 'Teléfonoa ez du balio.';
    if (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) $errors[] = 'Emaila ez du balio.';

    // Data: baliozkotu eta normalizatu a YYYY-MM-DD usando DateTime (acepta YYYY-MM-DD y DD-MM-YYYY)
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
                $errors[] = 'Fecha ez du balio. Erabili aaaa-mm-dd o dd-mm-aaaa.';
            }
        }
    } else {
        $errors[] = 'Jaiotze data behar da.';
    }

    if (empty($errors)) {
        $upd = $mysqli->prepare("UPDATE users SET fullname=?, dni=?, phone=?, birthdate=?, email=? WHERE username=?");
        if (!$upd) {
            $errors[] = 'Eguneraketa prestatzean errorea: '.$mysqli->error;
        } else {
            $upd->bind_param('ssssss', $fullname_new, $dni_new, $phone_new, $birthdate_new, $email_new, $user);
            if ($upd->execute()) {
                header('Location: /show_user?user=' . urlencode($user));
                exit;
            } else {
                $errors[] = 'Errorea eguneratzean: '.$upd->error;
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
  <title>Editatu erabiltzailea</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Editatu erabiltzailea</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach ($errors as $e): ?>
        <li><?=htmlspecialchars($e)?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form id="user_modify_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Izen abizenak: <input name="fullname" value="<?=htmlspecialchars($fullname)?>" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" value="<?=htmlspecialchars($dni)?>" required></label><br>
    <label>Teléfonoa: <input name="phone" value="<?=htmlspecialchars($phone)?>" required></label><br>
    <label>Jaiotze data (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" value="<?=htmlspecialchars($birthdate)?>" required placeholder="aaaa-mm-dd"></label><br>
    <label>Emaila: <input name="email" value="<?=htmlspecialchars($email)?>" required></label><br>
    <button id="user_modify_submit" type="submit">Gorde</button>
  </form>
</body>
</html>
