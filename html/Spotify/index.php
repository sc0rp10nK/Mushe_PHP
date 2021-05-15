<?php
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$session = new SpotifyWebAPI\Session(
    $_ENV["ClientID"],
    $_ENV["ClientSecret"],
    'http://localhost:8080/actions/callback.php'
);

$state = $session->generateState();
$options = [
    'scope' => [
        'user-read-currently-playing',
        'user-read-playback-state',
        'streaming'
    ],
];

header('Location: ' . $session->getAuthorizeUrl($options));
die();
?>