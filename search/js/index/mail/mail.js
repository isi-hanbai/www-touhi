
var error = new Array();
$(function(){
	//バリデーション
	$("[name=name]").on("blur",function(){
		validEmpty("name","お名前 ");
	});
	$("[name=mail]").on("blur",function(){
		validEmptyAndMailFormat("mail","メールアドレス ");
	});
	$("[name=tel]").on("blur",function(){
		validEmpty("tel","電話番号");
	});
	$("[name=message]").on("blur",function(){
		validEmpty("message","お問合せ内容 ");
	});
	$("#sub").on("click",function(){
		validEmpty("name","お名前 ");
		validEmptyAndMailFormat("mail","メールアドレス ");
		validEmpty("tel","電話番号 ");
		validEmpty("message","お問合せ内容 ");
		if(error.length >0){
			var str = "";
			str+= "入力に誤りがあります。下記の項目を修正して再度ボタンを押してください。\n";
			for(var i =0;i<error.length;i++){
				if(error[i] =="nameEmpty"){
					str+="「お名前」を入力してください。\n";
				}
				if(error[i] =="mailEmpty"){
					str+="「メールアドレス」を入力してください。\n";
				}
				if(error[i] =="telEmpty"){
					str+="「電話番号」を入力してください。\n";
				}
				if(error[i] =="messageEmpty"){
					str+="「お問合せ内容」を入力してください。\n";
				}
			}
			alert(str);
		}else{
			if(window.confirm("メールを送信します。内容を変更される場合はキャンセルボタンを押してください。")){
				$("#form").submit();
			}
		}
	});
});
