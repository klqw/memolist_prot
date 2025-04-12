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

// タブの読み込み
$stmt_tab = $db->prepare('SELECT id, position, tab, isactive FROM tabs WHERE userid = ? ORDER BY position');
if (!$stmt_tab) {
    die($db->error);
}
$stmt_tab->bind_param('i', $userid);
$success = $stmt_tab->execute();
if(!$success) {
    die($db->error);
}
$stmt_tab->bind_result($id, $position, $tab, $isactive);

$tabs = [];
while ($stmt_tab->fetch()) {
    $tabs[] = [
        'id' => $id,
        'position' => $position,
        'tab' => $tab,
        'isactive' => $isactive
    ];
}
$stmt_tab->close();

// メモの読み込み
$stmt_memo = $db->prepare('SELECT m.id, m.position, m.memo, m.bgcolor, m.tabid FROM memos AS m LEFT JOIN tabs AS t ON m.tabid = t.id WHERE m.userid = ? AND t.isactive = ? ORDER BY position');
if (!$stmt_memo) {
    die($db->error);
}
$isactive_memo = 'active';
$stmt_memo->bind_param('is', $userid, $isactive_memo);
$success = $stmt_memo->execute();
if (!$success) {
    die($db->error);
}
$stmt_memo->bind_result($id, $position, $memo, $bgcolor, $tabid);

$memos = [];
while ($stmt_memo->fetch()) {
    $memos[] = [
        'id' => $id,
        'position' => $position,
        'memo' => $memo,
        'bgcolor' => $bgcolor,
        'tabid' => $tabid
    ];
}
$stmt_memo->close();
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
            <?php if (isset($_SESSION['error']) && $_SESSION['error'] === 'tab_count'): ?>
                <p class="error">登録できるタブは10件までです。新たにタブを追加したい場合は他のタブを削除してください</p>
            <?php endif; ?>
            <ul id="tabArea" class="tab-menu">
                <?php foreach ($tabs as $tab): ?>
                    <li class="tabs <?php echo h($tab['isactive']) ?>" data-tab-id="<?php echo h($tab['id']) ?>"><span class="tab-name"><?php echo h($tab['tab']) ?></span><button class="delete-tab">×</button></li>
                <?php endforeach; ?>
                <a href="add_tab.php" class="add-tab">＋</a>
            </ul>
            <ul id="sortableArea">
                <?php foreach ($memos as $memo): ?>
                <li id="<?php echo h($memo['id']); ?>" class="list <?php $bgClass = (!$memo['bgcolor']) ? 'aqua' : h($memo['bgcolor']); echo $bgClass ?>">
                    <span class="memoText"><?php echo h($memo['memo']); ?></span>
                    <div class="right-button">
                        <div class="aqua bgcolor-btn" data-value="aqua"></div>
                        <div class="khaki bgcolor-btn" data-value="khaki"></div>
                        <div class="lightsalmon bgcolor-btn" data-value="lightsalmon"></div>
                        <div class="edit" data-id="<?php echo h($memo['id']); ?>">編集</div>
                        <div class="delete" data-id="<?php echo h($memo['id']); ?>">削除</div>
                    </div>
                </li>
                <?php endforeach; ?>
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
                <?php if (isset($_SESSION['error']) && $_SESSION['error'] === 'memo_count'): ?>
                    <p class="error">1つのタブに登録できるメモは25件までです。新たにメモを追加したい場合は他のメモを削除してください</p>
                <?php endif; ?>
            </form>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="script.js"></script>
</body>
</html>