<?php
require_once "vendor/autoload.php";
require_once "api/function.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db = getDb();
require_logined_session();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //投稿取得
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST") {
        $username = $_SESSION["username"];
        if (0 < getFollowNum($db, $username)) {
            // bindParamを利用したSQL文の実行
            $sql = "SELECT postid,post_userid,name, content,date,userid,followed_userid,music_id FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid JOIN FOLLOWS ON FOLLOWS.follower_userid = USERS.userid WHERE date IS NOT NULL AND followed_userid = :userid
              UNION SELECT postid,post_userid,name, content, date,userid,followed_userid,music_id FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid JOIN FOLLOWS ON FOLLOWS.followed_userid = USERS.userid WHERE date IS NOT NULL AND userid = :userid ORDER BY date DESC";
            $sth = $db->prepare($sql);
            $sth->bindParam(":userid", $username);
            $sth->execute();
            $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql =
                "SELECT postid,post_userid,name, content,date,userid,music_id FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND userid = :userid ORDER BY date DESC;";
            $sth = $db->prepare($sql);
            $sth->bindParam(":userid", $username);
            $sth->execute();
            $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $sql =
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL ORDER BY date DESC;";
        $sth = $db->query($sql);
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    //投稿する
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
        if (isset($_SESSION["access"])) {
            $api = new SpotifyWebAPI\SpotifyWebAPI();
            $api->setAccessToken($_SESSION["access"]);
            try {
                if (strpos($_POST["content"], '$なうぷれ') !== false) {
                    $nowplaying = $api->getMyCurrentTrack();
                    $music_id = $nowplaying->item->id;
                    $content = str_replace(
                        '$なうぷれ' . "\r\n",
                        "",
                        $_POST["content"]
                    );
                    $content = str_replace(
                        '$なうぷれ' . "\r",
                        "",
                        $_POST["content"]
                    );
                    $content = str_replace('$なうぷれ', "", $_POST["content"]);
                } elseif (
                    strpos(
                        $_POST["content"],
                        "https://open.spotify.com/track/"
                    ) !== false
                ) {
                    $array = explode("$", $_POST["content"]);
                    $tmp_musicid = str_replace(
                        "https://open.spotify.com/track/",
                        "",
                        $array[0]
                    );
                    $music_id = mb_strstr($tmp_musicid, "?si=", true);
                    $music_id = str_replace("\r\n", "", $music_id);
                    $content = $array[1];
                } else {
                    $music_id = null;
                    $content = $_POST["content"];
                }
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
                    $nowplaying = $api->getMyCurrentTrack();
                    $music_id = $nowplaying->item->id;
                    $content = str_replace(
                        '$なうぷれ' . "\r\n",
                        "",
                        $_POST["content"]
                    );
                    $content = str_replace(
                        '$なうぷれ' . "\r",
                        "",
                        $_POST["content"]
                    );
                    $content = str_replace('$なうぷれ', "", $_POST["content"]);
                }
            }
        } elseif (
            strpos($_POST["content"], '$なうぷれ') !== false ||
            strpos($_POST["content"], "https://open.spotify.com/track/") !==
                false
        ) {
            header("Location: /actions/callback.php");
        } else {
            $music_id = null;
            $content = $_POST["content"];
        }
        $date = date("Y/m/d H:i:s");
        $sql = 'INSERT INTO POSTS
(content, add_date, date, post_userid,music_id) VALUES (:content, :add_date, :date, :post_userid,:music_id)';
        $prepare = $db->prepare($sql);
        $prepare->bindValue(":content", $content, PDO::PARAM_STR);
        $prepare->bindValue(":add_date", $date);
        $prepare->bindValue(":date", $date);
        $prepare->bindValue(":post_userid", $user["userid"]);
        $prepare->bindValue(":music_id", $music_id);
        $prepare->execute();
        header("Location: /");
    }
    header("Location: /");
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  define("title", "Mushe");
  define("path", "");
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
              <textarea class="form-control" name="content" id="content"  maxlength="150" required></textarea>
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
      <form id="delete" action="/actions/delete.php" method="post">
        <a id="post_delete" data-toggle="modal" data-target="#exampleModal" ><i class="fa fa-times" aria-hidden="true"></i></a>
        <input
            type="hidden"
            name="postid"
            value="<?echo h($posts[$i]["postid"])?>"
        />
        <input type="hidden" name="type" value="post">
      </form>
      <?php endif; ?>
          <div class="post_user_box">
                <a class ="profile_link" href="/<?echo h($posts[$i]["userid"]);?>">
                  <div class="post_user_icon_block">
                    <img src="/actions/image.php?id=<?echo h($posts[$i]["userid"]);?>" id="post_user_icon" />
                  </div>
                  </a>
                <a class ="profile_link" href="/<?echo h($posts[$i]["userid"]);?>">
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
              <input type="hidden" name="followid" value=<?echo h($posts[$i]['userid']);?>>
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
          <p><?echo wordwrap(nl2br(h($posts[$i]["content"])), 36, "\n", true);?></p>
          </div>
          <div class="posts_footer">
          <?php if (isset($posts[$i]["music_id"])): ?>
            <p class="posts_spotify_required">Spotify user only</p>
          <?php endif; ?>
          <p class="posts_date"><? echo  convert_to_fuzzy_time($posts[$i]["date"]);?></p>
          </div>
          <a class ="link" href="/p/<?echo h($posts[$i]["postid"]);?>"></a>
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