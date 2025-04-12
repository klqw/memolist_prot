<?php
require('library.php');

// URL直打ちの場合はDB処理をせずリダイレクト
if (!isset($_SERVER['HTTP_REFERER'])) {
    header('Location: index.php');
    exit();
}

// DBへの接続
$db = dbconnect();

// POSTデータを受け取る
$orders = $_POST['order'];

// 配列の順番に従ってIDを更新
foreach ($orders as $position => $id) {
    $stmt = $db->prepare('UPDATE tabs SET position = ? WHERE id = ?;');
    $stmt->bind_param('ii', $position, $id);
    $stmt->execute();
}

echo 'Order updated successfully!';
?>