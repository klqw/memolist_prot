<?php
session_start();
require('library.php');

// URL直打ちの場合はDB処理をせずリダイレクト
if (!isset($_SERVER['HTTP_REFERER'])) {
    header('Location: index.php');
    exit();
}

// POSTデータを受け取る
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$tab = filter_input(INPUT_POST, 'tab', FILTER_SANITIZE_SPECIAL_CHARS);

// レスポンス用の配列
$response = [
    'status' => '',
    'message' => ''
];

// ログインしている and 空文字でない場合、DBを更新する
if (!isset($_SESSION['id']) || !isset($_SESSION['name'])) { // 未ログインのエラー処理
    header('Location: login.php');
    exit();
} else if (!trim($tab)) { // 未入力のエラー処理
    $response['status'] = 'blank';
    $response['message'] = '未入力または半角スペースのみのタブ名は登録できません';
    echo json_encode($response);
} else {
    // セッションからユーザーIDを取得
    $userid = $_SESSION['id'];

    // DBへの接続
    $db = dbconnect();

    // 編集したタブ名でDBを更新
    $stmt = $db->prepare('UPDATE tabs SET tab = ? WHERE id = ? AND userid = ? LIMIT 1');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('sii', $tab, $id, $userid);
    $success = $stmt->execute();
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = h($tab);
        echo json_encode($response);
    } else {
        die($db->error);
    }

}

?>