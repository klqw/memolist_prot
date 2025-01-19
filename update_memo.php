<?php
session_start();
require('library.php');

// POSTデータを受け取る
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$memo = filter_input(INPUT_POST, 'memo', FILTER_SANITIZE_SPECIAL_CHARS);

// レスポンス用の配列
$response = [
    'status' => '',
    'message' => ''
];

// ログインしている and 空文字でない and 100文字以下の場合、DBを更新する
$pattern = '/^[0]+$/';
if (!isset($_SESSION['id']) || !isset($_SESSION['name'])) { // 未ログインのエラー処理
    header('Location: login.php');
    exit();
} else if (!preg_match($pattern, $memo) && !trim($memo)) { // 未入力のエラー処理
    $response['status'] = 'blank';
    $response['message'] = '未入力または半角スペースのみのメモは登録できません';
    echo json_encode($response);
} else if (mb_strlen($memo, 'UTF-8') > 100) { // 100文字超えた場合のエラー処理
    $response['status'] = 'string';
    $response['message'] = '入力したメモに不正な文字が入っています';
    echo json_encode($response);
} else {
    // セッションからユーザーIDを取得
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 編集したメモでDBを更新
    $stmt = $db->prepare('UPDATE memos SET memo = ? WHERE id = ? AND userid = ? LIMIT 1');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('sii', $memo, $id, $userid);
    $success = $stmt->execute();
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = h($memo);
        echo json_encode($response);
    } else {
        die($db->error);
    }

}

?>