<?php
require_once "../api/function.php";
// セッション開始
@session_start();
// ユーザー情報取得
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["username"]) && $_SESSION["username"] != "GUEST" && isset($_POST['postid'])) {
        $db = getDb();
        $username = $_SESSION["username"];
        $sql ="SELECT COUNT(*) AS cnt FROM POSTS WHERE post_userid = :id AND postid = :postid;";
        $sth = $db->prepare($sql);
        $sth->bindParam(":id", $username);
        $sth->bindParam(":postid", $_POST['postid']);
        $sth->execute();
        $result = $sth->fetch();
        if ($result["cnt"] > 0) {
                $id = $_POST['postid'];
                $sql = "UPDATE POSTS SET date = NULL WHERE postid = :id;";
                $prepare = $db->prepare($sql);
                $prepare->bindValue(':id', $id);
                $prepare->execute();
                $uri = $_SERVER['HTTP_REFERER'];
                header("Location: ".$uri);
        }
    }
    else{
        header( "Location: ../index.php" );
    }
}else{
    header( "Location: ../index.php" );
}
?>