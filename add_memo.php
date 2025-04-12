<?php
session_start();
require('library.php');
unset($_SESSION['error']);

// URL直打ちの場合はDB処理をせずリダイレクト
if (!isset($_SERVER['HTTP_REFERER'])) {
    header('Location: index.php');
    exit();
}

// ログインチェック
if (!isset($_SESSION['id']) || !isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

// 新規メモデータがセッションに入っているときにDBへの登録を実行
if (isset($_SESSION['memo'])) {
    // セッションからメモとユーザーIDを取得
    $memo = $_SESSION['memo'];
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 現在選択しているタブIDを取得
    $stmt = $db->prepare('SELECT * FROM tabs WHERE isactive = ? AND userid = ?');
    if (!$stmt) {
        die($db->error);
    }
    $isactive = 'active';
    $stmt->bind_param('si', $isactive, $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $tabid = $row['id']; // 取得したタブID
    $stmt->close();

    // 現在登録されているメモの個数を取得
    $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM memos WHERE userid = ? AND tabid = ?');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('ii', $userid, $tabid);
    $stmt->execute();
    $counts = $stmt->get_result();
    $count = $counts->fetch_assoc();
    // var_dump($count);
    $position = $count['cnt'] + 0;
    // var_dump($position);
    $stmt->close();

    // $position($count['cnt']) < 25 のときに新しいメモをDBに追加
    if ($position < 25) {
        $stmt = $db->prepare('INSERT INTO memos (position, memo, bgcolor, userid, tabid) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            die($db->error);
        }
        $bgcolor = 'aqua'; // 新規メモのデフォルト背景色はaqua
        $stmt->bind_param('issii', $position, $memo, $bgcolor, $userid, $tabid);
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }
    
        unset($_SESSION['memo']);
        header('Location: index.php');
        exit();

    } else { // $position >= 25 のときはエラーメッセージを表示させる
        $_SESSION['error'] = 'memo_count';
        header('Location: index.php');
        exit();
    }
    
} else {
    header('Location: index.php');
    exit();
}
?>