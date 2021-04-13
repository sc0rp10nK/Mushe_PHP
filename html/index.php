<?php
require_once 'api/function.php';
$db = getDb();
require_logined_session();
if ($_SERVER['REQUEST_METHOD'] == 'GET') { //投稿取得
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') { //投稿する
    $db = getDb();
    // セッション開始
    @session_start();
    // ユーザー情報取得
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        // bindParamを利用したSQL文の実行
        $sql      = 'SELECT * FROM USERS WHERE userid = :id;';
        $sth      = $db->prepare($sql);
        $sth->bindParam(':id', $username);
        $sth->execute();
        $user = $sth->fetch();
        if (isset($_POST['content']) && validate_token(filter_input(INPUT_POST, 'token'))) {
            $content = $_POST['content'];
            $date    = date('Y-m-d H:i:s');
            $sql     = 'INSERT INTO POSTS
(content, add_date, date, userid) VALUES (:content, :add_date, :date, :userid)';
            $prepare = $db->prepare($sql);
            $prepare->bindValue(':content', $content, PDO::PARAM_STR);
            $prepare->bindValue(':add_date', $date);
            $prepare->bindValue(':date', $date);
            $prepare->bindValue(':userid', $user['userid']);
            $prepare->execute();
            $uri = $_SERVER['HTTP_REFERER'];
            header('Location: ' . $uri);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
define('title', 'Mushe');
include 'global_menu.php';
?>
 <body>
    <div class="post_container">
      <div id="post_box">
        <form action="" method="post">
          <h3 class="text-center">POST</h3>
          <input
            type="hidden"
            name="token"
            value="<?= h(generate_token()) ?>"
          />
          <div class="input-group">
            <input class="form-control" name="content" id="content" required />
            <span class="input-group-btn">
              <button
                type="submit"
                class="btn btn-primary btn-lg btn-block"
                id="post_btn"
              >
                <i class="fa fa-paper-plane"></i>
              </button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>