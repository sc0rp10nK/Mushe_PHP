<?php
session_start();
$_SESSION = [];
session_destroy();
?>

<!DOCTYPE html>
<style>
.mx-auto{
  height:60px;
  display: grid;
  place-items: center;
}
</style>
<html>
  <?php
  define('title', 'ログアウト');
  include '../../global_menu.php';
  ?>
  <body>
      <div class="mx-auto" style="height:100vh;">
      <div>
      <h1>ログアウトしました</h1>
        <p><a href="/">ログインページに戻る</a></p>
      </div>
      </div>
  </body>
</html>