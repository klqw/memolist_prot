<?php
require('library.php');

// DBへの接続
$db = dbconnect();

// POSTデータを受け取る
$orders = $_POST['order'];

// 配列の順番に従ってIDを更新
foreach ($orders as $position => $id) {
    $stmt = $db->prepare('UPDATE memos SET position = ? WHERE id = ?;');
    $stmt->bind_param('ii', $position, $id);
    $stmt->execute();
}

echo 'Order updated successfully!';
?>