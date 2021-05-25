<?php
@session_start();
require_once "../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$session = new SpotifyWebAPI\Session(
    $_ENV["ClientID"],
    $_ENV["ClientSecret"],
    "http://localhost:8080/actions/callback.php"
);
$api = new SpotifyWebAPI\SpotifyWebAPI([
    "auto_refresh" => true,
    "auto_retry" => true,
]);
if (isset($_GET["code"])) {
    $session->requestAccessToken($_GET["code"]);
    $api->setAccessToken($session->getAccessToken());
    $_SESSION["access"] = $session->getAccessToken();
    $_SESSION["refresh"] = $session->getRefreshToken();
    header("Location: /");
} else {
    $state = $session->generateState();
    $options = [
        "scope" => [
            "user-read-currently-playing",
            "user-read-playback-state",
            "streaming",
        ],
    ];

    header("Location: " . $session->getAuthorizeUrl($options));
    die();
}
?>
