<?php
session_start();
require('library.php');
unset($_SESSION['error']);

// URL直打ちの場合はDB処理をせずリダイレクト
if (!isset($_SERVER['HTTP_REFERER'])) {
    header('Location: index.php');
    exit();
}

// ログインしているときにDBへの登録を実行
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    // セッションからユーザーIDを取得
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 現在登録されているタブの個数を取得
    $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM tabs WHERE userid = ?');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('i', $userid);
    $stmt->execute();
    $counts = $stmt->get_result();
    $count = $counts->fetch_assoc();
    $position = $count['cnt'] + 0;
    $stmt->close();

    // $position($count['cnt']) < 10 のときに新しいタブをDBに追加
    if ($position < 10) {
        $stmt = $db->prepare('INSERT INTO tabs (position, tab, isactive, userid) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            die($db->error);
        }
        $tab = 'Untitled'; // 新規タブのデフォルトテキスト設定
        $isactive = 'inactive'; // 新規タブは非選択状態に設定
        $stmt->bind_param('issi', $position, $tab, $isactive, $userid);
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }
    
        header('Location: index.php');
        exit();

    } else { // $position >= 10 のときはエラーメッセージを表示させる
        $_SESSION['error'] = 'tab_count';
        header('Location: index.php');
        exit();
    }

} else {
    header('Location: login.php');
    exit();
}
?>