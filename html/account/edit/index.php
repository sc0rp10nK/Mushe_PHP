<?php
require_once '../../api/function.php';
$db = getDb();
require_logined_session();
$user = getUser($db);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['type']) && validate_token(filter_input(INPUT_POST, 'token'))) {
            if($_POST['type'] == 'pedit'){
                $name = $_POST['name'];
                $username = $_SESSION['username'];
                // bindParamを利用したSQL文の実行
                $sql='SELECT COUNT(*) AS cnt FROM USER_PROFILE_IMAGES WHERE image_userid = :id;';
                $sth= $db->prepare($sql);
                $sth->bindParam(':id', $username);
                $sth->execute();
                $result =  $sth -> fetchAll(PDO::FETCH_ASSOC);
                if($result[0]['cnt'] > 0){
                }else{
                    if (isset($_FILES['image']['name'])) {
                        $name = $_FILES['image']['name'];
                        $type = $_FILES['image']['type'];
                        $content = file_get_contents($_FILES['image']['tmp_name']);
                        $size = $_FILES['image']['size'];
                        $sql = 'INSERT INTO USER_PROFILE_IMAGES(image_name, image_type, image_content, image_size, created_at,image_userid)
                                    VALUES (:image_name, :image_type, :image_content, :image_size, now(),:image_userid)';
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':image_name', $name, PDO::PARAM_STR);
                        $stmt->bindValue(':image_type', $type, PDO::PARAM_STR);
                        $stmt->bindValue(':image_content', $content, PDO::PARAM_STR);
                        $stmt->bindValue(':image_size', $size, PDO::PARAM_INT);
                        $stmt->bindValue(':image_userid', $username, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
            }
       }
}
?>
<!DOCTYPE html>
<html lang="ja">
<?php
define('title', 'Mushe');
include '../../global_menu.php';
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
                        <img src="/actions/image.php?id=<?echo h($username)?>">
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
                        <input type="hidden" name="token" value="<?= h(generate_token()) ?>">
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
        $('img').remove();
        var file = $(this).prop('files')[0];
        var fileReader = new FileReader();
        fileReader.onloadend = function() {
            $('#preview').html('<img src="' + fileReader.result + '"/>');
            $('img').addClass('resize-image');
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