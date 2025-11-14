<?php

require_once __DIR__.'/db.php';

$errors = [];
$fullname = $dni = $phone = $birthdate_input = $email = $username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate_request()) {
        http_response_code(400);
        $errors[] = 'CSRF token invalid.';
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $dni = strtoupper(trim($_POST['dni'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $birthdate_input = trim($_POST['birthdate'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        
        if (!preg_match('/^[\\p{L}\\s]+$/u', $fullname)) $errors[] = 'Izen eta abizenak testuzkoa izan behar dira.';
        if (!preg_match('/^[0-9]{8}-[A-Z]$/', $dni)) $errors[] = 'NAN formatua okerra da.';
        if (!preg_match('/^[0-9]{9}$/', $phone)) $errors[] = 'Telefono formatu okerra.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email baliogabea.';
        if (strlen($username) < 3) $errors[] = 'Erabiltzaile izena laburra da.';
        if (strlen($password) < 6) $errors[] = 'Pasahitza laburra da.';

        // Jaiotze data parseatu eta balidatu
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
                   $errors[] = 'Data formatu okerra. Erabili aaaa-mm-dd edo dd-mm-aaaa.';
               }
            }
        } else {
            $errors[] = 'Jaiotze data beharrezkoa da.';
        }

        if (empty($errors)) {
            // Pasahitzaren hash seguru bat sortu
            $pw_hash = password_hash($password, PASSWORD_DEFAULT);
            // Eremu sentsibleak zifratu
            try {
                $dni_enc = encrypt_field($dni);
                $phone_enc = encrypt_field($phone);
                $email_enc = encrypt_field($email);
            } catch (Exception $e) {
                $errors[] = 'Zifratze errorea: '.$e->getMessage();
            }

            if (empty($errors)) {
                $stmt = $mysqli->prepare("INSERT INTO users (fullname, dni, phone, birthdate, email, username, password, is_admin) VALUES (?,?,?,?,?,?,?, 0)");
                if (!$stmt) {
                    $errors[] = 'Prepare errorea: '.$mysqli->error;
                } else {
                    $stmt->bind_param('sssssss', $fullname, $dni_enc, $phone_enc, $birthdate, $email_enc, $username, $pw_hash);
                    if ($stmt->execute()) {
                        header('Location: /login');
                        exit;
                    } else {
                        $errors[] = 'Erregistro errorea: '.$stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
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
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e, ENT_QUOTES, 'UTF-8')?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form id="register_form" method="post" action="" onsubmit="return validateRegisterForm();">
    <?= csrf_token_input() ?>
    <label>Izen eta abizenak <input name="fullname" required value="<?=htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>NAN (11111111-Z): <input name="dni" required value="<?=htmlspecialchars($dni, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>Telefonoa: <input name="phone" required value="<?=htmlspecialchars($phone, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label> Jaiotze data (aaaa-mm-dd o dd-mm-aaaa): <input name="birthdate" required placeholder="aaaa-mm-dd" value="<?=htmlspecialchars($birthdate_input, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>Email: <input name="email" required value="<?=htmlspecialchars($email, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>Eabiltzailea: <input name="username" required value="<?=htmlspecialchars($username, ENT_QUOTES, 'UTF-8')?>"></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="register_submit" type="submit">Erregistratu</button>
  </form>
  <a href="/" class="back-btn">Hasierara bueltatu</a>
</body>
</html>   
