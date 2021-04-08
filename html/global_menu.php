<?php
@session_start();
require_once 'api/function.php';
$db = getDb();
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    // bindParamを利用したSQL文の実行
    $sql = 'SELECT * FROM USR WHERE userid = :id;';
    $sth = $db->prepare($sql);
    $sth->bindParam(':id', $username);
    $sth->execute();
    $user = $sth->fetch();
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
  <title><?php echo title; ?></title>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/">Mushe</a>
      <?php if (isset($_SESSION['username'])): ?>
      <div>
      <span class="text-primary"><?echo $user['name']?> / <a href="/account/logout">ログアウト</a></span>
      </div>
      <?php endif; ?>
    </div>
  </nav>
</head>