<?php

require_once 'dbconnect.php';

class TweetLogic
{
    /**
     * ツイートを登録する
     * @param int $userId
     * @param string $tweet
     * @return bool $result
     */
    public static function tweet($tweet, $userId, $userName)
    {
        $result = false;
        $sql = 'INSERT INTO tweet (tweet, auther_id, auther_name, datetime) VALUES (?, ?, ?, NOW())';
        
        $arr = [];
        $arr[] = $tweet;
        $arr[] = $userId;
        $arr[] = $userName;
        
        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            $_SESSION['message'] = "ツイートしました"; // または任意の成功メッセージを指定
            return $result;
        } catch(\Exception $e) {
            $_SESSION['message'] = "ツイートに失敗しました"; // または任意の失敗メッセージを指定
            return $result;
        }
    }
    /**
     * いいねを登録
     * @param int $userId
     * @param string $tweet
     * @return bool $result
     */
    public static function add_favorite($userId, $tweetId)
    {
        $result = false;
        $sql = 'INSERT INTO favorite (user_id, tweet_id, datetime) VALUES (?, ?, NOW())';
        
        $arr = [];
        $arr[] = $userId;
        $arr[] = $tweetId;

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            $_SESSION['add_favorite'] = "いいねしました"; // または任意の成功メッセージを指定
            // tweetテーブルのいいねカウントを増加させるクエリ
            $addSql = "UPDATE tweet SET favorite_count = favorite_count + 1 WHERE tweet_id = ?";
            $addArr = [];
            $addArr[] = $tweetId;
            // クエリを実行
            try{
                $addStmt = connect()->prepare($addSql);
                $addResult = $addStmt->execute($addArr);
            } catch(\Exception $e) {
                $_SESSION['add_favorite'] = "いいねカウントエラー";
                return $result;
            }
            return $result;
        } catch(\Exception $e) {
            // もしすでにいいねしていたらいいねを解除
            $removeSql = 'DELETE FROM favorite WHERE user_id = ? AND tweet_id = ?';
            try{
                $removeStmt = connect()->prepare($removeSql);
                $result = $removeStmt->execute($arr);

                $decSql = "UPDATE tweet SET favorite_count = favorite_count - 1 WHERE tweet_id = ?";
                $decArr = [];
                $decArr[] = $tweetId;
                // クエリを実行
                try{
                    $decStmt = connect()->prepare($decSql);
                    $decResult = $decStmt->execute($decArr);
                    $_SESSION['add_favorite'] = "いいねカウント減少"; // または任意の成功メッセージを指定
                    return $result;
                } catch(\Exception $e) {
                    $_SESSION['add_favorite'] = "いいねカウント減少エラー";
                    return $result;
                }
            } catch(\Exception $e) {
                $_SESSION['add_favorite'] = "いいねカウント減少エラー";
                return $result;
            }
        }
    }
    /**
     * リポストを登録/解除
     * @param int $userId
     * @param string $tweet
     * @return bool $result
     */
    public static function repost($userId, $tweetId)
    {
        $result = false;
        $sql = 'INSERT INTO repost (repost_user_id, original_tweet_id, reposted_at) VALUES (?, ?, NOW())';
        
        $arr = [];
        $arr[] = $userId;
        $arr[] = $tweetId;

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            $_SESSION['repost'] = "リポストしました"; // または任意の成功メッセージを指定
            // tweetテーブルのリポストカウントを増加させるクエリ
            $addSql = "UPDATE tweet SET repost_count = repost_count + 1 WHERE tweet_id = ?";
            $addArr = [];
            $addArr[] = $tweetId;
            // クエリを実行
            try{
                $addStmt = connect()->prepare($addSql);
                $addResult = $addStmt->execute($addArr);
            } catch(\Exception $e) {
                $_SESSION['repost'] = "リポストカウントエラー";
                return $result;
            }
            return $result;
        } catch(\Exception $e) {
            // もしすでにリポストしていたらリポストを解除
            $removeSql = 'DELETE FROM repost WHERE repost_user_id = ? AND original_tweet_id = ?';
            try{
                $removeStmt = connect()->prepare($removeSql);
                $result = $removeStmt->execute($arr);

                $decSql = "UPDATE tweet SET repost_count = repost_count - 1 WHERE tweet_id = ?";
                $decArr = [];
                $decArr[] = $tweetId;
                // クエリを実行
                try{
                    $decStmt = connect()->prepare($decSql);
                    $decResult = $decStmt->execute($decArr);
                    $_SESSION['repost'] = "リポストカウント減少"; // または任意の成功メッセージを指定
                    return $result;
                } catch(\Exception $e) {
                    $_SESSION['repost'] = "リポストカウント減少エラー";
                    return $result;
                }
            } catch(\Exception $e) {
                $_SESSION['repost'] = "リポストカウント減少エラー";
                return $result;
            }

        }
    }

    /**
     * タイムラインを取得
     * @param int $userId // ログインユーザがいいねをしたか判別する（未実装）
     * @return array|bool $timeline|false
     */
    public static function get_timeline($userId)
    {
        $result = false;
        // $sql = 'SELECT * FROM tweet ORDER BY datetime desc';
        $sql = "
        SELECT
            repost_user_id,
            repost_user_name,
            tweet_id,
            auther_id,
            auther_name,
            tweet,
            MAX(post_time) AS post_time,
            MAX(original_post_time) AS original_post_time,
            MAX(favorite_count) AS favorite_count,
            MAX(repost_count) AS repost_count,
            MAX(favorite_flag) AS favorite_flag,
            MAX(repost_flag) AS repost_flag,
            post_type
        FROM (
            SELECT 
                repost_user_id, 
                users.name AS repost_user_name, 
                original_tweet_id AS tweet_id, 
                tweet.auther_id, 
                tweet.auther_name, 
                tweet.tweet, 
                reposted_at AS post_time, 
                tweet.datetime AS original_post_time, 
                tweet.favorite_count, 
                tweet.repost_count, 
                CASE WHEN favorite.user_id = ? THEN 1 ELSE 0 END AS favorite_flag, 
                CASE WHEN repost.repost_user_id = ? THEN 1 ELSE 0 END AS repost_flag, 
                'repost' AS post_type 
            FROM 
                repost 
            LEFT OUTER JOIN 
                tweet ON repost.original_tweet_id = tweet.tweet_id 
            LEFT OUTER JOIN 
                users ON repost.repost_user_id = users.id 
            LEFT OUTER JOIN 
                favorite ON repost.original_tweet_id = favorite.tweet_id 
            UNION ALL
            SELECT 
                NULLIF(1, 1), 
                NULLIF(1, 1), 
                tweet.tweet_id, 
                auther_id, 
                auther_name, 
                tweet, 
                tweet.datetime, 
                tweet.datetime, 
                favorite_count, 
                repost_count, 
                CASE WHEN favorite.user_id = ? THEN 1 ELSE 0 END AS favorite_flag, 
                CASE WHEN repost.repost_user_id = ? THEN 1 ELSE 0 END AS repost_flag, 
                'tweet' AS post_type 
            FROM 
                tweet 
            LEFT OUTER JOIN 
                favorite ON tweet.tweet_id = favorite.tweet_id 
            LEFT OUTER JOIN 
                repost ON tweet.tweet_id = repost.original_tweet_id 
        ) AS subquery
        GROUP BY repost_user_id, repost_user_name, tweet_id, auther_id, auther_name, tweet, post_type
        ORDER BY post_time DESC;
        ";
        $arr = [];
        $arr[] = $userId;
        $arr[] = $userId;
        $arr[] = $userId;
        $arr[] = $userId;
        
        try {

            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // $stmt->execute();
            // SQLの結果を返す
            $timeline = $stmt->fetchAll();

            return $timeline;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * ユーザー別のタイムラインを取得
     * @param int $login_userId // ログインユーザがいいねをしたか判別する（未実装）
     * @param int $userId
     * @return array|bool $timeline|false
     */
    public static function get_user_timeline($login_userId, $userId)
    {
        $result = false;
        $sql = "
        SELECT
            MAX(repost_user_id) AS repost_user_id,
            MAX(repost_user_name) AS repost_user_name,
            tweet_id,
            auther_id,
            auther_name,
            tweet,
            MAX(post_time) AS post_time,
            MAX(original_post_time) AS original_post_time,
            MAX(favorite_count) AS favorite_count,
            MAX(repost_count) AS repost_count,
            MAX(favorite_flag) AS favorite_flag,
            MAX(repost_flag) AS repost_flag,
            post_type
        FROM (
            SELECT 
                repost_user_id, 
                users.name AS repost_user_name, 
                original_tweet_id AS tweet_id, 
                tweet.auther_id, 
                tweet.auther_name, 
                tweet.tweet, 
                reposted_at AS post_time, 
                tweet.datetime AS original_post_time, 
                tweet.favorite_count, 
                tweet.repost_count, 
                CASE WHEN favorite.user_id = ? THEN 1 ELSE 0 END AS favorite_flag, 
                CASE WHEN repost.repost_user_id = ? THEN 1 ELSE 0 END AS repost_flag, 
                'repost' AS post_type 
            FROM 
                repost 
            LEFT OUTER JOIN 
                tweet ON repost.original_tweet_id = tweet.tweet_id 
            LEFT OUTER JOIN 
                users ON repost.repost_user_id = users.id 
            LEFT OUTER JOIN 
                favorite ON repost.original_tweet_id = favorite.tweet_id 
            WHERE 
                repost_user_id = ?
            UNION ALL
            SELECT 
                NULLIF(1, 1), 
                NULLIF(1, 1), 
                tweet.tweet_id, 
                auther_id, 
                auther_name, 
                tweet, 
                tweet.datetime, 
                tweet.datetime, 
                favorite_count, 
                repost_count, 
                CASE WHEN favorite.user_id = ? THEN 1 ELSE 0 END AS favorite_flag, 
                CASE WHEN repost.repost_user_id = ? THEN 1 ELSE 0 END AS repost_flag, 
                'tweet' AS post_type 
            FROM 
                tweet 
            LEFT OUTER JOIN 
                favorite ON tweet.tweet_id = favorite.tweet_id 
            LEFT OUTER JOIN 
                repost ON tweet.tweet_id = repost.original_tweet_id 
            WHERE 
                auther_id = ?
        ) AS subquery
        GROUP BY tweet_id, auther_id, auther_name, tweet, post_type
        ORDER BY post_time DESC;
        ";
        $arr = [];
        $arr[] = $login_userId;
        $arr[] = $login_userId;
        $arr[] = $userId;
        $arr[] = $login_userId;
        $arr[] = $login_userId;
        $arr[] = $userId;
        
        try {

            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // SQLの結果を返す
            $timeline = $stmt->fetchAll();

            return $timeline;
        } catch(\Exception $e) {
            return false;
        }
    }
}