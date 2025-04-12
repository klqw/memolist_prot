<?php
session_start();
require('library.php');
unset($_SESSION['error']);

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

// DBへの接続
$db = dbconnect();

$stmt = $db->prepare('UPDATE tabs SET isactive = ? WHERE userid = ?');
if (!$stmt) {
  die($db->error);
}
$inactive = 'inactive';
$stmt->bind_param('si', $inactive, $userid);
$success = $stmt->execute();
$stmt->close();

$stmt = $db->prepare('UPDATE tabs SET isactive = ? WHERE id = ? AND userid = ? LIMIT 1');
if (!$stmt) {
    die($db->error);
}
$isactive = 'active';
$stmt->bind_param('sii', $isactive, $id, $userid);
$success = $stmt->execute();
if ($success) {
    echo 'success';
} else {
    die($db->error);
}

?>