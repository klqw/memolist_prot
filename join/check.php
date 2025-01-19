<?php
session_start();
require('../library.php');

if (isset($_SESSION['form'])) {
    $form = $_SESSION['form'];
} else {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = dbconnect();
    $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    if (!$stmt) {
        die($db->error);
    }
    $password = password_hash($form['password'], PASSWORD_DEFAULT);
    $stmt->bind_param('ss', $form['username'], $password);
    $success = $stmt->execute();
    if (!$success) {
        die($db->error);
    }

    unset($_SESSION['form']);
    header('Location: thanks.php');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="header">
        <div class="login-title">
            <p>ユーザー登録</p>
        </div>
    </div>

    <div class="login-container">
        <div class="login-contents">
            <p>入力した内容を確認して、「登録する」ボタンをクリックしてください</p>
            <form action="" method="post">
                <p>
                    ユーザーID<br>
                    <?php echo h($form['username']); ?>
                </p>
                <p>
                    パスワード<br>
                    【表示されません】
                </p>
                <div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <button class="login-button">登録する</button></div>
            </form>
        </div>
    </div>
    
</body>
</html>