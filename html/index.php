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
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid JOIN FOLLOWS ON FOLLOWS.follower_userid = USERS.userid WHERE date IS NOT NULL AND followed_userid = :userid UNION SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid JOIN FOLLOWS ON FOLLOWS.follower_userid = USERS.userid WHERE date IS NOT NULL AND userid = :userid ORDER BY date DESC";
        $sth = $db->prepare($sql);
        $sth->bindParam(":userid", $username);
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
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST") {
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
           <?php if (
               isset($_SESSION["username"]) &&
               $_SESSION["username"] != "GUEST"
           ): ?>
            <form action="" method="post" name="post">
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
              <div class="post_user_icon_block">
                <img src="/actions/image.php?id=<?echo h($user['userid']);?>" id="post_comment_user_icon" />
              </div>
              <textarea class="form-control" name="content" id="content"  required></textarea>
              <input
                class="post_button"
                id="post_button"
                type="submit"
                value="投稿"
                disabled
              />
            </form>
            <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
    <?php for ($i = 0; $i < count($posts); $i++): ?>
      <div class="posts_container">
      <?php if (
          isset($_SESSION["username"]) &&
          $_SESSION["username"] != "GUEST" &&
          $posts[$i]["userid"] == $_SESSION["username"]
      ): ?>
      <form id="delete" action="actions/delete.php" method="post">
        <a id="post_delete" data-toggle="modal" data-target="#exampleModal" ><i class="fa fa-times" aria-hidden="true"></i></a>
        <input
            type="hidden"
            name="postid"
            value="<?echo $posts[$i]["postid"]?>"
        />
      </form>
      <?php endif; ?>
          <div class="post_user_box">
                <a class ="profile_link" href="/profile/?id=<?echo $posts[$i]["userid"]?>">
                  <div class="post_user_icon_block">
                    <img src="/actions/image.php?id=<?echo h($posts[$i]["userid"]);?>" id="post_user_icon" />
                  </div>
                  </a>
                <a class ="profile_link" href="/profile/?id=<?echo $posts[$i]["userid"]?>">
                  <div class="post_user_name_block">
                    <p name="post_user_name" id="post_user_name"><?echo h($posts[$i]["name"]);?></p>
                    <p name="post_user_id" id="post_user_id">@<?echo h($posts[$i]["userid"]);?></p>
                  </div>
                  </a>
                <?php if (
                    isset($_SESSION["username"]) &&
                    $_SESSION["username"] != "GUEST" &&
                    $posts[$i]["userid"] != $_SESSION["username"]
                ): ?>
<form class="post_user_followbtn_block" action="/actions/follow.php" method="post">
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
              <input type="hidden" name="followid" value=<?echo $posts[$i]['userid']?>>
              <?php if (
                  isFollowed($db, $posts[$i]["userid"], $_SESSION["username"])
              ): ?>
                <input
                      class="post_followed_button"
                      id="post_followed_button"
                      type="submit"
                      onMouseOver="this.value='フォロー解除';" onMouseOut="this.value='フォロー中';"
                      value="フォロー中"
                      />
              <?php else: ?>
                <input
                      class="post_follow_button"
                      id="post_follow_button"
                      type="submit"
                      value="フォローする"
                      />
              <?php endif; ?>
          </form>
                <?php endif; ?>
          </div>
          <div class="posts_body">
          <p><?echo nl2br($posts[$i]["content"]);?></p>
          </div>
          <div class="posts_footer">
          <p class="posts_date"><? echo  convert_to_fuzzy_time($posts[$i]["date"]);?></p>
          </div>
          <a class ="link" href="/p?id=<?echo $posts[$i]["postid"]?>"></a>
      </div>
    <?php endfor; ?>
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">投稿を削除しますか？</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <div class="modal-body">
        <p>この操作は取り消せません。プロフィール、あなたをフォローしているアカウントのタイムライン、Musheの検索結果から投稿が削除されます。 </p>
      </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
          <button type="submit" form="delete" class="btn btn-primary">削除する</button>
        </div>
      </div>
    </div>
  </div>
  </body>
</html>