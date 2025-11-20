<?php

require_once __DIR__.'/db.php';

if (!isset($_GET['user'])) {
    http_response_code(400);
    die('Erabiltzailea zehaztu behar da.');
}
$user = $_GET['user'];
if (!isset($_SESSION['username']) || $_SESSION['username'] !== $user) {
    http_response_code(403);
    die('Ez dago autorizatuta. Identifikatuta egon behar da eta erabiltzailea bera izan.');
}

$errors = [];
// Zifratu erabiltzaile datuak
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email FROM users WHERE username=?");
if (!$stmt) {
    die('Kontsulta errorea: '.$mysqli->error);
}
$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->bind_result($fullname, $dni_enc, $phone_enc, $birthdate, $email_enc);
if (!$stmt->fetch()) {
    die('Erabiltzailea ez da aurkitu.');
}
$stmt->close();

function maybe_decrypt($val) {
    if ($val === null) return null;
    $decoded = base64_decode($val, true);
    if ($decoded === false) return $val; 
    $plain = decrypt_field($val);
    return $plain === null ? $val : $plain;
}

$fullname = $fullname;
$dni = maybe_decrypt($dni_enc);
$phone = maybe_decrypt($phone_enc);
$email = maybe_decrypt($email_enc);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate_request()) {
        http_response_code(400);
        $errors[] = 'CSRF token invalid.';
    } else {
        $fullname_new = trim($_POST['fullname'] ?? '');
        $dni_new = strtoupper(trim($_POST['dni'] ?? ''));
        $phone_new = trim($_POST['phone'] ?? '');
        $birthdate_input = trim($_POST['birthdate'] ?? '');
        $email_new = trim($_POST['email'] ?? '');

        // Oinarrizko eremuak berriz baliozkotzea

        if (!preg_match('/^[\\p{L}\\s]+$/u', $fullname_new)) $errors[] = 'Izen okerra.';
        if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni_new) || !check_nif($dni_new)) $errors[] = 'NAN okerra.';
        if (!preg_match('/^[0-9]{9}$/', $phone_new)) $errors[] = 'Teléfono okerra.';
        if (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email okerra.';

        // Data: YYYYY-MM-DD baliozkotu eta normalizatu
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
                    $errors[] = 'Data Okerra. erabili aaaa-mm-dd o dd-mm-aaaa.';
                }
            }
        } else {
            $errors[] = 'Jaoitzen data behar da .';
        }

        if (empty($errors)) {
            $upd = $mysqli->prepare("UPDATE users SET fullname=?, dni=?, phone=?, birthdate=?, email=? WHERE username=?");
            if (!$upd) {
                $errors[] = 'Errorea aktualizazioa prestatzean: '.$mysqli->error;
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
  <title>Aldatu erabiltzailea</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Aldatu erabiltzailea</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach ($errors as $e): ?>
        <li><?=htmlspecialchars($e, ENT_QUOTES, 'UTF-8')?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form id="user_modify_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <?= csrf_token_input() ?>
    <label>Izen eta abizenak: <input name="fullname" value="<?=htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" value="<?=htmlspecialchars($dni, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>Teléfono: <input name="phone" value="<?=htmlspecialchars($phone, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <label>Jaiitze data (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" value="<?=htmlspecialchars($birthdate, ENT_QUOTES, 'UTF-8')?>" required placeholder="aaaa-mm-dd"></label><br>
    <label>Email: <input name="email" value="<?=htmlspecialchars($email, ENT_QUOTES, 'UTF-8')?>" required></label><br>
    <button id="user_modify_submit" type="submit">Gorde</button>
  </form>
</body>
</html> 
