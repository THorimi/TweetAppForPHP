<?php

/**
 * XSS対策：エスケープ処理
 * @param string $str 対象の文字列
 * @return string 処理された文字列
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF対策：ワンタイムトークン
 * @param void
 * @return string $csrf_token
*/
function setToken() {
    // $csrf_token = bin2hex(random_bytes(32));
    $csrf_token = bin2hex(openssl_random_pseudo_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;

    return $csrf_token;
}