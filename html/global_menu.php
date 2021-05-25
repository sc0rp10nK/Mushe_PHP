<?php
@session_start();
require_once "api/function.php";
$db = getDb();
if (isset($_SESSION["username"])) {
    //ログインユーザーの情報取得
    $user = getLoginUser($db);
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js" integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA==" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" integrity="sha512-DIW4FkYTOxjCqRt7oS9BFO+nVOwDL4bzukDyDtMO7crjUZhwpyrWBFroq+IqRe6VnJkTpRAS6nhDvf0w+wHmxg==" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css" integrity="sha512-O03ntXoVqaGUTAeAmvQ2YSzkCvclZEcPQu1eqloPaHfJ5RuNGiS4l+3duaidD801P50J28EHyonCV06CUlTSag==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.js" integrity="sha512-OmBbzhZ6lgh87tQFDVBHtwfi6MS9raGmNvUNTjDIBb/cgv707v9OuBVpsN6tVVTLOehRFns+o14Nd0/If0lE/A==" crossorigin="anonymous"></script>
<link rel="stylesheet" href="<?php echo path; ?>/style.css">
<script src="script.js"></script>
<link rel="stylesheet" href="/menu_style.css">
  <title><?php echo title; ?></title>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/">Mushe</a>
      <?php if (isset($_SESSION["username"])): ?>
      <form id="form" action="/search" method="get">
        <input id="sbox"  id="q" name="q" type="text" placeholder="キーワード検索"/>
        <button type="submit" id="sbtn"><i class="fa fa-search"></i></button>
      </form>
      <?php endif; ?>
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
        <a class="dropdown-item" href="/<?echo $_SESSION["username"]?>"><i class="fa fa-user-o"></i> プロフィール</a>
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