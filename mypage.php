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

$login_user = $_SESSION['login_user'];

// ツイートした時の動作
$message = "";
if($tweet = filter_input(INPUT_POST, 'new_tweet')){
    if($tweet = filter_input(INPUT_POST, 'tweet_text')) {
        $tweet_result = TweetLogic::tweet($tweet, $login_user['id'], $login_user['name']);
        if($tweet_result) {
            // ツイートが正常に投稿された場合のみリダイレクト
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit; // リダイレクト後にスクリプトの実行を停止するためにexitを使用
        }
    } else {
        $message = "ツイート内容を入力してください";
    }
}
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// リポストを押した時
if($repost_id = filter_input(INPUT_POST, 'repost_id')) {
    $_SESSION['repost'] = "1";
    $repost_result = TweetLogic::repost($login_user['id'], $repost_id);
    if($repost_result) {
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit; // リダイレクト後にスクリプトの実行を停止するためにexitを使用
    }
    echo $repost_id;
    echo $_SESSION['repost'];
}

// いいねを押した時
if($favo = filter_input(INPUT_POST, 'favorite')) {
    $favo_id = filter_input(INPUT_POST, 'favo_id');
    $favo_result = TweetLogic::add_favorite($login_user['id'], $favo_id);
    if($favo_result) {
        header("Location: {$_SERVER['REQUEST_URI']}");
        exit; // リダイレクト後にスクリプトの実行を停止するためにexitを使用
    }
}
// タイムラインを取得
$get_timeline = TweetLogic::get_timeline($login_user['id']);
// var_dump($get_timeline);
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
    <section>
        <?= $message? $message:"" ?>
        <ul class="tweet_list">
            <li>
                <form action="user_page.php" method="POST">
                    <input type="hidden" name="user_id" value="<?= $login_user['id'] ?>">
                    <input type="submit" name="user_page" value="<?= $login_user['name'] ?>">
                </form>
                <form action="mypage.php" method="POST">
                    <input type="hidden" value="">
                    <p>
                        <textarea placeholder="つぶやく内容" name="tweet_text"></textarea>
                    </p>
                    <input type="submit" name="new_tweet" value="つぶやく">
                </form>
            </li>
            <?php require_once 'template/timeline_loop.php'; ?>
        </ul>
    </section>
</body>
</html>