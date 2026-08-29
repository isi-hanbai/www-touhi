$(function(){

	//添付しているファイルを削除
	$(document).on("click",".deleteFile",function(){
		var This = $(this);
		var file = $(this).data("filename");
		if(window.confirm("添付ファイルを削除してもいいですか？")){
			$.post(baseurl+"/api/mail/deletetmp/",{file:$(this).data("filename"),id:$("[name='id']").val(),type:"ImageUser"},function(data){
				console.log(data);
				if(data == "OK"){
					This.parent().remove();
					$('[name="tmp[]"][value="'+file+'"]').remove();
				}
			});
		}
	});

	//資料アップロード
	$("#uploadBtn").on("click",function(){
		$("#userfile").click();
	});
	$("#userfile").on("change",function(){
		$("#imageupload").submit();
	});


  //分類に応じた項目を表示
  if($("[name=class]").val() !=""){
    getFacilityOfValue();
  }
  $("[name=class]").on("change",function(){
    getFacilityOfValue();
  });
	$("#pwactive").on("click",function(){
		if($(this).attr("checked",true)){
			$("[name=pw]").attr("disabled",false).val("").focus();
		}else{
			$("[name=pw]").attr("disabled",true);
		}
	});
});
$(function(){
  //住所を検索
  $("[name=zip2]").on("blur",function(){
    var d = $("[name=zip]").val()+$("[name=zip2]").val();
    var key = $("[name=zip]");
    $.post(baseurl+"/api/addr/",{"d":d},function(data){
      console.log(data);
      if(data == "NotExsist" || data == "AccessDenied"){
      }else{
        var addrs =  JSON.parse(data);
        $("[name=pref]").val(addrs[0]['pref']);
        $("[name=addr]").focus();
        $("[name=addr]").val(addrs[0]['city']+addrs[0]['town']);
        validEmpty("pref","都道府県");
        validEmpty("addr","市区町村、番地");
      }
    });
  });
	$("#pwactive").on("click",function(){
		if($(this).attr("checked",true)){
			$("[name=pw]").attr("disabled",false).val("").focus();
		}else{
			$("[name=pw]").attr("disabled",true);
		}
	});
});
//バリデーション
var error =new Array;
$(function(){
	$("[name=name]").on("blur",function(){
		validEmpty("name","医療機関名");
	});
	$("[name=kana]").on("blur",function(){
		validEmptyKana("kana","フリガナ");
	});
	$("[name=mail]").on("blur",function(){
		validEmptyAndMailFormatAndSame("mail","メールアドレス");
	});
	$("[name=tel]").on("blur",function(){
		validEmptyAndZero("tel","電話番号");
	});
	$("[name=tel2]").on("blur",function(){
		validEmptyAndZero("tel2","電話番号2");
	});
	$("[name=tel3]").on("blur",function(){
		validEmptyAndTel("tel3","電話番号3","tel","tel2");
	});
	$("[name=zip]").on("blur",function(){
		validEmptyAndZero("zip","郵便番号");
	});
	$("[name=zip2]").on("blur",function(){
		validEmptyAndZip("zip2","郵便番号2","zip");
	});
	$("[name=pref]").on("blur",function(){
		validEmpty("pref","都道府県");
	});
	$("[name=addr]").on("blur",function(){
		validEmpty("addr","市区町村、番地");
	});
	$("[name=pw]").on("blur",function(){
		validEmpty("pw","パスワード");
	});
	$("#sub").on("click",function(){
		validEmpty("name","医療機関名");
		validEmptyKana("kana","フリガナ");
		//validEmptyAndMailFormatAndSame("mail","メールアドレス");
		validEmptyAndMailFormat("mail","メールアドレス");
		validEmptyAndZero("tel","電話番号1");
		validEmptyAndZero("tel2","電話番号2");
		validEmptyAndTel("tel3","電話番号3","tel","tel2");
		validEmptyAndZero("zip","郵便番号1");
		validEmptyAndZip("zip2","郵便番号2","zip");
		validEmpty("pref","都道府県");
		validEmpty("addr","市区町村、番地");
		validEmpty("pw","パスワード");
		if(error.length >0){
			var str = "";
			str+= "入力に誤りがあります。下記の項目を修正して再度ボタンを押してください。\n";
			for(var i =0;i<error.length;i++){
				if(error[i] =="nameEmpty"){
					str+="「医療機関名」を入力してください。\n";
				}
				if(error[i] =="kanaEmpty"){
					str+="「フリガナ」を入力してください。\n";
				}
				if(error[i] =="kanaNotKana"){
					str+="「フリガナ」は全角カタカナで入力してください。\n";
				}
				if(error[i] =="mailEmpty"){
					str+="「メールアドレス」を入力してください。\n";
				}
				if(error[i] =="mailMailFormat"){
					str+="「メールアドレス」の形式を正しく入力してください。\n";
				}
				if(error[i] =="mailMailSame"){
					str+="この「メールアドレス」はすでに登録されています。\n";
				}
				if(error[i] =="telEmpty"){
					str+="「電話番号1」を入力してください。\n";
				}
				if(error[i] =="telZero"){
					str+="「電話番号1」はゼロのみの入力は出来ません。\n";
				}
				if(error[i] =="tel2Empty"){
					str+="「電話番号2」を入力してください。\n";
				}
				if(error[i] =="telZero"){
					str+="「電話番号1」はゼロのみの入力は出来ません。\n";
				}
				if(error[i] =="tel3Empty"){
					str+="「電話番号3」を入力してください。\n";
				}
				if(error[i] =="tel3NotTel"){
					str+="電話番号として正しくありません\n";
				}
				if(error[i] =="tel3Zero"){
					str+="「電話番号3」はゼロのみの入力は出来ません。\n";
				}
				if(error[i] =="zipEmpty"){
					str+="「郵便番号１」を入力してください。\n";
				}
				if(error[i] =="zipZero"){
					str+="「郵便番号１」はゼロのみの入力は出来ません。\n";
				}
				if(error[i] =="zipNumeric"){
					str+="「郵便番号1」が半角数字ではありません\n";
				}
				if(error[i] =="zip2Empty"){
					str+="「郵便番号2」を入力してください。\n";
				}
				if(error[i] =="zip2NotZip"){
					str+="「郵便番号2」を入力してください。\n";
				}
				if(error[i] =="zip2Numeric"){
					str+="「郵便番号2」が半角数字ではありません\n";
				}
				if(error[i] =="prefEmpty"){
					str+="「都道府県」を選択してください。\n";
				}
				if(error[i] =="addrEmpty"){
					str+="「市区町村、番地」を入力してください。\n";
				}
				if(error[i] =="pwEmpty"){
					str+="「パスワード」を入力してください。\n";
				}
			}
			alert(str);
		}else{
			if(window.confirm("会員情報の編集を行います。内容を変更される場合はキャンセルボタンを押してください。")){
				$("#form").submit();
			}
		}
	});
});
function getFacilityOfValue(){
  //選択された分類に応じて項目を取得
  var user = $("[name=id]").val();

	console.log(user);
  $.get(baseurl+"/api/user/useroffacility/?id="+$("[name=class]").val()+"&user="+user,function(data){
    var json = JSON.parse(data);
		console.log(json);
    var str = "<div id=\"added\">";
		var arr = new Array("TEL","FAX","規模・定員","短期利用共同生活介護の提供","共同型指定認知症対応型通所介護の提供");
    for(var i=0;i<json.facility.length;i++){
      if(json.value.length >0){
        var vv = json.value[0][json.facility[i].facilty];
      }else{
        var vv = "";
      }
      if(json.facility[i]['sel'] !=1){
				if($.inArray(json.facility[i].name,arr) >= 0){
          str+= '<lebel>'+json.facility[i].name+'</label>';
					str+= '<div class="form-row">';
					str+= '<div class="col col-lg-3">';
					str+= '<textarea class="form-control requid-textarea" name="added['+json.facility[i].facilty+']">'+vv+'</textarea>';
					str+= '</div>';
					str+= '<div class="col">';
					str+= '</div>';
					str+= '</div>';
				}else{
          str+= '<lebel>'+json.facility[i].name+'</label>';
          str+= '<textarea class="form-control requid-textarea" name="added['+json.facility[i].facilty+']">'+vv+'</textarea>';
				}
      }
    }

    str+= '<div class="form-row">';
    for(var i=0;i<json.facility.length;i++){
      if(json.facility[i]['sel'] ==1){
        if(json.value.length >0){
          var vv = json.value[0][json.facility[i].facilty];
        }else{
          var vv = "";
        }
        str+= '<div class="col col-md-4 col-lg-3 col-xl-3""><label>'+json.facility[i].name+'</label>';
        var ansewer = {0:"未選択",1:"☓",2:"△",3:"○"};
        str+= '<select class="custom-select w-100 custom-select-lg" name="added['+json.facility[i].facilty+']">';
        for(var j =0;j<Object.keys(ansewer).length;j++){
          if(vv == j){
            str+= '<option value="'+j+'" selected>'+ansewer[j]+'</option>';
          }else{
            str+= '<option value="'+j+'">'+ansewer[j]+'</option>';
          }
        }
        str+= '</select>';
        str+= '</div>';
      }
    }
    str+= '</div>';

    str+= '</div>';
    if($("#added").length >0){
      $("#added").remove();
    }
    $("#addfacility").html(str);
  });
}

function getParam(){
  var result = {};
  if( 1 < window.location.search.length ){
    // 最初の1文字 (?記号) を除いた文字列を取得する
    var query = window.location.search.substring( 1 );
    // クエリの区切り記号 (&) で文字列を配列に分割する
    var parameters = query.split( '&' );
    for( var i = 0; i < parameters.length; i++ ){
      // パラメータ名とパラメータ値に分割する
      var element = parameters[ i ].split( '=' );
      var paramName = decodeURIComponent( element[0] );
      var paramValue = decodeURIComponent( element[1] );
      // パラメータ名をキーとして連想配列に追加する
      result[ paramName ] = paramValue;
    }
  }
  return result;
}
$(function(){
  $(document).on('input','.requid-textarea', function(e) {
    var lineHeight = parseInt("25px");
    var lines = ($(this).val() + '\n').match(/\n/g).length;
    $(this).height(lineHeight * lines);
  });
});
