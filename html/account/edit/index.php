<?php
// セッション開始
@session_start();
require_once "../../api/function.php";
require_logined_session();
$db = getDb();
//ログインユーザーの情報取得
$user = getLoginUser($db);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST["type"]) &&
        validate_token(filter_input(INPUT_POST, "token"))
    ) {
        $msg;
        $msgEnable = false;
        if ($_POST["type"] === "pedit") {
            $username = $_SESSION["username"];
            if (!empty($_FILES["image"]["name"])) {
                // bindParamを利用したSQL文の実行
                $sql =
                    "SELECT COUNT(*) AS cnt FROM USER_PROFILE_IMAGES WHERE image_userid = :id;";
                $sth = $db->prepare($sql);
                $sth->bindParam(":id", $username);
                $sth->execute();
                $result = $sth->fetch();
                if ($result["cnt"] > 0) {
                    $name = $_FILES["image"]["name"];
                    $type = $_FILES["image"]["type"];
                    $content = file_get_contents($_FILES["image"]["tmp_name"]);
                    $sql =
                        "UPDATE USER_PROFILE_IMAGES SET image_name = :image_name , image_type = :image_type, image_content = :image_content, created_at = now() WHERE image_userid = :image_userid; ";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":image_name", $name, PDO::PARAM_STR);
                    $stmt->bindValue(":image_type", $type, PDO::PARAM_STR);
                    $stmt->bindValue(
                        ":image_content",
                        $content,
                        PDO::PARAM_STR
                    );
                    $stmt->bindValue(
                        ":image_userid",
                        $username,
                        PDO::PARAM_STR
                    );
                    $stmt->execute();
                    $msgEnable = true;
                    $msg = "プロフィールの変更しました。";
                }
            } elseif (isset($_POST["name"])) {
                $name = $_POST["name"];
                $sql = "UPDATE USERS SET name = :name WHERE userid = :userid";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":name", $name, PDO::PARAM_STR);
                $stmt->bindValue(":userid", $username, PDO::PARAM_STR);
                $stmt->execute();
                $msgEnable = true;
                $msg = "プロフィールの変更しました。";
            }
        } elseif ($_POST["type"] === "pwchg") {
            $username = $_SESSION["username"];
            if (
                isset($_POST["oldpassword"]) &&
                isset($_POST["password"]) &&
                isset($_POST["password-confirmation"])
            ) {
                try {
                    $oldpass = $_POST["oldpassword"];
                    $newpass = $_POST["password"];
                    $newpassConfirm = $_POST["password-confirmation"];
                    $pass_reg_str = "/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,50}$/i";
                    if (
                        !preg_match($pass_reg_str, h($oldpass)) &&
                        !preg_match($pass_reg_str, h($newpass)) &&
                        !preg_match($pass_reg_str, h($newpassConfirm))
                    ) {
                        throw new Exception("変更失敗しました。");
                    } elseif (!$newpass === $newpassConfirm) {
                        throw new Exception("変更失敗しました。");
                    }
                    // データベースへの接続開始
                    $db = getDb();
                    // bindParamを利用したSQL文の実行
                    $sql = "SELECT * FROM USERS WHERE userid = :id;";
                    $sth = $db->prepare($sql);
                    $sth->bindParam(":id", $username);
                    $sth->execute();
                    $result = $sth->fetch();
                    //認証処理
                    if (
                        validate_token(filter_input(INPUT_POST, "token")) &&
                        password_verify($oldpass, $result["pwHash"])
                    ) {
                        $sql =
                            "UPDATE USERS SET pwHash = :pwHash WHERE userid = :userid";
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(
                            ":pwHash",
                            password_hash($newpass, PASSWORD_DEFAULT)
                        );
                        $stmt->bindValue(":userid", $username, PDO::PARAM_STR);
                        $stmt->execute();
                        $msgEnable = true;
                        $msg = "パスワードを変更しました";
                    } else {
                        // 認証が失敗したとき
                        // 「403 Forbidden」
                        http_response_code(403);
                    }
                } catch (PDOException $e) {
                    $errmsg = "変更失敗しました。";
                    $msgEnable = true;
                } catch (Exception $e) {
                    $errmsg = $e->getMessage();
                    $msgEnable = true;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<?php
define("title", "Mushe");
define("path", "/account/edit");
include "../../global_menu.php";
if (!empty($msg) && $msgEnable) {
    print "<script>iziToast.success({ title: 'SUCCESS', message: '{$msg}',position: 'topRight' });</script>";
} elseif (!empty($errmsg) && $msgEnable) {
    print "<script>iziToast.error({ title: 'ERROR', message: '{$errmsg}',position: 'topRight' });</script>";
}
?>
<body>
<main class="p-3">
    <div class="content">
            <div class="edit_nav">
                <div class="col-12">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="true">プロフィール編集</a>
                    <a class="nav-link" id="v-pills-password-tab" data-toggle="pill" href="#v-pills-password" role="tab" aria-controls="v-pills-password-tab" aria-selected="false">パスワード変更</a>
                    </div>
                </div>
            </div>
            <div class="edit_body">
                <div class="col-12">
                    <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                    <div id="preview" class="user_icon_block">
                        <img id="preview_img" src="/actions/image.php?id=<?echo h($user['userid']);?>">
                    </div>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="file" name="image" id="input-file" accept="image/*">
                        </div>
                        <div class="form-group">
                        <p>名前</p>
                            <input id="text" type="text" class="form-control" name="name" placeholder="<?echo h($user['name'])?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" id="profileEditBtn" class="btn btn-primary btn-lg btn-block">変更</button>
                        </div>
                        <input type="hidden" name="token" value="<?= h(
                            generate_token()
                        ) ?>">
                        <input type="hidden" name="type" value="pedit">
                    </form>
                    </div>
                    <div class="tab-pane fade" id="v-pills-password" role="tabpanel" aria-labelledby="v-pills-password-tab">
                    <form action="" method="post">
                        <div class="form-group">
                        <p>現在のパスワード</p>
                            <div class="input-wrap-old">
                                <input id="oldpassword" type="password" class="form-control" name="oldpassword" placeholder="現在パスワード" required="required">
                                <i class="oldtoggle-pass fa fa-eye"></i>
                            </div>
                        </div>
                        <div class="form-group">
                        <p>新しいパスワード</p>
                            <div class="input-wrap">
                                <input id="password" type="password" class="form-control" name="password" placeholder="新しいパスワード" required="required">
                                <i class="toggle-pass fa fa-eye"></i>
                            </div>
                        </div>
                        <div class="form-group">
                        <p>新しいパスワードを確認</p>
                            <div class="input-wrap-pass-confirmation">
                                <input id="password-confirmation" type="password" class="form-control" name="password-confirmation" placeholder="新しいパスワードを確認" required="required">
                                <i class="toggle-pass-confirmation fa fa-eye"></i>
                            </div>
                        </div>
                        <input type="hidden" name="token" value="<?= h(
                            generate_token()
                        ) ?>">
                        <input type="hidden" name="type" value="pwchg">
                        <div class="form-group">
                            <button type="submit" id="editBtn" class="btn btn-primary btn-lg btn-block">変更</button>
                        </div>
                    </form>
                    </div>
                    </div>
                </div>
            </div>
    </div>
</main>
<script>
$(function(){
    $('#input-file').change(function(){
        $('#preview_img').remove();
        var file = $(this).prop('files')[0];
        var fileReader = new FileReader();
        fileReader.onloadend = function() {
            $('#preview').html('<img src="' + fileReader.result + '"/>');
            $('#preview_img').addClass('resize-image');
        }
        fileReader.readAsDataURL(file);
    });
});
    //現在パスワード用
    //パスワードの表示非表示
    $(function() {
        $('.oldtoggle-pass').on('click', function() {
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
    $('#oldpassword').keyup(function() {
        var regexError;
        if(!$(this).val().match(/^(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,50}$/i)){
            $(this).css("background-color", "#FEF4F8");
            regexError = true;
        }else{
            $(this).css("background-color", "#f2f2f2");
        }
        if(regexError){ //正規表現と一致しない場合
            // エラーが見つかった場合
            if( !$(".input-wrap-old").next('span.error').length ) { // この要素の後続要素が存在しない場合
                $(".input-wrap-old").after('<span class="error" style="color:red; font-size:13px;">半角英字と半角数字それぞれ1文字以上含む<br>8文字以上50文字以下で入力してください</span>')
            }
            $("#editBtn").prop("disabled", true);
        }else {
            // エラーがなかった場合
            $(".input-wrap-old").next('span.error').remove(); // エラーメッセージを削除
        }
    });
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
            $("#editBtn").prop("disabled", true);
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
            $("#editBtn").prop("disabled", true);
            if( !$(".input-wrap-pass-confirmation").next('span.error').length ) { // この要素の後続要素が存在しない場合
                $(".input-wrap-pass-confirmation").after('<span class="error" style="color:red; font-size:13px;">半角英字と半角数字それぞれ1文字以上含む<br>8文字以上50文字以下で入力してください</span>')
            }
        }else {
            // エラーがなかった場合
            $(".input-wrap-pass-confirmation").next('span.error').remove(); // エラーメッセージを削除
            if(passConfirmError){ //パスワードが一致しない場合
                // エラーが見つかった場合
                $("#editBtn").prop("disabled", true);
                if( !$(".input-wrap-pass-confirmation").next('span.error').length ) { // この要素の後続要素が存在しない場合
                    $(".input-wrap-pass-confirmation").after('<span class="error" style="color:red; font-size:13px;">パスワードが一致しません</span>')
                }
            }else{
               // エラーがなかった場合
                $(".input-wrap-pass-confirmation").next('span.error').remove(); // エラーメッセージを削除
                $("#editBtn").prop("disabled", false);
            }
        }
    });
</script>
</body>
</html>