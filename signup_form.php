<?php
session_start();
require_once 'functions.php';
require_once 'classes/UserLogic.php';

// ログインしている場合
$result = UserLogic::checkLogin();
if($result) {
    header('Location: mypage.php');
    return;
}

$login_err = isset($_SESSION['login_err']) ? $_SESSION['login_err'] : null;
unset($_SESSION['login_err']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント作成 / つぶやきアプリ</title>
</head>
<body>
    <div class="form_container">
        <h2>アカウント作成</h2>
        <?php if(isset($login_err)) : ?>
            <p><?= $login_err; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <p>
                <label for="username">名前</label>
                <input type="text" name="username">
            </p>
            <p>
                <label for="email">メールアドレス</label>
                <input type="email" name="email">
            </p>
            <p>
                <label for="password">パスワード</label>
                <input type="password" name="password">
            </p>
            <p>
                <label for="password_conf">パスワード（確認）</label>
                <input type="password" name="password_conf">
            </p>
            <input type="hidden" name="csrf_token" value="<?= h(setToken()); ?>">
            <p>
                <input type="submit" value="アカウントを登録">
            </p>
        </form>
        <a href="login_form.php">ログインする</a>
    </div>
</body>
</html>