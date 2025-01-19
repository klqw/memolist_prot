$(function() {
    // ページが読み込まれたときの処理
    $(document).ready(function() {
        // 並び順の取得
        let sortedIds = getSortOrder();
        // 並び順の保存
        setSortOrder(sortedIds);
    });

    // メモの並び順が変更されたときの処理
    $('#sortableArea').sortable({
        // 並べ替えの即時反映
        update: function(event, ui) {
            // 並び順の取得
            let sortedIds = getSortOrder();
            // console.log(sortedIds);

            // 並び順の保存
            setSortOrder(sortedIds);
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
                    let sortedIds = getSortOrder();
                    // 並び順の再保存
                    setSortOrder(sortedIds);
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

    // 現在の並び順を取得
    function getSortOrder() {
        let sorted = $('#sortableArea').sortable('toArray', { attribute: 'id'});
        return sorted;
    }

    // 現在の並び順を保存する
    function setSortOrder(sortedIds) {
        $.ajax({
            url: 'save_order.php',
            type: 'POST',
            data: { order: sortedIds },
            success: function(response) {
                // console.log(response);
            },
            error: function() {
                alert('サーバに接続できませんでした');
            }
        });
    }

});