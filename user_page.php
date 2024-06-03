<?php
session_start();
require_once 'classes/UserLogic.php';
require_once 'classes/TweetLogic.php';
require_once 'functions.php';

// ログインしているか判定、していなかったら登録画面に返す
$result = UserLogic::checkLogin();
if(!$result) {
    $_SESSION['login_err'] = 'アカウントを登録してログインしてください。';
    header('Location: signup_form.php');
    return;
}
// 特定のユーザーのページ
if(filter_input(INPUT_POST, 'user_id')){
    $_SESSION['page_user_id'] = filter_input(INPUT_POST, 'user_id');
}
$user_id = $_SESSION['page_user_id'];

$login_user = $_SESSION['login_user'];
$_SESSION['page_user'] = UserLogic::getUserById($user_id);
$page_user = $_SESSION['page_user'];

// いいねを押した時
if($favo = filter_input(INPUT_POST, 'favorite')) {
    $favo_id = filter_input(INPUT_POST, 'favo_id');
    $favo_result = TweetLogic::add_favorite($login_user['id'], $favo_id);
    if($favo_result) {
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit; // リダイレクト後にスクリプトの実行を停止するためにexitを使用
    }
}

// フォロー
// $follow_flag = $_SESSION['follow_flag'];
if($followed_id = filter_input(INPUT_POST, 'follow')){
    $follow_result = UserLogic::follow($login_user['id'], $followed_id);
    if($follow_result) {
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit; // リダイレクト後にスクリプトの実行を停止するためにexitを使用
    }
}
// フォローしているか判別
$follow_flag = UserLogic::isFollowing($login_user['id'], $page_user['id']);



// ユーザー別のタイムラインを取得
$get_timeline = TweetLogic::get_user_timeline($login_user['id'], $page_user['id']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>つぶやきアプリ</title>
</head>
<body>
    <header>
        <h1>つぶやきアプリ</h1>
        <div class="header_right">
            <p>ログイン中：<?= h($login_user['name']); ?>さん</p>
            <form action="logout.php" method="POST">
                <input type="submit" name="logout" value="ログアウト">
            </form>
        </div>
    </header>
    <section class="user_sec">
        <div>
            <h2><?= $page_user['name'] ?>さんのタイムライン</h2>
            <div class="flex">
                <p><?= $page_user['following'] ?>: フォロー中</p>
                <p><?= $page_user['follower'] ?>: フォロワー</p>
            </div>
        </div>
        <?php if($login_user['id'] !== $page_user['id']) : ?>
            <div class="follow_button">
                <form action="" method="POST">
                    <input type="hidden" name="follow" value="<?= $page_user['id'] ?>">
                    <?php if(!$follow_flag) : ?>
                        <input type="submit" value="フォローする">
                    <?php else: ?>
                        <input type="submit" value="フォローを外す">
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
        <a href="mypage.php">Topに戻る</a>
    </section>
    <section>
        <ul class="tweet_list">
            
        <?php require_once 'template/timeline_loop.php'; ?>
        </ul>
    </section>
</body>
</html>