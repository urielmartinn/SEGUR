<?php

// MySQL konexioa (SSL aukerarekin)
$DB_HOST = getenv('DB_HOST') ? getenv('DB_HOST') : 'db';
$DB_USER = getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'appuser';
$DB_PASS = getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') : 'apppass';
$DB_NAME = getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'appdb';
$USE_SSL = getenv('DB_USE_SSL') === '1';

// mysqli hasieratu
$mysqli = mysqli_init();
if ($USE_SSL) {
    $ssl_key = getenv('DB_SSL_KEY') ?: null;
    $ssl_cert = getenv('DB_SSL_CERT') ?: null;
    $ssl_ca = getenv('DB_SSL_CA') ?: null;
    if ($ssl_key || $ssl_cert || $ssl_ca) {
        $mysqli->ssl_set($ssl_key, $ssl_cert, $ssl_ca, null, null);
    }
    $client_flags = MYSQLI_CLIENT_SSL;
} else {
    $client_flags = 0;
}

if (!$mysqli->real_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, ini_get("mysqli.default_port"), null, $client_flags)) {
    die('DB connect error: ' . mysqli_connect_error());
}
$mysqli->set_charset("utf8mb4");

// Saioen hasieratze segurua
// HTTPS aktibatuta badago, cookieak secure izango dira
if (php_sapi_name() !== 'cli') {
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || getenv('FORCE_HTTPS') === '1';

    // baieztatu sesioa irekita dagoen
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    } else {
    }
}

// libsodium instalatuta egon behar du aplikazioan, honek seguruago bihurtzen du guztia
if (!extension_loaded('sodium')) {
    throw new Exception('libsodium luzapena beharrezkoa da zifratze segururako (instalatu ext-sodium).');
}

function get_app_key(): string {
    $b64 = getenv('APP_ENC_KEY') ?: '';
    if ($b64 === '') {
        throw new Exception('APP_ENC_KEY inguruneko aldagaian definitu behar da');
    }
    $key = base64_decode($b64, true);
    if ($key === false || strlen($key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
        throw new Exception('APP_ENC_KEY baliogabea da (32 byte base64 izan behar du)');
    }
    return $key;
}

function encrypt_field(string $plaintext): string {
    $key = get_app_key();
    $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
    $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, '', $nonce, $key);
    return base64_encode($nonce . $cipher);
}

function decrypt_field(string $b64): ?string {
    $raw = base64_decode($b64, true);
    if ($raw === false || strlen($raw) < SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES) return null;
    $key = get_app_key();
    $nonce = substr($raw, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
    $cipher = substr($raw, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
    $plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, '', $nonce, $key);
    if ($plain === false) return null;
    return $plain;
}

//CSRF

function csrf_get_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if (php_sapi_name() !== 'cli') {
            session_start();
        }
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token_input(): string {
    $token = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function csrf_validate_request(): bool {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $posted = $_POST['csrf_token'] ?? '';
    if (!is_string($posted) || !is_string($sessionToken)) return false;
    return hash_equals($sessionToken, $posted);
}
?>
