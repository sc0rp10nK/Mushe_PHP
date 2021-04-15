$(function () {
  //初期処理
  var $btn = $("#post_button");
  //コメントテキストボックスに文字が存在するかの判定
  if ($.trim($("textarea").val())) {
    //存在しているとき
    $btn.prop("disabled", false);
    $btn.css({ opacity: "1" });
  } else {
    //存在しない
    $btn.prop("disabled", true);
    $btn.css({ opacity: "0.4" });
  }
  //コメントが入力時の判定
  $("textarea").keyup(function () {
    //コメントテキストボックスに文字が存在するかの判定
    if ($.trim($("textarea").val())) {
      //存在しているとき
      $btn.prop("disabled", false);
      $btn.css({ opacity: "1" });
    } else {
      //存在しない
      $btn.prop("disabled", true);
      $btn.css({ opacity: "0.4" });
    }
  });
});
//ブラウザバックされた時の処理
window.onpageshow = function () {
  var $btn = $("#post_button");
  //コメントテキストボックスに文字が存在するかの判定
  if ($.trim($("textarea").val())) {
    //存在しているとき
    $btn.prop("disabled", false);
    $btn.css({ opacity: "1" });
  } else {
    //存在しない
    $btn.prop("disabled", true);
    $btn.css({ opacity: "0.4" });
  }
};
