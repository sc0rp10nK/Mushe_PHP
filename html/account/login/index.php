<?php
require_once '../../api/function.php';
@session_start();
if($_SESSION['username'] === "GUEST"){
    $_SESSION = [];
    session_destroy();
}else{
    require_unlogined_session();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ユーザから受け取ったユーザ名とパスワード
    $username = h(filter_input(INPUT_POST, 'username'));
    $password = h(filter_input(INPUT_POST, 'password'));
    try {
        // データベースへの接続開始
        $db = getDb();
        // bindParamを利用したSQL文の実行
        $sql = 'SELECT * FROM USERS WHERE userid = :id;';
        $sth = $db->prepare($sql);
        $sth->bindParam(':id', $username);
        $sth->execute();
        $result = $sth->fetch();
        //認証処理
        if (
            validate_token(filter_input(INPUT_POST, 'token')) &&
            password_verify($password, $result['pwHash'])
        ) {
            // 認証が成功したとき
            // セッションIDの追跡を防ぐ
            session_regenerate_id(true);
            // ユーザ名をセット
            $_SESSION['username'] = $username;
            // ログイン完了後に / に遷移
            header('Location: /');
            exit();
        } else {
            // 認証が失敗したとき
            // 「403 Forbidden」
            http_response_code(403);
        }
    // データベースへの接続に失敗した場合
    } catch (PDOException $e) {
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  define('title', 'ログイン');
  include '../../global_menu.php';
  ?>
<body>
<div class="login-form">
    <form action="" method="post">
        <h2 class="text-center">ログイン</h2>
		<?php if (http_response_code() === 403): ?>
			<p style="color: red;">ユーザIDまたはパスワードが違います</p>
		<?php endif; ?>
        <div class="form-group has-error">
        	<input type="text" class="form-control" name="username" placeholder="ユーザID" required="required">
        </div>
		<div class="form-group">
            <div class="input-wrap">
                <input id="password" type="password" class="form-control" name="password" placeholder="パスワード" required="required">
                <i class="toggle-pass fa fa-eye"></i>
            </div>
        </div>
		<input type="hidden" name="token" value="<?= h(generate_token()) ?>">
        <div class="form-group">
            <button type="submit" id="loginBtn" class="btn btn-danger ">ログイン</button>
            <button type="submit" formaction="guestLogin.php" id="guestLoginBtn" class="btn btn-primary " formnovalidate>ゲストログイン</button>
        </div>
    </form>
	<p>アカウントをお持ちでないですか？ <a href="../sign-up">登録する</a></p>
</div>
</body>
<script>
       //パスワード用
    //パスワードの表示非表示
    $(function() {
        $('.toggle-pass').on('click', function() {
            $(this).toggleClass('fa-eye fa-eye-slash');
            var input = $(this).prev('input');
            if (input.attr('type') == 'text') {
            input.attr('type','password');
            } else {
            input.attr('type','text');
            }
        });
    });
    //パスワードチェック
    $('#password').keyup(function() {
        var regexError;
        if(!$(this).val().match(/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,50}$/i)){
            $(this).css("background-color", "#FEF4F8");
            regexError = true;
        }else{
            $(this).css("background-color", "#f2f2f2");
        }
        if(regexError){ //正規表現と一致しない場合
            // エラーが見つかった場合
            if( !$(".input-wrap").next('span.error').length ) { // この要素の後続要素が存在しない場合
                $(".input-wrap").after('<span class="error" style="color:red; font-size:13px;">半角英字と半角数字それぞれ1文字以上含む<br>8文字以上50文字以下で入力してください</span>')
            }
            $("#loginBtn").prop("disabled", true);
        }else {
            // エラーがなかった場合
            $(".input-wrap").next('span.error').remove(); // エラーメッセージを削除
            $("#loginBtn").prop("disabled", false);
        }
    });
</script>
</html>