<?php

require_once __DIR__.'/db.php';

$user = $_GET['user'] ?? '';
$stmt = $mysqli->prepare("SELECT fullname,dni,phone,birthdate,email,username FROM users WHERE username=?");
$stmt->bind_param('s',$user);
$stmt->execute();
$stmt->bind_result($fullname,$dni_enc,$phone_enc,$birthdate,$email_enc,$username);
$found = $stmt->fetch();


function maybe_decrypt($val) {
    if ($val === null) return null;
    $decoded = base64_decode($val, true);
    if ($decoded === false) return $val;
    $plain = decrypt_field($val);
    return $plain === null ? $val : $plain;
}

$display_dni = $found ? maybe_decrypt($dni_enc) : null;
$display_phone = $found ? maybe_decrypt($phone_enc) : null;
$display_email = $found ? maybe_decrypt($email_enc) : null;
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Erabiltzailea erakutsi</title></head>
<body>
  <h2>Erabiltzailea erakutsi</h2>
  <?php if (!$found): ?>
    <p>Erabiltzailea ez da aurkitu</p>
  <?php else: ?>
    <p>Izena: <?=htmlspecialchars($fullname)?></p>
    <p>NAN: <?=htmlspecialchars($display_dni)?></p>
    <p>Telefonoa: <?=htmlspecialchars($display_phone)?></p>
    <p>Jaiotze data: <?=htmlspecialchars($birthdate)?></p>
    <p>Email: <?=htmlspecialchars($display_email)?></p>
    <p>Erabiltzailea: <?=htmlspecialchars($username)?></p>
    <?php if (isset($_SESSION['username']) && $_SESSION['username']===$username): ?>
      <a href="/modify_user?user=<?=urlencode($username)?>">Nire datuak aldatu</a>
    <?php endif; ?>
  <?php endif; ?>
  <a href="/" class="back-btn">Hasierara bueltatu</a>

</body>
</html> 
