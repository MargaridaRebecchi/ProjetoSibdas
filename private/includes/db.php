<?php

require_once __DIR__ . '/../../config/config.php';

$conn = new mysqli(
    MYSQL_HOST,
    MYSQL_USERNAME,
    MYSQL_PASSWORD,
    MYSQL_DATABASE
);

if ($conn->connect_error) {
    die("Erro na ligação à BD: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>