<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    @session_start();
    $_SESSION['username'] = "GUEST";
    header('Location: /');
    exit();
}
?>