<?php
session_start();
require('library.php');

// 新規メモデータがセッションに入っている and ログインしているときにDBへの登録を実行
if (isset($_SESSION['memo']) && isset($_SESSION['id']) && isset($_SESSION['name'])) {
    // セッションからメモとユーザーIDを取得
    $memo = $_SESSION['memo'];
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 現在登録されているメモの個数を取得
    $counts = $db->query('SELECT COUNT(*) AS cnt FROM memos');
    $count = $counts->fetch_assoc();
    // var_dump($count);
    $position = $count['cnt'] + 0;
    // var_dump($position);

    // 新しいメモをDBに追加
    $stmt = $db->prepare('INSERT INTO memos (position, memo, bgcolor, userid) VALUES (?, ?, ?, ?)');
    if (!$stmt) {
        die($db->error);
    }
    $bgcolor = 'aqua'; // 新規メモのデフォルト背景色はaqua
    $stmt->bind_param('issi', $position, $memo, $bgcolor, $userid);
    $success = $stmt->execute();
    if (!$success) {
        die($db->error);
    }

    unset($_SESSION['memo']);
    header('Location: index.php');
    exit();
    
} else {
    header('Location: index.php');
    exit();
}
?>