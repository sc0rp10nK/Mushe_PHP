<?php
require_once "api/function.php";
$db = getDb();
require_logined_session();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //投稿取得
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST") {
        $username = $_SESSION["username"];
        // bindParamを利用したSQL文の実行
        $sql =
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND userid = :id ORDER BY date DESC;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $username);
        $sth->execute();
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql =
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL ORDER BY date DESC;";
        $sth = $db->query($sql);
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    //投稿する
    // セッション開始
    @session_start();
    // ユーザー情報取得
    if (isset($_SESSION["username"])) {
        $username = $_SESSION["username"];
        // bindParamを利用したSQL文の実行
        $sql = "SELECT * FROM USERS WHERE userid = :id;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $username);
        $sth->execute();
        $user = $sth->fetch();
        if (
            isset($_POST["content"]) &&
            validate_token(filter_input(INPUT_POST, "token"))
        ) {
            $content = $_POST["content"];
            $date = date("Y/m/d H:i:s");
            $sql = 'INSERT INTO POSTS
(content, add_date, date, post_userid) VALUES (:content, :add_date, :date, :post_userid)';
            $prepare = $db->prepare($sql);
            $prepare->bindValue(":content", $content, PDO::PARAM_STR);
            $prepare->bindValue(":add_date", $date);
            $prepare->bindValue(":date", $date);
            $prepare->bindValue(":post_userid", $user["userid"]);
            $prepare->execute();
            $uri = $_SERVER["HTTP_REFERER"];
            header("Location: " . $uri);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  define("title", "Mushe");
  include "global_menu.php";
  ?>
 <body>
 <?php if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST"): ?>
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
  <?php endif; ?>
    <?php for ($i = 0; $i < count($posts); $i++): ?>
      <div class="posts_container">
          <div class="post_user_box">
                <div class="post_user_icon_block">
                  <img src="/actions/image.php?id=<?echo h($posts[$i]["userid"]);?>" id="post_user_icon" />
                </div>
                <div class="post_user_name_block">
                  <p name="post_user_name" id="post_user_name"><?echo h($posts[$i]["name"]);?></p>
                  <p name="post_user_id" id="post_user_id">@<?echo h($posts[$i]["userid"]);?></p>
                </div>
                <?php if (
                    isset($_SESSION["username"]) &&
                    $_SESSION["username"] != "GUEST" &&
                    $posts[$i]["userid"] != $_SESSION["username"]
                ): ?>
                <div class="post_user_followbtn_block">
                  <input
                    class="post_follow_button"
                    id="post_follow_button"
                    type="button"
                    value="フォローする"
                  />
                </div>
                <?php endif; ?>
          </div>
          <div class="posts_body">
          <p><?echo $posts[$i]["content"];?></p>
          </div>
          <div class="posts_footer">
          <p class="posts_date"><? echo  convert_to_fuzzy_time($posts[$i]["date"]);?></p>
          </div>
          <a class ="link" href="/p?id=<?echo $posts[$i]["postid"]?>"></a>
      </div>
    <?php endfor; ?>
  </body>
</html>