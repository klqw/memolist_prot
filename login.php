<?php
session_start();
require('library.php');
$error = [];
$username = '';
$password = '';

// ログイン中の場合はindex.phpへリダイレクト
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($username === '' || $password === '') {
        $error['login'] = 'blank'; // ユーザー名 or パスワード が未入力のとき
    } else {
        // ログインチェック
        $db = dbconnect();
        // 入力されたユーザーIDと一致するものを探す
        $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
        if (!$stmt) {
            die($db->error);
        }

        $stmt->bind_param('s', $username);
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }

        $stmt->bind_result($id, $name, $hash);
        $result = $stmt->fetch();
        $stmt->close();
        // var_dump($result);

        // ユーザー名が登録されているときにパスワードの一致確認を行う
        if ($result) {
            // パスワードの一致確認
            if (password_verify($password, $hash)) {
                // ログイン成功
                session_regenerate_id();
                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
                // 初回ログインのときにタブを作成する
                $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM tabs WHERE userid = ?');
                if (!$stmt) {
                    die($db->error);
                }
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $counts = $stmt->get_result();
                $count = $counts->fetch_assoc();
                $stmt->close();
                if ($count['cnt'] < 1) {
                    $stmt = $db->prepare('INSERT INTO tabs (position, tab, isactive, userid) VALUES (?, ?, ?, ?)');
                    if (!$stmt) {
                        die($db->error);
                    }
                    $position = 0;
                    $tab = 'Untitled';
                    $isactive = 'active';
                    $stmt->bind_param('issi', $position, $tab, $isactive, $id);
                    $success = $stmt->execute();
                    if (!$success) {
                        die($db->error);
                    }
                }
                header('Location: index.php');
                exit();
            } else {
                // ログイン失敗
                $error['login'] = 'failed';
            }
        } else {
            // 入力したユーザー名が登録されていないとき
            $error['login'] = 'undefined';
        }

    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン画面</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="login-title">
            <p>ログイン画面</p>
        </div>
    </div>
    <div class="login-container">
        <div class="login-contents">
            <p>ユーザー名とパスワードを入力してログインしてください</p>
            <p>
                ユーザー登録がまだの方はこちらからどうぞ<br>
                &raquo;<a href="join/">ユーザー登録をする</a>
            </p>

            <form action="" method="post">
                <label for="login-usr">ユーザー名</label>
                <input type="text" id="login-usr" name="username" class="login-text" maxlength="12" value="<?php echo h($username); ?>">
                <?php if (isset($error['login']) && $error['login'] === 'blank'): ?>
                    <span class="login-error">ユーザー名とパスワードを入力してください</span><br>
                <?php endif; ?>
                <?php if (isset($error['login']) && $error['login'] === 'undefined'): ?>
                    <span class="login-error">入力したユーザー名は登録されていません</span><br>
                <?php endif; ?>
                <label for="login-pwd">パスワード</label>
                <input type="password" id="login-pwd" name="password" class="login-text" maxlength="20" value="<?php echo h($password); ?>">
                <?php if (isset($error['login']) && $error['login'] === 'failed'): ?>
                    <span class="login-error">パスワードを正しく入力してください</span><br>
                <?php endif; ?>
                <button class="login-button">ログインする</button>
            </form>
        </div>
    </div>

</body>
</html>