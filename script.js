$(function() {
    // ページが読み込まれたときの処理
    $(document).ready(function() {
        // メモ並び順の取得
        let sortedMemoIds = getSortOrder();
        // メモ並び順の保存
        setSortOrder(sortedMemoIds);
        // タブ並び順の取得
        let sortedTabIds = getTabSortOrder();
        // タブ並び順の保存
        setTabSortOrder(sortedTabIds);
    });

    // メモの並び順が変更されたときの処理
    $('#sortableArea').sortable({
        // 並べ替えの即時反映
        update: function(event, ui) {
            // メモ並び順の取得
            let sortedMemoIds = getSortOrder();
            // console.log(sortedMemoIds);
            // メモ並び順の保存
            setSortOrder(sortedMemoIds);
        }
    });

    // リストの背景色を変更する処理
    $('.bgcolor-btn').click(function() {
        let listItem = $(this).closest('li'); // 編集ボタンに最も近い親要素のliを取得
        let listId = listItem.attr('id'); // 上記liのidを取得
        // console.log(listId);
        let listClass = listItem.attr('class'); // 上記liのclassを取得
        // console.log(listClass);
        let buttonValue = $(this).data('value'); // 押下したボタンのvalueを文字列で取得
        // console.log(buttonValue);

        $.ajax({
            url: 'update_bgcolor.php',
            method: 'POST',
            dataType: 'json',
            data: {
                id: listId,
                bgcolor: buttonValue
            },
            success: function(response) {
                if (response.status === 'success') {
                    // 背景色の更新
                    $('#' + listId).removeClass(listClass).addClass('list ' + response.bgcolor + ' ui-sortable-handle');
                } else {
                    alert('メモリストの背景色を更新できませんでした');
                }
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    });

    // メモを編集するときの処理
    $('.edit').click(function() {
        let listItem = $(this).closest('li'); // 編集ボタンに最も近い親要素のliを取得
        let memoId = $(this).data('id'); // 編集するメモのIDを取得
        let memoText = listItem.find('.memoText').text(); // liの子孫要素のspanのテキストを取得

        let editText = prompt('メモを編集してください(100文字まで): ', memoText);
        while (editText.trim() === '' || editText.length > 100) {
            alert('入力する文字数は1文字以上100文字以下にしてください');
            editText = prompt('メモを編集してください(100文字まで): ', memoText);
        }

        $.ajax({
            url: 'update_memo.php',
            method: 'POST',
            dataType: 'json',
            data: {
                id: memoId,
                memo: editText
            },
            success: function(response) {
                if (response.status === 'success') {
                    listItem.find('.memoText').text(response.message);
                } else if (response.status === 'blank') {
                    alert(response.message);
                } else if (response.status === 'string') {
                    alert(response.message);
                }
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    });

    // メモを削除するときの処理
    $('.delete').click(function() {
        let memoId = $(this).data('id'); // 削除するメモのIDを取得
        // console.log(memoId);

        $.ajax({
            url: 'delete_memo.php',
            type: 'POST',
            data: { id: memoId }, // 削除するメモのIDを送信
            success: function(response) {
                if (response === 'success') { // 削除成功時の処理
                    // リストから該当のメモを削除
                    $('li[id="' + memoId + '"]').remove();
                    // 並び順の再取得
                    let sortedMemoIds = getSortOrder();
                    // 並び順の再保存
                    setSortOrder(sortedMemoIds);
                } else { // 削除失敗時の処理
                    alert('メモの削除に失敗しました');
                }
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    });

    $('#sortableArea').disableSelection();



    // タブ切り替え
    $('.tabs').on('click', '.tab-name', function() {
        if ($(this).parent().hasClass('inactive')) {
            let tabId = $(this).parent().data('tab-id'); // 切り替えるタブのIDを取得
            $.ajax({
                url: 'change_tab.php',
                type: 'POST',
                data: { id: tabId }, // 切り替えるタブのIDを送信
                success: function(response) {
                    if (response === 'success') { // 切り替え成功時の処理
                        window.location.href = "index.php";
                    } else {
                        alert('タブの切り替えに失敗しました');
                    }
                },
                error: function() {
                    alert('サーバに接続できませんでした');
                }
            });
        }
    });

    // タブ名編集
    $('.tabs').on('dblclick', '.tab-name', function() {
        let tabId = $(this).parent().data('tab-id'); // 保存するタブ名のIDを取得
        // console.log(tabId);
        let $span = $(this);
        let currentName = $span.text();
        let $input = $('<input type="text" maxlength="20">').val(currentName);

        $span.replaceWith($input);
        $input.focus();
        $input.select();

        // Enterかフォーカス外れで保存処理
        $input.on('blur keydown', function(e) {
            if (e.type === 'blur' || e.key === 'Enter') {
                let newName = $input.val(); // 保存する新しいタブ名を取得

                $.ajax({
                    url: 'update_tab.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: tabId,
                        tab: newName
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            let $newSpan = $('<span class="tab-name"></span>').text(response.message);
                            $input.replaceWith($newSpan);
                        } else if (response.status === 'blank') {
                            let $currentSpan = $('<span class="tab-name"></span>').text(currentName);
                            $input.replaceWith($currentSpan);
                        }
                    },
                    error: function() {
                        alert('サーバに接続できませんでした');
                    }
                });
            }
        });

    });

    // タブ削除
    $('.tabs').on('click', '.delete-tab', function() {
        let tabId = $(this).parent().data('tab-id'); // 削除するタブのIDを取得
        // console.log(tabId);

        // 指定したタブに紐づくメモの個数を取得する
        $.ajax({
            url: 'count_memo.php',
            method: 'POST',
            dataType: 'json',
            data: { id: tabId },
            success: function(response) {
                if (response.status === 'success') {
                    let count = response.count;
                    if (count > 0) {
                        if (confirm('このタブを削除しますか？(このタブに登録したメモも一緒に削除されます)')) {
                            deleteTab(tabId);
                        }
                    } else {
                        deleteTab(tabId);
                    }
                } else {
                    alert('タブに紐づくメモの個数を取得できませんでした');
                }
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });

    });

    // タブ並び順が変更されたときの処理
    $('#tabArea').sortable({
        axis: 'x',
        cancel: '.delete-tab, .add-tab',
        // 並べ替えの即時反映
        update: function(event, ui) {
            // タブ並び順の取得
            let sortedTabIds = getTabSortOrder();
            // console.log(sortedTabIds);
            // タブ並び順の保存
            setTabSortOrder(sortedTabIds);
        }
    });

    $('#tabArea').disableSelection();


    // 現在のメモ並び順を取得
    function getSortOrder() {
        let sorted = $('#sortableArea').sortable('toArray', { attribute: 'id'});
        return sorted;
    }

    // 現在のメモ並び順を保存する
    function setSortOrder(sortedMemoIds) {
        $.ajax({
            url: 'save_memo_order.php',
            type: 'POST',
            data: { order: sortedMemoIds },
            success: function(response) {
                // console.log(response);
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    }

    // 現在のタブ並び順を取得
    function getTabSortOrder() {
        let sorted = $('#tabArea').sortable('toArray', { attribute: 'data-tab-id'});
        return sorted;
    }

    // 現在のタブ並び順を保存する
    function setTabSortOrder(sortedTabIds) {
        $.ajax({
            url: 'save_tab_order.php',
            type: 'POST',
            data: { order: sortedTabIds },
            success: function(response) {
                // console.log(response);
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    }

    // 指定したタブを削除する
    function deleteTab(tabId) {
        $.ajax({
            url: 'delete_tab.php',
            method: 'POST',
            dataType: 'json',
            data: { id: tabId }, // 削除するタブのIDを送信
            success: function(response) {
                if (response.status === 'success') { // 削除成功時の処理
                    window.location.reload();
                } else if (response.status === 'fail') {  // タブ1個以下のときのエラーメッセージ
                    alert(response.message);
                } else { // 削除失敗時の処理(その他)
                    alert('タブの削除に失敗しました');
                }
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    }

});