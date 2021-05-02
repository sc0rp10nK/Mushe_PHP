<?php
require_once "../api/function.php";
// セッション開始
@session_start();
// ユーザー情報取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST" && isset($_POST['followid'])) {
        $db = getDb();
        if (isFollowed($db, $_POST['followid'], $_SESSION["username"])) {
            $sql = "DELETE FROM FOLLOWS WHERE followed_userid = :followed_userid AND follower_userid = :follower_userid;";
            $prepare = $db->prepare($sql);
            $prepare->bindValue(":follower_userid", $_POST['followid'], PDO::PARAM_STR);
            $prepare->bindValue(":followed_userid", $_SESSION["username"], PDO::PARAM_STR);
            $prepare->execute();
            $uri = $_SERVER['HTTP_REFERER'];
            header("Location: ".$uri);
        } else {
            $sql = 'INSERT INTO FOLLOWS
            (follower_userid, followed_userid) VALUES (:follower_userid,:followed_userid)';
            $prepare = $db->prepare($sql);
            $prepare->bindValue(":follower_userid", $_POST['followid'], PDO::PARAM_STR);
            $prepare->bindValue(":followed_userid", $_SESSION["username"], PDO::PARAM_STR);
            $prepare->execute();
            $uri = $_SERVER['HTTP_REFERER'];
            header("Location: ".$uri);
        }
    } else {
        header("Location: ../index.php");
    }
} else {
    header("Location: ../index.php");
}

?>