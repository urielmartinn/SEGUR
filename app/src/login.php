<?php

require_once __DIR__ . '/db.php';

// Sesiioa hasi soilik ez baldin badago
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} else {
    if (session_id() === '') {
        session_start();
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate_request()) {
        http_response_code(400);
        $error = 'CSRF token invalid.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $mysqli->prepare("SELECT username, password, is_admin FROM users WHERE username=?");
        if ($stmt) {
            $stmt->bind_param('s', $username);
            if (! $stmt->execute()) {
                $error = 'Error interno de base de datos.';
                $stmt->close();
            } else {
                $stmt->store_result(); 
                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($u, $hash, $is_admin);
                    $stmt->fetch();
                    $stmt->close(); 

                    // Legacy SHA256 detektatu eta aldatu
                    if (preg_match('/^[0-9a-f]{64}$/i', $hash)) {
                        if (hash('sha256', $password) === $hash) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $upd = $mysqli->prepare("UPDATE users SET password=? WHERE username=?");
                            if ($upd) {
                                $upd->bind_param('ss', $newHash, $username);
                                $upd->execute();
                                $upd->close();
                            }
                            $_SESSION['username'] = $u;
                            $_SESSION['is_admin'] = (int)$is_admin;
                            header('Location: /');
                            exit;
                        } else {
                            $error = 'Kredentzialak ez dute balio.';
                        }
                    } else {
                        if (password_verify($password, $hash)) {
                            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                                $newHash = password_hash($password, PASSWORD_DEFAULT);
                                $upd = $mysqli->prepare("UPDATE users SET password=? WHERE username=?");
                                if ($upd) {
                                    $upd->bind_param('ss', $newHash, $username);
                                    $upd->execute();
                                    $upd->close();
                                }
                            }
                            $_SESSION['username'] = $u;
                            $_SESSION['is_admin'] = (int)$is_admin;
                            header('Location: /');
                            exit;
                        } else {
                            $error = 'Kredentzialak ez dute balio.';
                        }
                    }
                } else {
                    $error = 'Erabiltzailea ez da aurkitu.';
                    $stmt->close();
                }
            }
        } else {
            $error = 'Error interno de base de datos.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>
  <?php if ($error) echo '<p style="color:red">'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</p>'; ?>
  <form id="login_form" method="post" action="">
    <?= csrf_token_input() ?>
    <label>Erabiltzaile: <input name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button id="login_submit" type="submit">Sartu</button>
    <a href="/" class="back-btn">Hasierara bueltatu</a>
  </form>
</body>
</html> 
