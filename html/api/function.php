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
    if (isset($_SESSION['username'])) {
        header('Location: /');
        exit();
    }
}
function require_logined_session()
{
    // セッション開始
    @session_start();
    // ログインしていなければ /login.php に遷移
    if (!isset($_SESSION['username'])) {
        header('Location: /account/login');
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
    return hash('sha256', session_id());
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
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
// DBへ接続するファイルを外部化
function getDb()
{
    $dsn = 'mysql:dbname=SNS; host=sns_mysql; charset=utf8';
    $usr = 'usr';
    $passwd = 'password';

    try {
        $db = new PDO($dsn, $usr, $passwd);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        header('Content-Type: text/plain; charset=UTF-8', true, 500);
        exit($e->getMessage());
    } finally {
        $db = null;
    }
}
?>