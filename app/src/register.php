<?php
// Erregistroa: localhost:81/register
// id formularioa: register_form
// id botoia: register_submit
session_start();
require_once __DIR__.'/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bildu eta sanazioa
    $fullname = trim($_POST['fullname'] ?? '');
    $dni = strtoupper(trim($_POST['dni'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $birthdate_input = trim($_POST['birthdate'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Zerbitzariaren baliozkotzeak
    if (!preg_match('/^[A-Za-zÑñÁÉÍÓÚáéíóúü\\s]+$/u', $fullname)) $errors[] = 'Izen abizenak testua bakarrik.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni) || !check_nif($dni)) $errors[] = 'NAN ez da baliozkoa.';
    if (!preg_match('/^[0-9]{9}$/', $phone)) $errors[] = 'Teléfonoa ez da baliozkoa.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Emaila ez da baliozkoa.';
    if (strlen($username) < 3) $errors[] = 'Erabiltzailea oso motza.';
    if (strlen($password) < 6) $errors[] = 'Password os motza.';

    // Data: YYYYY-MM-DD baliozkotu eta normalizatu DateTime erabiliz (acepta YYYY-MM-DD y DD-MM-YYYY)
    $birthdate = null;
    if ($birthdate_input !== '') {
        // DateTime erabiliz formatu onartuetan parseatzen saiatu
        $d = DateTime::createFromFormat('Y-m-d', $birthdate_input);
        $errors_dt = DateTime::getLastErrors();
        if ($d && $errors_dt['warning_count'] === 0 && $errors_dt['error_count'] === 0) {
            $birthdate = $d->format('Y-m-d');
        } else {
          // Formatu alternatiboa saiatu dd-mm-YYYY
            $d2 = DateTime::createFromFormat('d-m-Y', $birthdate_input);
            $errors_dt2 = DateTime::getLastErrors();
            if ($d2 && $errors_dt2['warning_count'] === 0 && $errors_dt2['error_count'] === 0) {
                $birthdate = $d2->format('Y-m-d');
            } else {
                $errors[] = 'Data ez da baliozkoa. Erabili uuuu-hh-ee edo uuuu-hh-ee formatua.';
            }
        }
    } else {
        $errors[] = 'Jaiotza-data nahitaezkoa da.';
    }

    if (empty($errors)) {
        try {
            $stmt = $mysqli->prepare("INSERT INTO users (fullname, dni, phone, birthdate, email, username, password) VALUES (?,?,?,?,?,?,SHA2(?,256))");
            if (!$stmt) throw new Exception('Error prepare: '.$mysqli->error);
            $stmt->bind_param('sssssss', $fullname, $dni, $phone, $birthdate, $email, $username, $password);
            if ($stmt->execute()) {
               // Bide garbira bideratu /login
                header('Location: /login');
                exit;
            } else {
                $errors[] = 'Errorea erregistratzean: '.$stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = 'Excepción gehitzean: '.$e->getMessage();
        }
    }
}

function check_nif($dni) {
    // Formatua 11111111-Z -> algoritmo estandarra
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
  <title>Erregistroa</title>
  <script src="/js/validation.js"></script>
</head>
<body>
  <h2>Erregistroa</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form id="register_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Izen abizenak: <input name="fullname" required></label><br>
    <label>NAN (11111111-Z): <input name="dni" required></label><br>
    <label>Telefonoa: <input name="phone" required></label><br>
    <label>Jaiotze data (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" required placeholder="aaaa-mm-dd"></label><br>
    <label>Emaila: <input name="email" required></label><br>
    <label>Erabiltzailea: <input name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="register_submit" type="submit">Erregistratu</button>
  </form>
</body>
</html>
