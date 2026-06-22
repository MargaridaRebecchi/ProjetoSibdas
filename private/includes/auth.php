<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_utilizador'])) {
    header("Location: ../public/login.php");
    exit;
}