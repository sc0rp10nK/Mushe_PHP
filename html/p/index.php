<?php
// セッション開始
@session_start();
require_once "../api/function.php";
require "../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = getDb();
$postid = h($_GET["id"]);
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //投稿情報取得
    $sql =
        "SELECT COUNT(*) AS cnt FROM POSTS WHERE date IS NOT NULL AND postid = :id;";
    $sth = $db->prepare($sql);
    $sth->bindParam(":id", $postid);
    $sth->execute();
    $result = $sth->fetch();
    if ($result["cnt"] > 0) {
        $sql =
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND postid = :id;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $postid);
        $sth->execute();
        $posts = $sth->fetch();
        if (isset($posts["music_id"])) {
            try {
                if (isset($_SESSION["access"])) {
                    $api = new SpotifyWebAPI\SpotifyWebAPI();
                    $api->setAccessToken($_SESSION["access"]);
                } else {
                    header("Location: /actions/callback.php");
                }
                $track = $api->getTrack($posts["music_id"]);
            } catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
                print $e->getcode();
                if ($e->hasExpiredToken()) {
                    $session = new SpotifyWebAPI\Session(
                        $_ENV["ClientID"],
                        $_ENV["ClientSecret"],
                        "http://localhost/actions/callback.php"
                    );
                    $session->refreshAccessToken($_SESSION["refresh"]);
                    $_SESSION["access"] = $session->getAccessToken();
                    $_SESSION["refresh"] = $session->getRefreshToken();
                    $api = new SpotifyWebAPI\SpotifyWebAPI();
                    $api->setAccessToken($_SESSION["access"]);
                    $track = $api->getTrack($posts["music_id"]);
                }
            }
        }
        define("title", "Mushe");
        define("path", "/p");
        include "../global_menu.php";
    } else {
        http_response_code(404);
        include "../error/404.php";
        exit();
    }
    //コメント取得
    $sql =
        "SELECT COUNT(*) AS cnt FROM COMMENTS WHERE date IS NOT NULL AND postid = :id;";
    $sth = $db->prepare($sql);
    $sth->bindParam(":id", $postid);
    $sth->execute();
    $result = $sth->fetch();
    if ($result["cnt"] > 0) {
        $sql =
            "SELECT * FROM COMMENTS JOIN USERS ON COMMENTS.userid = USERS.userid WHERE date IS NOT NULL AND COMMENTS.postid = :id;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $postid);
        $sth->execute();
        $comments = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST") {
        //コメントを投稿
        // ユーザー情報取得
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
            $sql = 'INSERT INTO COMMENTS
  (content, add_date, date,postid, userid) VALUES (:content, :add_date, :date,:postid, :userid)';
            $prepare = $db->prepare($sql);
            $prepare->bindValue(":content", $content, PDO::PARAM_STR);
            $prepare->bindValue(":add_date", $date);
            $prepare->bindValue(":date", $date);
            $prepare->bindValue(":postid", $postid);
            $prepare->bindValue(":userid", $user["userid"]);
            $prepare->execute();
            $uri = $_SERVER["HTTP_REFERER"];
            header("Location: " . $uri);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <body>
  <div class="main">
      <div class="content">
        <div class="main_box">
          <div class="post_user_box">
          <a class ="profile_link" href="/<?echo h($posts["userid"]);?>">
            <div class="post_user_icon_block">
              <img src="/actions/image.php?id=<?echo h($posts["userid"]);?>" id="post_user_icon" />
            </div>
            </a>
            <a class ="profile_link" href="/<?echo h($posts["userid"]);?>">
            <div class="post_user_name_block">
              <p name="post_user_name" id="post_user_name"><?echo h($posts["name"])?></p>
            </div>
            </a>
            <?php if (
                isset($_SESSION["username"]) &&
                $_SESSION["username"] != "GUEST" &&
                $posts["userid"] != $_SESSION["username"]
            ): ?>
        <form class="post_user_followbtn_block" action="../actions/follow.php" method="post">
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
              <input type="hidden" name="followid" value=<?echo h($posts["userid"]);?>>
              <?php if (
                  isFollowed($db, $posts["userid"], $_SESSION["username"])
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
          <div class="post_img_box">
          <?php if (isset($posts["music_id"])): ?>
              <img src="<?= $track->album->images[0]->url ?>" id="post_img" />
              <a href="<?= $track->external_urls->spotify ?>" target="_blank">
                <div class="mask">
                  <p class="caption">Open Spotify</p>
                </div>
              </a>
          <?php endif; ?>
          </div>
          <div class="footer">
            <div class="post_footer_menu">
              <div class="posts_date_box">
                <p class="posts_date"><? echo  h(convert_to_fuzzy_time($posts["date"]));?></p>
              </div>
              <?php if (isset($posts["music_id"])): ?>
                <div class="post_music_box">
                <p> <i class="fa fa-music" aria-hidden="true"></i>&nbsp;&nbsp;<?= $track
                    ->artists[0]
                    ->name ?>&nbsp;&nbsp;-&nbsp;&nbsp;<?= $track->name ?></p>
                </div>
              <?php endif; ?>
            </div>
            <div class="post_text_box">
              <p>
              <p><?echo wordwrap(nl2br(h($posts["content"])), 36, "\n", true);?></p>
              </p>
            </div>
          </div>
        </div>
        <div class="post_comment_main">
          <div class="post_comment_post_box">
          <?php if (
              isset($_SESSION["username"]) &&
              $_SESSION["username"] != "GUEST"
          ): ?>
            <form action="" method="post" name="comment_post">
              <div class="post_comment_post_user_icon_block">
                <img src="/actions/image.php?id=<?echo h($user['userid']);?>" id="post_comment_user_icon" />
              </div>
              <textarea id="post_comment_textbox" name="content" required></textarea>
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
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
          <div class="post_comment_box">
            <?php if (is_countable($comments)): ?>
              <?php for ($i = 0; $i < count($comments); $i++): ?>
                  <div class="post_comment_block">
                    <div class="post_comment_user_box">
                    <a class ="profile_link" href="/<?echo $comments[$i]["userid"]?>">
                      <div class="post_comment_user_icon_block">
                        <img src="/actions/image.php?id=<?echo h($comments[$i]["userid"]);?>" id="post_comment_user_icon" />
                      </div>
                    </a>
                      <div class="post_comment_body">
                        <div class="post_comment_user_name_block">
                        <a class ="profile_link" href="/<?echo h($comments[$i]["userid"]);?>">
                          <p
                            name="post_comment_user_name"
                            id="post_comment_user_name"
                          >
                            <?echo h($comments[$i]["name"]);?>
                          </p>
                          </a>
                          <p>&nbsp;&nbsp;</p>
                          <p class="post_comment_time"><? echo  h(convert_to_fuzzy_time($comments[$i]["date"]));?> </p>
                        </div>
                        <div class="post_comment_main">
                          <p><?echo wordwrap(nl2br(h($comments[$i]["content"])), 36, "\n", true);?></p>
                        </div>
                      </div>
                      <?php if (
                          isset($_SESSION["username"]) &&
                          $_SESSION["username"] != "GUEST" &&
                          $comments[$i]["userid"] == $_SESSION["username"]
                      ): ?>
                      <form id="delete" action="../actions/delete.php" method="post">
                        <a id="comment_delete" data-toggle="modal" data-target="#exampleModal" ><i class="fa fa-times" aria-hidden="true"></i></a>
                        <input
                            type="hidden"
                            name="commentid"
                            value="<?echo h($comments[$i]["commentid"]);?>"
                        />
                        <input type="hidden" name="type" value="comment">
                      </form>
                     <?php endif; ?>
                    </div>
                  </div>
                <?php endfor; ?>
          <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <!--コメント削除 Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">コメントを削除しますか？</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <div class="modal-body">
        <p>この操作は取り消せません。コメントが削除されます。 </p>
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
