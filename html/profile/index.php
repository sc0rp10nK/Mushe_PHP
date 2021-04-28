<?php
require_once '../api/function.php';
$db = getDb();
require_logined_session();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userid = h($_GET['id']);
    $sql = 'SELECT COUNT(*) AS cnt FROM USERS WHERE userid = :id;';
    $sth = $db->prepare($sql);
    $sth->bindParam(':id', $userid);
    $sth->execute();
    $result = $sth->fetch();
    if ($result['cnt'] > 0) {
        $profile_user = getUser($db,$userid);
        $sql = 'SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND userid = :id ORDER BY date DESC;';
        $sth = $db->prepare($sql);
        $sth->bindParam(':id', $userid);
        $sth->execute();
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    } else {
        http_response_code(404);
        include 'error/404.php';
        exit();
    }
} 
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
$title = h($profile_user['name']).'(@'.h($profile_user['userid']).')'; define('title', $title); include '../global_menu.php'; ?>
  <body>
    <div class="user_profile_container">
      <div class="user_profile_box">
        <div class="user_profile_icon_block">
          <img src="/actions/image.php?id=<?echo h($profile_user['userid']);?>" id="user_profile_icon" />
        </div>
        <div class="user_profile_name_block">
          <p name="user_profile_follows_num" id="user_profile_follows_num">100フォロー</p>
          <p name="user_profile_name" id="user_profile_name">
            <?echo h($profile_user['name']);?>
          </p>
          <p name="user_profile_followers_num" id="user_profile_followers_num">100フォロワー</p>
          <p name="user_profile_id" id="user_profile_id">@
            <?echo h($profile_user['userid']);?>
          </p>        </div>
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] != 'GUEST' && $user['userid'] != $_SESSION['username']): ?>
        <div class="user_profile_followbtn_block">
          <input
                 class="user_profile_follow_button"
                 id="user_profile_follow_button"
                 type="button"
                 value="フォローする"
                 />
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php for ($i = 0; $i < count($posts); ++$i): ?>
    <div class="posts_container">
      <?php if (isset($_SESSION['username']) && $_SESSION['username'] != 'GUEST' && $posts[$i]['userid'] == $_SESSION['username']): ?>
      <form id="delete" action="actions/delete.php" method="post">
        <a id="post_delete" data-toggle="modal" data-target="#exampleModal" >
          <i class="fa fa-times" aria-hidden="true">
          </i>
        </a>
        <input
               type="hidden"
               name="postid"
               value="<?echo $posts[$i]['postid']?>"
               />
      </form>
      <?php endif;?>
      <div class="post_user_box">
        <div class="post_user_icon_block">
          <img src="/actions/image.php?id=<?echo h($posts[$i]['userid']);?>" id="post_user_icon" />
        </div>
        <div class="post_user_name_block">
          <p name="post_user_name" id="post_user_name">
            <?echo h($posts[$i]['name']);?>
          </p>
          <p name="post_user_id" id="post_user_id">@
            <?echo h($posts[$i]['userid']);?>
          </p>
        </div>
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] != 'GUEST' && $posts[$i]['userid'] != $_SESSION['username']): ?>
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
        <p>
          <?echo nl2br($posts[$i]['content']);?>
        </p>
      </div>
      <div class="posts_footer">
        <p class="posts_date">
          <?php echo convert_to_fuzzy_time($posts[$i]['date']);?>
        </p>
      </div>
      <a class ="link" href="/p?id=<?echo $posts[$i]['postid']?>">
      </a>
    </div>
    <?php endfor; ?>
    <!-- Modal -->
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
  </body>
</html>
