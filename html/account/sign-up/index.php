<?php
require_once '../../api/function.php';

require_unlogined_session();

// POSTメソッドのときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['userid']) &&
        isset($_POST['password']) &&
        isset($_POST['name'])
    ) {
        try {
            $pass_reg_str = "/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,50}$/i";
            if(!preg_match($pass_reg_str,h($_POST['password']))){
                throw new Exception('登録失敗しました');
            }
            $db = getDb();
            $userid = h($_POST['userid']);
            $pass = h($_POST['password']);
            $name = h($_POST['name']);
            $sql =
                'INSERT INTO USERS (userid, pwHash, name) VALUES (:userid, :pwHash, :name)';
            $prepare = $db->prepare($sql);
            $prepare->bindValue(':userid', $userid, PDO::PARAM_STR);
            $prepare->bindValue(
                ':pwHash',
                password_hash($pass, PASSWORD_DEFAULT)
            );
            $prepare->bindValue(':name', $name);
            $prepare->execute();
            header('Location: /account/login');
        } catch (PDOException $e) {
            $errmsg = 'アカウントが既に存在がします';
        } catch(Exception $e){
            $errmsg = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
  <?php
  define('title', 'ログイン・Mushe');
  include '../../global_menu.php';
  ?>
<body>
<div class="login-form">
    <form action="" method="post">
        <h2 class="text-center">アカウント作成</h2>
		<? echo "<p class='errmsg'>{$errmsg}</p>" ?>
        <div class="form-group has-error">
        	<input type="text" maxlength="15" class="form-control" name="userid" placeholder="ユーザID" required="required">
        </div>
		<div class="form-group">
            <div class="input-wrap">
                <input id="password" type="password" class="form-control" name="password" placeholder="パスワード" required="required">
                <i class="toggle-pass fa fa-eye"></i>
            </div>
        </div>
        <div class="form-group">
            <div class="input-wrap-pass-confirmation">
                <input id="password-confirmation" type="password" class="form-control" name="password-confirmation" placeholder="パスワード確認" required="required">
                <i class="toggle-pass-confirmation fa fa-eye"></i>
            </div>
        </div>
        <div class="form-group">
            <input type="text" maxlength="15" class="form-control" name="name" placeholder="名前" required="required">
        </div>
        <div class="form-group">
            <button type="submit" id="regiBtn" class="btn btn-primary btn-lg btn-block">登録する</button>
        </div>
    </form>
	<p>アカウントをお持ちですか？ <a href="../login">ログインする</a></p>
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
            $("#regiBtn").prop("disabled", true);
        }else {
            // エラーがなかった場合
            $(".input-wrap").next('span.error').remove(); // エラーメッセージを削除
        }
    });
    //パスワード確認用
    //パスワードの表示非表示
    $(function() {
        $('.toggle-pass-confirmation').on('click', function() {
            $(this).toggleClass('fa-eye fa-eye-slash');
            var input = $(this).prev('input');
            if (input.attr('type') == 'text') {
            input.attr('type','password');
            } else {
            input.attr('type','text');
            }
        });
    });
    $('#password-confirmation').keyup(function() {
        var regexError;
        if(!$(this).val().match(/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,50}$/i)){
            $(this).css("background-color", "#FEF4F8");
            regexError = true;
        }else{
            $(this).css("background-color", "#f2f2f2");
        }
        var passConfirmError;
        if($("#password").val() != $(this).val()){
            $(this).css("background-color", "#FEF4F8");
            passConfirmError = true;
        }else{
            $(this).css("background-color", "#f2f2f2");
        }
        if(regexError){ //正規表現と一致しない場合
            // エラーが見つかった場合
            $("#regiBtn").prop("disabled", true);
            if( !$(".input-wrap-pass-confirmation").next('span.error').length ) { // この要素の後続要素が存在しない場合
                $(".input-wrap-pass-confirmation").after('<span class="error" style="color:red; font-size:13px;">半角英字と半角数字それぞれ1文字以上含む<br>8文字以上50文字以下で入力してください</span>')
            }
        }else {
            // エラーがなかった場合
            $(".input-wrap-pass-confirmation").next('span.error').remove(); // エラーメッセージを削除
            if(passConfirmError){ //パスワードが一致しない場合
                // エラーが見つかった場合
                $("#regiBtn").prop("disabled", true);
                if( !$(".input-wrap-pass-confirmation").next('span.error').length ) { // この要素の後続要素が存在しない場合
                    $(".input-wrap-pass-confirmation").after('<span class="error" style="color:red; font-size:13px;">パスワードが一致しません</span>')
                }
            }else{
               // エラーがなかった場合
                $(".input-wrap-pass-confirmation").next('span.error').remove(); // エラーメッセージを削除
                $("#regiBtn").prop("disabled", false);
            }
        }
    });
</script>
</html>