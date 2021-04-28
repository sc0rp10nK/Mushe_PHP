<?php

/**
 * ログイン状態によってリダイレクトを行うsession_startのラッパー関数
 * 初回時または失敗時にはヘッダを送信してexitする
 */
function require_unlogined_session()
{
    // セッション開始
    @session_start();
    // ログインしていれば / に遷移
    if (isset($_SESSION["username"])) {
        header("Location: /");
        exit();
    }
}
function require_logined_session()
{
    // セッション開始
    @session_start();
    // ログインしていなければ /login.php に遷移
    if (!isset($_SESSION["username"])) {
        header("Location: /account/login");
        exit();
    }
}

/**
 * CSRFトークンの生成
 *
 * @return string トークン
 */
function generate_token()
{
    // セッションIDからハッシュを生成
    return hash("sha256", session_id());
}

/**
 * CSRFトークンの検証
 *
 * @param string $token
 * @return bool 検証結果
 */
function validate_token($token)
{
    // 送信されてきた$tokenがこちらで生成したハッシュと一致するか検証
    return $token === generate_token();
}

/**
 * htmlspecialcharsのラッパー関数
 *
 * @param string $str
 * @return string
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}
// DBへ接続するファイルを外部化
function getDb()
{
    $dsn = "mysql:dbname=SNS; host=sns_mysql; charset=utf8";
    $usr = "usr";
    $passwd = "password";

    try {
        $db = new PDO($dsn, $usr, $passwd);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        header("Content-Type: text/plain; charset=UTF-8", true, 500);
        exit($e->getMessage());
    } finally {
        $db = null;
    }
}
function getLoginUser($db)
{
    $username = $_SESSION["username"];
    // bindParamを利用したSQL文の実行
    $sql = "SELECT * FROM USERS WHERE userid = :id;";
    $sth = $db->prepare($sql);
    $sth->bindParam(":id", $username);
    $sth->execute();
    $user = $sth->fetch();
    return $user;
}
function getUser($db,$userid)
{
    // bindParamを利用したSQL文の実行
    $sql = "SELECT * FROM USERS WHERE userid = :id;";
    $sth = $db->prepare($sql);
    $sth->bindParam(":id", $userid);
    $sth->execute();
    $user = $sth->fetch();
    return $user;
}
/**
 * https://gist.github.com/wgkoro/4985763から引用
 * X秒前、X分前、X時間前、X日前などといった表示に変換する。
 * 一分未満は秒、一時間未満は分、一日未満は時間、
 * 31日以内はX日前、それ以上はX月X日と返す。
 * X月X日表記の時、年が異なる場合はyyyy年m月d日と、年も表示する
 *
 * @param   <String> $time_db       strtotime()で変換できる時間文字列 (例：yyyy/mm/dd H:i:s)
 * @return  <String>                X日前,などといった文字列
 **/
function convert_to_fuzzy_time($time_db)
{
    $unix = strtotime($time_db);
    $now = time();
    $diff_sec = $now - $unix;

    if ($diff_sec < 60) {
        $time = $diff_sec;
        $unit = "秒前";
    } elseif ($diff_sec < 3600) {
        $time = $diff_sec / 60;
        $unit = "分前";
    } elseif ($diff_sec < 86400) {
        $time = $diff_sec / 3600;
        $unit = "時間前";
    } elseif ($diff_sec < 2764800) {
        $time = $diff_sec / 86400;
        $unit = "日前";
    } else {
        if (date("Y") != date("Y", $unix)) {
            $time = date("Y年n月j日", $unix);
        } else {
            $time = date("n月j日", $unix);
        }

        return $time;
    }

    return (int) $time . $unit;
}
?>
