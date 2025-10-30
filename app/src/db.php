<?php
// db.php - conexión a la base de datos
// Comentarios: se utiliza mysqli, credenciales desde docker-compose env si se desea
$DB_HOST = getenv('DB_HOST') ? getenv('DB_HOST') : 'db';
$DB_USER = getenv('MYSQL_USER') ? getenv('MYSQL_USER') : 'appuser';
$DB_PASS = getenv('MYSQL_PASSWORD') ? getenv('MYSQL_PASSWORD') : 'apppass';
$DB_NAME = getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') : 'appdb';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) {
    die('DB connect error: ' . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
?>
