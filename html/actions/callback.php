<?php
@session_start();
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$session = new SpotifyWebAPI\Session(
    $_ENV["ClientID"],
    $_ENV["ClientSecret"],
    'http://localhost:8080/actions/callback.php'
);

$session->requestAccessToken($_GET['code']);

$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();
$_SESSION['access'] = $accessToken;
$_SESSION['refresh'] = $refreshToken;
header('Location: ../index.php');
die();
?>