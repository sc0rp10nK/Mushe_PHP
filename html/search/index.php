<?php
require_once "../api/function.php";
$db = getDb();
require_logined_session();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["q"])) {
        $tmq = h($_GET["q"]);
        if (0 === strpos($_GET["q"], "@")) {
            $q = "{$tmq}";
            $id = mb_substr($q, 1);
            $id = "%{$id}%";
            $sql = "SELECT * FROM USERS WHERE userid LIKE :id";
            $sth = $db->prepare($sql);
            $sth->bindParam(":id", $id);
            $sth->execute();
            $resultUsers = $sth->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $q = "%{$tmq}%";
            $sql =
                "SELECT * FROM POSTS JOIN USERS ON POSTS.post_userid = USERS.userid WHERE date IS NOT NULL AND content LIKE :q";
            $sth = $db->prepare($sql);
            $sth->bindParam(":q", $q);
            $sth->execute();
            $resultPosts = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<?php
$title = "Mushe - 検索結果:{$_GET["q"]}";
define("title", $title);
define("path", "/search");
include "../global_menu.php";
?>
    <body>
        <?php if (isset($resultPosts) && count($resultPosts) > 0): ?>
            <?php for ($i = 0; $i < count($resultPosts); ++$i): ?>
                <div class="posts_container">
                <?php if (
                    isset($_SESSION["username"]) &&
                    $_SESSION["username"] != "GUEST" &&
                    $resultPosts[$i]["userid"] == $_SESSION["username"]
                ): ?>
                <form id="delete" action="../actions/delete.php" method="post">
                    <a id="post_delete" data-toggle="modal" data-target="#exampleModal" >
                    <i class="fa fa-times" aria-hidden="true">
                    </i>
                    </a>
                    <input
                        type="hidden"
                        name="postid"
                        value="<?echo $resultPosts[$i]['postid']?>"
                        />
                </form>
                <?php endif; ?>
                <div class="post_user_box">
                <a class ="profile_link" href="/<?echo h($resultPosts[$i]["userid"]);?>">
                    <div class="post_user_icon_block">
                        <img src="/actions/image.php?id=<?echo h($resultPosts[$i]["userid"]);?>" id="post_user_icon" />
                    </div>
                    </a>
                    <a class ="profile_link" href="/<?echo h($resultPosts[$i]["userid"]);?>">
                    <div class="post_user_name_block">
                        <p name="post_user_name" id="post_user_name"><?echo h($resultPosts[$i]["name"]);?></p>
                        <p name="post_user_id" id="post_user_id">@<?echo h($resultPosts[$i]["userid"]);?></p>
                    </div>
                    </a>
                    <?php if (
                        isset($_SESSION["username"]) &&
                        $_SESSION["username"] != "GUEST" &&
                        $resultPosts[$i]["userid"] != $_SESSION["username"]
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
                                class="post_button"
                                id="post_button"
                                type="submit"
                                value="フォローする"
                                />
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="posts_body">
                    <p><?echo wordwrap(nl2br(h($resultPosts[$i]["content"])), 36, "\n", true);?></p>
                </div>
                <div class="posts_footer">
                <?php if (isset($resultPosts[$i]["music_id"])): ?>
                    <p class="posts_spotify_required">Spotify user only</p>
                <?php endif; ?>
                    <p class="posts_date">
                    <?php echo h(
                        convert_to_fuzzy_time($resultPosts[$i]["date"])
                    ); ?>
                    </p>
                </div>
                <a class ="link" href="/p?id=<?echo h($resultPosts[$i]['postid']);?>">
                </a>
                </div>
            <?php endfor; ?>
        <?php elseif (isset($resultUsers) && count($resultUsers) > 0): ?>
            <div class="users_container">
            <?php for ($i = 0; $i < count($resultUsers); ++$i): ?>
                <div class="user_box">
                    <a class ="profile_link" href="/<?echo h($resultUsers[$i]["userid"]);?>">
                    <div class="user_icon_block">
                        <img src="/actions/image.php?id=<?echo h($resultUsers[$i]['userid']);?>" id="user_icon" />
                    </div>
                    </a>
                    <div class="user_name_block">
                    <a class ="profile_link" href="/<?echo h($resultUsers[$i]["userid"]);?>">
                        <p name="user_name" id="user_name">
                        <?echo h($resultUsers[$i]['name']);?>
                        </p>
                    </a> 
                        <p name="user_id" id="user_id">@<?echo h($resultUsers[$i]['userid']);?></p>
                    </div>
                        <?php if (
                            isset($_SESSION["username"]) &&
                            $_SESSION["username"] != "GUEST" &&
                            $resultUsers[$i]["userid"] != $_SESSION["username"]
                        ): ?>
                        <form class="followbtn_block" action="../actions/follow.php" method="post">
                            <input
                            type="hidden"
                            name="token"
                            value="<?= h(generate_token()) ?>"
                            />
                            <input type="hidden" name="followid" value=<?echo h($resultUsers[$i]['userid']);?>>
                            <?php if (
                                isFollowed(
                                    $db,
                                    $resultUsers[$i]["userid"],
                                    $_SESSION["username"]
                                )
                            ): ?>
                            <input
                                    class="followed_button"
                                    id="followed_button"
                                    type="submit"
                                    onMouseOver="this.value='フォロー解除';" onMouseOut="this.value='フォロー中';"
                                    value="フォロー中"
                                    />
                            <?php else: ?>
                            <input
                                    class="button"
                                    id="button"
                                    type="submit"
                                    value="フォローする"
                                    />
                            <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        <?php else: ?>
        <div class="not_found_box">
         <h2>「<?echo $tmq?>」の検索結果はありません</h2>
         <p>入力した単語の検索結果はありません。単語の入力を間違えた可能性があります。</p>
        </div>
        <?php endif; ?>
    </body>
</html>