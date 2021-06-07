<?php
require_once "../api/function.php";
$db = getDb();
require_logined_session();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $userid = h($_GET["id"]);
    $sql = "SELECT COUNT(*) AS cnt FROM USERS WHERE userid = :id;";
    $sth = $db->prepare($sql);
    $sth->bindParam(":id", $userid);
    $sth->execute();
    $result = $sth->fetch();
    if ($result["cnt"] > 0) {
        $profile_user = getUser($db, $userid);
        $followers = getFollowerUsers($db, $userid);
        $follows = getFollowUsers($db, $userid);
        $sql =
            "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND userid = :id ORDER BY date DESC;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $userid);
        $sth->execute();
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    } else {
        http_response_code(404);
        include "../error/404.php";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  $title = h($profile_user["name"]) . "(@" . h($profile_user["userid"]) . ")";
  define("title", $title);
  define("path", "/profile");
  include "../global_menu.php";
  ?>
  <body>
    <div class="user_profile_container">
      <div class="user_profile_box">
        <div class="user_profile_icon_block">
          <img src="/actions/image.php?id=<?echo h($profile_user['userid']);?>" id="user_profile_icon" />
        </div>
        <div class="user_profile_name_block">
        <a id="user_profile_follows_num" data-toggle="modal" data-target="#followModal" ><?echo h(getFollowNum($db,$profile_user['userid']));?>フォロー</a>
          <p name="user_profile_name" id="user_profile_name">
            <?echo h($profile_user['name']);?>
          </p>
          <a id="user_profile_followers_num" data-toggle="modal" data-target="#followerModal" ><?echo h(getFollowerNum($db,$profile_user['userid']));?>フォロワー</a>
          <p name="user_profile_id" id="user_profile_id">
          @<?echo h($profile_user['userid']);?>
          </p>        </div>
        <?php if (
            isset($_SESSION["username"]) &&
            $_SESSION["username"] != "GUEST" &&
            $profile_user["userid"] != $_SESSION["username"]
        ): ?>
          <form class="user_profile_followbtn_block" action="../actions/follow.php" method="post">
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
              <input type="hidden" name="followid" value=<?echo h($profile_user['userid']);?>>
              <?php if (
                  isFollowed(
                      $db,
                      $profile_user["userid"],
                      $_SESSION["username"]
                  )
              ): ?>
                <input
                      class="user_profile_followed_button"
                      id="user_profile_followed_button"
                      type="submit"
                      onMouseOver="this.value='フォロー解除';" onMouseOut="this.value='フォロー中';"
                      value="フォロー中"
                      />
              <?php else: ?>
                <input
                      class="user_profile_follow_button"
                      id="user_profile_follow_button"
                      type="submit"
                      value="フォローする"
                      />
              <?php endif; ?>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php for ($i = 0; $i < count($posts); ++$i): ?>
    <div class="posts_container">
      <?php if (
          isset($_SESSION["username"]) &&
          $_SESSION["username"] != "GUEST" &&
          $posts[$i]["userid"] == $_SESSION["username"]
      ): ?>
      <form id="delete" action="../actions/delete.php" method="post">
        <a id="post_delete" data-toggle="modal" data-target="#exampleModal" >
          <i class="fa fa-times" aria-hidden="true">
          </i>
        </a>
        <input
               type="hidden"
               name="postid"
               value="<?echo h($posts[$i]['postid']);?>"
               />
               <input type="hidden" name="type" value="post">
      </form>
      <?php endif; ?>
      <div class="post_user_box">
        <div class="post_user_icon_block">
          <img src="/actions/image.php?id=<?echo h($posts[$i]['userid']);?>" id="post_user_icon" />
        </div>
        <div class="post_user_name_block">
          <p name="post_user_name" id="post_user_name">
            <?echo h($posts[$i]['name']);?>
          </p>
          <p name="post_user_id" id="post_user_id">
            @<?echo h($posts[$i]['userid']);?>
          </p>
        </div>
        <?php if (
            isset($_SESSION["username"]) &&
            $_SESSION["username"] != "GUEST" &&
            $posts[$i]["userid"] != $_SESSION["username"]
        ): ?>
        <form class="post_followbtn_block" action="../actions/follow.php" method="post">
              <input
              type="hidden"
              name="token"
              value="<?= h(generate_token()) ?>"
              />
              <input type="hidden" name="followid" value=<?echo $profile_user['userid']?>>
              <?php if (
                  isFollowed(
                      $db,
                      $profile_user["userid"],
                      $_SESSION["username"]
                  )
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
        <p class="posts_date">
          <?php echo h(convert_to_fuzzy_time($posts[$i]["date"])); ?>
        </p>
      </div>
      <a class ="link" href="/p?id=<?echo h($posts[$i]['postid']);?>">
      </a>
    </div>
    <?php endfor; ?>
    <!-- 投稿削除Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">投稿を削除しますか？
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×
              </span>
            </button>
          </div>
          <div class="modal-body">
            <p>この操作は取り消せません。プロフィール、あなたをフォローしているアカウントのタイムライン、Musheの検索結果から投稿が削除されます。 
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ
            </button>
            <button type="submit" form="delete" class="btn btn-primary">削除する
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- フォローModal -->
    <div class="modal fade" id="followModal" tabindex="-1" aria-labelledby="followModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="followModalLabel">フォロー</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
          <?php for ($i = 0; $i < count($follows); ++$i): ?>
              <div class="follow_user_box">
                <a class ="profile_link" href="/<?echo h($follows[$i]["userid"]);?>">
                  <div class="follow_user_icon_block">
                    <img src="/actions/image.php?id=<?echo h($follows[$i]['userid']);?>" id="follow_user_icon" />
                  </div>
                </a>
                  <div class="follow_user_name_block">
                  <a class ="profile_link" href="/<?echo h($follows[$i]["userid"]);?>">
                    <p name="follow_user_name" id="follow_user_name">
                      <?echo h($follows[$i]['name']);?>
                    </p>
                  </a> 
                    <p name="follow_user_id" id="follow_user_id">@<?echo h($follows[$i]['userid']);?></p>
                  </div>
                    <?php if (
                        isset($_SESSION["username"]) &&
                        $_SESSION["username"] != "GUEST" &&
                        $follows[$i]["userid"] != $_SESSION["username"]
                    ): ?>
                    <form class="follow_followbtn_block" action="../actions/follow.php" method="post">
                        <input
                        type="hidden"
                        name="token"
                        value="<?= h(generate_token()) ?>"
                        />
                        <input type="hidden" name="followid" value=<?echo h($follows[$i]['userid']);?>>
                        <?php if (
                            isFollowed(
                                $db,
                                $follows[$i]["userid"],
                                $_SESSION["username"]
                            )
                        ): ?>
                          <input
                                class="follow_followed_button"
                                id="follow_followed_button"
                                type="submit"
                                onMouseOver="this.value='フォロー解除';" onMouseOut="this.value='フォロー中';"
                                value="フォロー中"
                                />
                        <?php else: ?>
                          <input
                                class="follow_follow_button"
                                id="follow_follow_button"
                                type="submit"
                                value="フォローする"
                                />
                        <?php endif; ?>
                  </form>
                <?php endif; ?>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- フォロワーModal -->
    <div class="modal fadeい" id="followerModal" tabindex="-1" aria-labelledby="followerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="followerModalLabel">フォロワー</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
          </div>
          <div class="modal-body">
          <?php for ($i = 0; $i < count($followers); ++$i): ?>
            <div class="follower_user_box">
                <a class ="profile_link" href="/<?echo h($followers[$i]["userid"]);?>">
                    <div class="follower_user_icon_block">
                      <img src="/actions/image.php?id=<?echo h($followers[$i]['userid']);?>" id="follower_user_icon" />
                    </div>
                </a>
                <div class="follower_user_name_block">
                  <a class ="profile_link" href="/<?echo $followers[$i]["userid"]?>">
                    <p name="follower_user_name" id="follower_user_name">
                      <?echo h($followers[$i]['name']);?>
                    </p>
                  </a>
                  <p name="follower_user_id" id="follower_user_id">@<?echo h($followers[$i]['userid']);?></p>
                </div>
                <?php if (
                    isset($_SESSION["username"]) &&
                    $_SESSION["username"] != "GUEST" &&
                    $followers[$i]["userid"] != $_SESSION["username"]
                ): ?>
                    <form class="follower_followbtn_block" action="../actions/follow.php" method="post">
                        <input
                        type="hidden"
                        name="token"
                        value="<?= h(generate_token()) ?>"
                        />
                        <input type="hidden" name="followid" value=<?echo h($followers[$i]['userid']);?>>
                        <?php if (
                            isFollowed(
                                $db,
                                $followers[$i]["userid"],
                                $_SESSION["username"]
                            )
                        ): ?>
                          <input
                                class="follower_followed_button"
                                id="follower_followed_button"
                                type="submit"
                                onMouseOver="this.value='フォロー解除';" onMouseOut="this.value='フォロー中';"
                                value="フォロー中"
                                />
                        <?php else: ?>
                          <input
                                class="follower_follow_button"
                                id="follower_follow_button"
                                type="submit"
                                value="フォローする"
                                />
                        <?php endif; ?>
                  </form>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
            </div>
        </div>
      </div>
    </div>
  </body>
</html>
