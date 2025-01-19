<?php
session_start();
require('library.php');

// POSTデータを受け取る
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$bgcolor = filter_input(INPUT_POST, 'bgcolor', FILTER_SANITIZE_SPECIAL_CHARS);

// レスポンス用の配列
$response = [
    'status' => '',
    'bgcolor' => ''
];

// ログインしている場合、DBを更新する
if (!isset($_SESSION['id']) || !isset($_SESSION['name'])) { // 未ログインのエラー処理
    header('Location: login.php');
    exit();
} else {
    // セッションからユーザーIDを取得
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 選択したbackground-colorに合わせてDBを更新
    $stmt = $db->prepare('UPDATE memos SET bgcolor = ? WHERE id = ? AND userid = ? LIMIT 1');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('sii', $bgcolor, $id, $userid);
    $success = $stmt->execute();
    if ($success) {
        $response['status'] = 'success';
        $response['bgcolor'] = h($bgcolor);
        echo json_encode($response);
    } else {
        die($db->error);
    }

}
?>