<?php
session_start();
require('../library.php');

if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
    $form = $_SESSION['form'];
} else {
    $form = [
        'username' => '',
        'password' => ''
    ];
}
$error = [];

// フォームの内容をチェック
$pattern = '/^[a-zA-Z0-9]+$/';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ユーザー名のチェック
    $form['username'] = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($form['username'] === '') {
        $error['username'] = 'blank';
    } else if (!preg_match($pattern, $form['username'])) {
        $error['username'] = 'string';
    } else {
        $db = dbconnect();
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        if (!$stmt) {
            die($db->error);
        }
        $stmt->bind_param('s', $form['username']);
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }

        $stmt->bind_result($cnt);
        $stmt->fetch();

        if ($cnt > 0) {
            $error['username'] = 'duplicate';
        }
    }

    // パスワードのチェック
    $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($form['password'] === '') {
        $error['password'] = 'blank';
    } else if (strlen($form['password']) < 4) {
        $error['password'] = 'length';
    } else if (!preg_match($pattern, $form['password'])) {
        $error['password'] = 'string';
    }

    // エラーがない場合確認画面へ遷移
    if (empty($error)) {
        $_SESSION['form'] = $form;

        header('Location: check.php');
        exit();
    }
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
            <p>次のフォームに必要事項を入力してください</p>
            <form action="" method="post">
                <label for="signup-usr">ユーザー名 (半角英数字 12文字まで)</label>
                <input type="text" id="signup-usr" name="username" class="login-text" maxlength="12" value="<?php echo h($form['username']); ?>">
                <?php if (isset($error['username']) && $error['username'] === 'blank'): ?>
                    <span class="login-error">ユーザー名を入力してください</span><br>
                <?php endif; ?>
                <?php if (isset($error['username']) && $error['username'] === 'string'): ?>
                    <span class="login-error">ユーザー名は半角英数字で入力してください</span><br>
                <?php endif; ?>
                <?php if (isset($error['username']) && $error['username'] === 'duplicate'): ?>
                    <span class="login-error">指定されたユーザー名はすでに登録されています</span><br>
                <?php endif; ?>
                <label for="signup-pwd">パスワード (半角英数字 4文字以上20文字以下)</label>
                <input type="password" id="signup-pwd" name="password" class="login-text" maxlength="20" value="<?php echo h($form['password']); ?>">
                <?php if (isset($error['password']) && $error['password'] === 'blank'): ?>
                    <span class="login-error">パスワードを入力してください</span><br>
                <?php endif; ?>
                <?php if (isset($error['password']) && $error['password'] === 'length'): ?>
                    <span class="login-error">パスワードは4文字以上で入力してください</span><br>
                <?php endif; ?>
                <?php if (isset($error['password']) && $error['password'] === 'string'): ?>
                    <span class="login-error">パスワードは半角英数字で入力してください</span>
                <?php endif; ?>
                <p><button type="submit" class="login-button">入力内容を確認する</button> | &laquo;<a href="../login.php">ログイン画面に戻る</a></p>
            </form>
        </div>
    </div>

</body>
</html>