<?php

require_once 'dbconnect.php';

class UserLogic
{
    /**
     * ユーザーを登録する
     * @param array $userData
     * @return bool $result
     */
    public static function createUser($userData)
    {
        $result = false;
        $sql = 'INSERT INTO users (name, email, password) VALUES (?, ?, ?)';

        // $userDataを配列に入れる
        $arr = [];
        $arr[] = $userData['username'];
        $arr[] = $userData['email'];
        $arr[] = password_hash($userData['password'], PASSWORD_DEFAULT);

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            return $result;
        } catch(\Exception $e) {
            return $result;
        }
    }

    /**
     * ログイン処理
     * @param string $email
     * @param string $password
     * @return bool $result
     */
    public static function login($email, $password)
    {
        // 結果
        $result = false;
        // ユーザーをemailから検索して取得
        $user = self::getUserByEmail($email);

        if (!$user) {
            $_SESSION['msg'] = 'メールアドレスが一致しません。';
            return $result;
        }
        
        // パスワードの紹介
        if (password_verify($password, $user['password'])){
            // ログイン成功
            // セッションハイジャック対策
            session_regenerate_id(true);
            $_SESSION['login_user'] = $user;
            $result = true;
            return $result;
        }
        
        $_SESSION['msg'] = 'パスワードが一致しません。';
        return $result;
    }

    /**
     * emailからユーザーを取得
     * @param string $email
     * @return array|bool $user|false
     */
    public static function getUserByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = ?';

        // emailを配列に入れる
        $arr = [];
        $arr[] = $email;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // SQLの結果を返す
            $user = $stmt->fetch();
            return $user;
        } catch(\Exception $e) {
            return false;
        }
    }
    /**
     * idからユーザーを取得
     * @param string $id
     * @return array|bool $user|false
     */
    public static function getUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = ?';

        // idを配列に入れる
        $arr = [];
        $arr[] = $id;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // SQLの結果を返す
            $user = $stmt->fetch();
            return $user;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * ログインチェック
     * @param void
     * @return bool $result
     */
    public static function checkLogin()
    {
        $result = false;

        // セッションにログインユーザが入っていなかったらFalse
        if (isset($_SESSION['login_user']) && $_SESSION['login_user']['id'] > 0) {
            return $result = true;
        }

        return $result;
    }

    /**
     * ログアウト処理
     * @param void
     */
    public static function logout()
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * 他のユーザーをフォロー
     * @param int $follow_id
     * @param int $followed_id
     * @return bool false
     */
    public static function follow($follow_id, $followed_id)
    {
        
        try {

            $result = false;
            $sql = 'INSERT INTO follow (follow_user_id, followed_user_id, follow_at) VALUES (?, ?, NOW())';
            // フォロー数、フォロワー数を増加させるクエリ
            $addSql = "
            UPDATE users 
            SET following = CASE 
                              WHEN id = ? THEN following + 1 
                              ELSE following 
                            END,
                follower = CASE 
                              WHEN id = ? THEN follower + 1 
                              ELSE follower 
                            END
            ";
            
            $arr = [];
            $arr[] = $follow_id;
            $arr[] = $followed_id;

            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);

            // クエリを実行
            $addStmt = connect()->prepare($addSql);
            $result = $addStmt->execute($arr);

            $_SESSION['follow'] = "フォローカウント増加";
            return $result;
        } catch(\Exception $e) {
            // もしすでにフォローしていたらフォローを解除
            try{
                $removeSql = 'DELETE FROM follow WHERE follow_user_id = ? AND followed_user_id = ?';

                $removeStmt = connect()->prepare($removeSql);
                $result = $removeStmt->execute($arr);

                $decSql = "
                UPDATE users 
                SET following = CASE 
                                  WHEN id = ? THEN following - 1 
                                  ELSE following 
                                END,
                    follower = CASE 
                                  WHEN id = ? THEN follower - 1 
                                  ELSE follower 
                                END
                ";
                // クエリを実行
                try{
                    $decStmt = connect()->prepare($decSql);
                    $result = $decStmt->execute($arr);
                    $_SESSION['follow'] = "フォローカウント減少"; // または任意の成功メッセージを指定
                    return $result;
                } catch(\Exception $e) {
                    $_SESSION['follow'] = "フォローカウント減少エラー1";
                    return $result;
                }
            } catch(\Exception $e) {
                $_SESSION['follow'] = "フォローカウント減少エラー2";
                return $result;
            }
        }
    }

    /**
     * フォローしているか判別
     * @param int $follow_id
     * @param int $followed_id
     * @return bool false
     */
    public static function isFollowing($follow_id, $followed_id)
    {
        $sql = 'SELECT * FROM follow WHERE follow_user_id = ? AND followed_user_id = ?';

        // 配列に入れる
        $arr = [];
        $arr[] = $follow_id;
        $arr[] = $followed_id;

        try {
            $stmt = connect()->prepare($sql);
            $stmt->execute($arr);
            // SQLの結果を返す
            $user = $stmt->fetch();
            $result = $user ? true:false;
            return $result;
        } catch(\Exception $e) {
            return false;
        }
    }


}