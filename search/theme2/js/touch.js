
/*UI setting*/
$(function(){
	// イベント登録
	$('.select').on('touchstart touchmove touchend', function(event){
		// タッチスタートでフラグを設定
		if ('touchstart' == event.type){
			$(this).attr('data-touchstarted', '');
			return;
		}
		// タッチムーブが発生したら、フラグを消す。(タップと判定させない為)
		if ('touchmove' == event.type){
			$(this).removeAttr('data-touchstarted');
			return;
		}
		// タッチエンド時にフラグがあれば、タップと判定する。
		if ('undefined' != typeof $(this).attr('data-touchstarted')){
			// ここでタップ時のイベントハンドラを呼び出す。
			onListItemTapped2.call(this, event);
			// フラグを削除
			$(this).removeAttr('data-touchstarted');
		}
	});
	// PCからのクリックにも対応
	$('.select').on('click', function(event){
		onListItemTapped2.call(this, event);
	});
});