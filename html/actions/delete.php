<?php
require_once "../api/function.php";
// セッション開始
@session_start();
// ユーザー情報取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST") {
        if ($_POST["type"] === "post" && isset($_POST["postid"])) {
            $db = getDb();
            $username = $_SESSION["username"];
            $sql =
                "SELECT COUNT(*) AS cnt FROM POSTS WHERE post_userid = :id AND postid = :postid;";
            $sth = $db->prepare($sql);
            $sth->bindParam(":id", $username);
            $sth->bindParam(":postid", $_POST["postid"]);
            $sth->execute();
            $result = $sth->fetch();
            if ($result["cnt"] > 0) {
                $id = $_POST["postid"];
                $sql = "UPDATE POSTS SET date = NULL WHERE postid = :id;";
                $prepare = $db->prepare($sql);
                $prepare->bindValue(":id", $id);
                $prepare->execute();
                $uri = $_SERVER["HTTP_REFERER"];
                header("Location: " . $uri);
            }
        } elseif ($_POST["type"] === "comment" && isset($_POST["commentid"])) {
            $db = getDb();
            $username = $_SESSION["username"];
            $sql =
                "SELECT COUNT(*) AS cnt FROM COMMENTS WHERE userid = :id AND commentid = :commentid;";
            $sth = $db->prepare($sql);
            $sth->bindParam(":id", $username);
            $sth->bindParam(":commentid", $_POST["commentid"]);
            $sth->execute();
            $result = $sth->fetch();
            if ($result["cnt"] > 0) {
                $commentid = $_POST["commentid"];
                $sql =
                    "UPDATE COMMENTS SET date = NULL WHERE commentid = :commentid;";
                $prepare = $db->prepare($sql);
                $prepare->bindValue(":commentid", $commentid);
                $prepare->execute();
                $uri = $_SERVER["HTTP_REFERER"];
                header("Location: " . $uri);
            }
        }
    } else {
        header("Location: /");
    }
} else {
    header("Location: /");
}
?>
