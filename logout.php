<?php
session_start();

unset($_SESSION['id']);
unset($_SESSION['name']);
unset($_SESSION['access_token']);

header('Location: login.php');
exit();
?>