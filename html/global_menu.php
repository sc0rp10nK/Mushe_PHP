<?php
@session_start();
require_once "api/function.php";
$db = getDb();
if (isset($_SESSION["username"])) {
    //ログインユーザーの情報取得
    $user = getUser($db);
}
?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fork-awesome@1.1.7/css/fork-awesome.min.css" integrity="sha256-gsmEoJAws/Kd3CjuOQzLie5Q3yshhvmo7YNtBG7aaEY=" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="/menu_style.css">
  <title><?php echo title; ?></title>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/">Mushe</a>
      <?php if (
          isset($_SESSION["username"]) &&
          $_SESSION["username"] != "GUEST"
      ): ?>
      <ul class="navbar-nav ">
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img src="/actions/image.php?id=<?echo h($user["userid"]);?>" class="avatar" alt="Avatar">
        </a>
        <!-- この下の行に dropdown-menu-right を追加するだけ。 -->
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
        <a class="dropdown-item" href="#"><i class="fa fa-user-o"></i> プロフィール</a>
        <a class="dropdown-item" href="/account/edit"><i class="fa fa-cog" aria-hidden="true"></i> プロフィール編集</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="/account/logout"><i class="fa fa-sign-out" aria-hidden="true"></i> ログアウト</a>
        </div>
      </li>
      </ul>
      <?php elseif ($_SESSION["username"] === "GUEST"): ?>
        <span class="text-primary"><a href="/account/login">ログイン</a></span>
      <?php endif; ?>
    </div>
  </nav>
</head>