<?php
session_start();
require('library.php');
$error = '';
$newMemo = '';

// ログイン有無の判定と処理
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
    $userid = $_SESSION['id'];
    $name = $_SESSION['name'];
} else {
    header('Location: login.php');
    exit();
}

// 新しいメモを入力したときの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newMemo = filter_input(INPUT_POST, 'newMemo', FILTER_SANITIZE_SPECIAL_CHARS);

    // 入力チェック
    $pattern = '/^[0]+$/';
    if (!preg_match($pattern, $newMemo) && !trim($newMemo)) {
        $error = 'blank';
    } else if (mb_strlen($newMemo, 'UTF-8') > 100) {
        $error = 'string';
    }

    // エラーがない場合は新規メモ登録の処理へ
    if (empty($error)) {
        $_SESSION['memo'] = $newMemo;

        header('Location: add_memo.php');
        exit();
    }
}

// DBへの接続
$db = dbconnect();

// メッセージの読み込み
$stmt = $db->prepare('SELECT id, position, memo, bgcolor FROM memos WHERE userid = ? ORDER BY position');
if (!$stmt) {
    die($db->error);
}
$stmt->bind_param('i', $userid);
$success = $stmt->execute();
if (!$success) {
    die($db->error);
}

$stmt->bind_result($id, $position, $memo, $bgcolor);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($name) ?>'s memo</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/sunny/jquery-ui.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="title">
            <p><?php echo h($name) ?>'s memo</p>
        </div>
        <div class="logout">
            <a href="logout.php">ログアウト</a>
        </div>
    </div>
    <div class="container">
        <div class="contents-top">
            <ul id="sortableArea">
                <?php while ($stmt->fetch()): ?>
                <li id="<?php echo h($id); ?>" class="list <?php $bgClass = (!$bgcolor) ? 'aqua' : h($bgcolor); echo $bgClass ?>">
                    <span class="memoText"><?php echo h($memo); ?></span>
                    <div class="right-button">
                        <div class="aqua bgcolor-btn" data-value="aqua"></div>
                        <div class="skyblue bgcolor-btn" data-value="skyblue"></div>
                        <div class="lightsalmon bgcolor-btn" data-value="lightsalmon"></div>
                        <div class="edit" data-id="<?php echo h($id); ?>">編集</div>
                        <div class="delete" data-id="<?php echo h($id); ?>">削除</div>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="contents-bottom">
            <form action="" method="post">
                <input type="text" id="newMemo" name="newMemo" placeholder="追加したいメモを記入してください(100文字まで)" maxlength="100" value="<?php echo h($newMemo); ?>">
                <button type="submit" class="newmemo-btn">新しいメモを登録</button>
                <?php if (isset($error) && $error === 'blank'): ?>
                    <p class="error">未入力または半角スペースのみのメモは登録できません</p>
                <?php endif; ?>
                <?php if (isset($error) && $error === 'string'): ?>
                    <p class="error">入力したメモに不正な文字が入っています</p>
                <?php endif; ?>
            </form>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>