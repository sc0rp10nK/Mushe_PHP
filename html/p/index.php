<?php
// セッション開始
@session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  define("title", "Mushe");
  include "../global_menu.php";
  $postid = h($_GET["p"]);
  // bindParamを利用したSQL文の実行
  $sql =
      "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND postid = :id;";
  $sth = $db->prepare($sql);
  $sth->bindParam(":id", $postid);
  $sth->execute();
  $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <body>
  <div class="main">
      <div class="content">
        <div class="main_box">
          <div class="post_user_box">
            <div class="post_user_icon_block">
              <img src="/actions/image.php?id=<?echo h($posts[0]["userid"]);?>" id="post_user_icon" />
            </div>
            <div class="post_user_name_block">
              <p name="post_user_name" id="post_user_name"><?echo h($posts[0]["name"])?></p>
            </div>
            <?php if (
                isset($_SESSION["username"]) &&
                $_SESSION["username"] != "GUEST" &&
                $posts[0]["userid"] != $_SESSION["username"]
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
          <div class="post_img_box">
            <img src="img/img.jpg" id="post_img" />
          </div>
          <div class="footer">
            <div class="post_footer_menu">
              <div class="posts_date_box">
                <p class="posts_date"><? echo  convert_to_fuzzy_time($posts[0]["date"]);?></p>
              </div>
              <div class="post_like_box">
                <i class="fa fa-heart-o"></i>
              </div>
            </div>
            <div class="post_text_box">
              <p>
              <?echo $posts[0]["content"];?>
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
            <form action="post" name="comment_post">
              <div class="post_comment_post_user_icon_block">
                <img src="/actions/image.php?id=<?echo h($user['userid']);?>" id="post_comment_user_icon" />
              </div>
              <textarea id="post_comment_textbox"></textarea>
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
            <div class="post_comment_block">
              <div class="post_comment_user_box">
                <div class="post_comment_user_icon_block">
                  <img src="img/icon2.jpg" id="post_comment_user_icon" />
                </div>
                <div class="post_comment_body">
                  <div class="post_comment_user_name_block">
                    <p
                      name="post_comment_user_name"
                      id="post_comment_user_name"
                    >
                      てすと
                    </p>
                    <p>&nbsp;&nbsp;</p>
                    <p class="post_comment_time">1時間前</p>
                  </div>
                  <div class="post_comment_main">
                    <p>いいですね</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
