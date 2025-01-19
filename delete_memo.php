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

// $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

// DBへの接続
$db = dbconnect();
$stmt = $db->prepare('DELETE FROM memos WHERE id = ? AND userid = ? LIMIT 1');
if (!$stmt) {
    die($db->error);
}
$stmt->bind_param('ii', $id, $userid);
$success = $stmt->execute();
// var_dump($success);
if ($success) {
    echo 'success';
} else {
    die($db->error);
}

// header('Location: index.php');
// exit();
?>