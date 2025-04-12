<?php
session_start();
require('library.php');

// ログインしている場合はセッションの値を代入
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    $userid = $_SESSION['id'];
    $name = $_SESSION['name'];
} else {
    header('Location: login.php');
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

// レスポンス用の配列
$response = [
    'status' => '',
    'message' => ''
];

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
$cnt = $count['cnt'] + 0;
$stmt->close();

// 指定したタブの状態と配置を取得
$stmt = $db->prepare('SELECT position, isactive FROM tabs WHERE id = ? AND userid = ? LIMIT 1');
if (!$stmt) {
    die($db->error);
}
$stmt->bind_param('ii', $id, $userid);
$stmt->execute();
$results = $stmt->get_result();
$result = $results->fetch_assoc();
$position = $result['position'] + 0;
$isactive = $result['isactive'];
$stmt->close();

// $cnt($count['cnt']) > 1 のときに削除処理
if ($cnt > 1) {
    // 削除対象がactiveだったときの処理
    if ($isactive === 'active') {
        // 位置が最後尾の場合
        if (($position + 1) === $cnt) {
            $stmt = $db->prepare('UPDATE tabs SET isactive = ? WHERE position = ? AND userid = ?');
            if (!$stmt) {
                die($db->error);
            }
            $active = 'active';
            $prev_position = $position - 1;
            $stmt->bind_param('sii', $active, $prev_position, $userid);
            $success = $stmt->execute();
            if (!$success) {
                die($db->error);
            }
            $stmt->close();
        } else {    // それ以外の場合
            $stmt = $db->prepare('UPDATE tabs SET isactive = ? WHERE position = ? AND userid = ?');
            if (!$stmt) {
                die($db->error);
            }
            $active = 'active';
            $next_position = $position + 1;
            $stmt->bind_param('sii', $active, $next_position, $userid);
            $success = $stmt->execute();
            if (!$success) {
                die($db->error);
            }
            $stmt->close();
        }
    }
    // 削除処理
    $stmt = $db->prepare('DELETE FROM tabs WHERE id = ? AND userid = ? LIMIT 1');
    if (!$stmt) {
        die($db->error);
    }
    $stmt->bind_param('ii', $id, $userid);
    $success = $stmt->execute();
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = '';
        echo json_encode($response);
    } else {
        die($db->error);
    }
} else { // $cnt <= 1 のときはエラーメッセージを表示させる
    $response['status'] = 'fail';
    $response['message'] = '最低でも1つタブが必要になります。このタブを削除したい場合は新たにタブを追加してください';
    echo json_encode($response);
}

// header('Location: index.php');
// exit();
?>