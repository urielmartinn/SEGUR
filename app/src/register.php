<?php
// Registroa: localhost:81/register
// id formularioa: register_form
// id botoia: register_submit
session_start();
require_once __DIR__.'/db.php';

$errors = [];

// Inizializatu balioak sticky bihurtuko ditugu
$fullname = $dni = $phone = $birthdate_input = $email = $username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bildu eta 
    $fullname = trim($_POST['fullname'] ?? '');
    $dni = strtoupper(trim($_POST['dni'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $birthdate_input = trim($_POST['birthdate'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Balioztaketak zerbitzaria
    if (!preg_match('/^[\p{L}\s]+$/u', $fullname)) $errors[] = 'Izen eta abizenak textua bakarrik.';
    if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni) || !check_nif($dni)) $errors[] = 'NAN okerra.';
    if (!preg_match('/^[0-9]{9}$/', $phone)) $errors[] = 'Telefono okerra.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email okerra.';
    if (strlen($username) < 3) $errors[] = 'Erabiltzailea motzegia da.';
    if (strlen($password) < 6) $errors[] = 'Password motzegia da.';

    // Data parsing...
    $birthdate = null;
    if ($birthdate_input !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $birthdate_input);
        $errors_dt = DateTime::getLastErrors();
        if ($d && $errors_dt['warning_count'] === 0 && $errors_dt['error_count'] === 0) {
            $birthdate = $d->format('Y-m-d');
        } else {
           $d2 = DateTime::createFromFormat('d-m-Y', $birthdate_input);
           $errors_dt2 = DateTime::getLastErrors();
           if ($d2 && $errors_dt2['warning_count'] === 0 && $errors_dt2['error_count'] === 0) {
               $birthdate = $d2->format('Y-m-d');
           } else {
               $errors[] = 'Fecha okera. Erabili formato hau aaaa-mm-dd o dd-mm-aaaa.';
           }
        }
    } else {
        $errors[] = 'Jaiotze data behar ';
    }

    if (empty($errors)) {
        try {
            
            $stmt = $mysqli->prepare("INSERT INTO users (fullname, dni, phone, birthdate, email, username, password, is_admin) VALUES (?,?,?,?,?,?,SHA2(?,256), 0)");
            if (!$stmt) throw new Exception('Error prepare: '.$mysqli->error);
            $stmt->bind_param('sssssss', $fullname, $dni, $phone, $birthdate, $email, $username, $password);
            if ($stmt->execute()) {
                // Berbiderapena
                header('Location: /login');
                exit;
            } else {
                $errors[] = 'Errorea erregistratzean: '.$stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = 'Excepction insertatzean: '.$e->getMessage();
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
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <h2>Erregistroa</h2>

  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Formulario con campos "sticky" (la contraseña NO se rellena por seguridad) -->
  <form id="register_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <label>Izen eta abizenak <input name="fullname" required value="<?=htmlspecialchars($fullname)?>"></label><br>
    <label>NAN (11111111-Z): <input name="dni" required value="<?=htmlspecialchars($dni)?>"></label><br>
    <label>Telefonoa: <input name="phone" required value="<?=htmlspecialchars($phone)?>"></label><br>
    <label> Jaiotze data (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" required placeholder="aaaa-mm-dd" value="<?=htmlspecialchars($birthdate_input)?>"></label><br>
    <label>Email: <input name="email" required value="<?=htmlspecialchars($email)?>"></label><br>
    <label>Eabiltzailea: <input name="username" required value="<?=htmlspecialchars($username)?>"></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="register_submit" type="submit">Erregistratu</button>
  </form>
  <a href="/" class="back-btn">Hasierara bueltatu</a>
</body>
</html> 
