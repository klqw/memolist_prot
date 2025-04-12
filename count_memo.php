<?php
session_start();
require('library.php');

// URL直打ちの場合はDB処理をせずリダイレクト
if (!isset($_SERVER['HTTP_REFERER'])) {
    header('Location: index.php');
    exit();
}

// POSTデータを受け取る
$tabid = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

// ログインチェック
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    // セッションからユーザーIDを取得
    $userid = $_SESSION['id'];
} else {
    header('Location: login.php');
    exit();
}

// レスポンス用の配列
$response = [
    'status' => '',
    'count' => 0
];

// DBへの接続
$db = dbconnect();

// メモ個数取得
$stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM memos WHERE userid = ? AND tabid = ?');
if (!$stmt) {
    die($db->error);
}
$stmt->bind_param('ii', $userid, $tabid);
$stmt->execute();
$results = $stmt->get_result();
$result = $results->fetch_assoc();
$count = $result['cnt'];
$response['status'] = 'success';
$response['count'] = $count;
echo json_encode($response);

?>