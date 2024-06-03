<?php
session_start();

require_once 'classes/UserLogic.php';

//エラーメッセージ
$err = [];

//バリデーション
if(!$email = filter_input(INPUT_POST, 'email')) {
    $err['email'] = "メールアドレスを記入してください。";
}
if(!$password = filter_input(INPUT_POST, 'password')) {
    $err['password'] = "パスワードを記入してください。";
}

if(count($err) > 0) {
    // エラーがあった場合元のページに戻す
    $_SESSION = $err;
    header('Location: login_form.php');
    return;
}
// ログイン失敗時
$result = UserLogic::login($email, $password);
if(!$result) {
    header('Location: login_form.php');
    return;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン成功 / つぶやきアプリ</title>
</head>
<body>
    <p>ログイン完了しました。</p>
    <a href="mypage.php">トップページへ</a>
</body>
</html>