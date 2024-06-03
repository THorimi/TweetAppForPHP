<?php
session_start();
require_once 'classes/UserLogic.php';
//エラーメッセージ
$err = [];

$token = filter_input(INPUT_POST, 'csrf_token');
// トークンがない、もしくは一致しない場合処理を中止
if(!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
    exit("不正なリクエストです。");
}

//バリデーション
if(!$username = filter_input(INPUT_POST, 'username')) {
    $err[] = "名前を記入してください。";
}
if(!$email = filter_input(INPUT_POST, 'email')) {
    $err[] = "メールアドレスを記入してください。";
}
$password = filter_input(INPUT_POST, 'password');
if (!preg_match("/\A(?=.*?[A-z])(?=.*?\d)[A-z\d]{6,20}+\z/",$password)) {
    $err[] = "パスワードは半角英数字混在で6桁以上20桁以下で記入してください。";
}
$password_conf = filter_input(INPUT_POST, 'password_conf');
if ($password !== $password_conf) {
    $err[] = "確認用パスワードと異なった値です。";
}

if(count($err) === 0) {
    // ユーザー登録処理
    $hasCreated = UserLogic::createUser($_POST);
    if(!$hasCreated) {
        $err[] = '登録に失敗しました。';
    }
}

// 二重送信対策
unset($_SESSION['csrf_token']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント登録完了 / つぶやきアプリ</title>
</head>
<body>
    <?php if(count($err) > 0): ?>
        <?php foreach($err as $e): ?>
            <p><?= $e ?></p>
        <?php endforeach ?>
    <?php else: ?>
        <p>アカウント登録完了しました。</p>
    <?php endif ?>
    <a href="signup_form.php">戻る</a>
    <a href="login.php">ログインする</a>
</body>
</html>