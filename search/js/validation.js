var shopUrl = baseurl+"/api/";
function validEmptyKana(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if(thisis.val().match(/^[ァ-ン　ー]+$/)){
			$("#"+name+"Valid").remove();
			thisis.parent(thisis).removeClass("has-error");
			thisis.parent(thisis).addClass("has-success");
			thisis.css("background-color","#efe");
			for(var i=0;i<error.length;i++){
				if(name+"NotKana" == error[i]){
					error.splice(i,1) ;
				}
			}
		}else{
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">全角カタカナで入力してください</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"NotKana") == -1){
			  // 存在しない
			　error.push(name+"NotKana");
			}
		}
	}
}
function validEmpty(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		$("#"+name+"Valid").remove();
		thisis.parent(thisis).removeClass("has-error");
		thisis.parent(thisis).addClass("has-success");
		thisis.css("background-color","#efe");
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
	}
}
function validEmptyDiff(name,description,diff){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val() != $("[name="+diff+"]").val()){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が一致しません</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"Diff") == -1){
			  // 存在しない
			　　error.push(name+"Diff");
			}
		}else{
			$("#"+name+"Valid").remove();
			thisis.parent(thisis).removeClass("has-error");
			thisis.parent(thisis).addClass("has-success");
			thisis.css("background-color","#efe");
			for(var i=0;i<error.length;i++){
				if(name+"Diff" == error[i]){
					error.splice(i,1) ;
				}
			}
		}
	}
}
function validEmptyAndNumeric(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^[0-9]+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"で数字以外の文字が入力されています</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"Numeric") == -1){
			  // 存在しない
			　　error.push(name+"Numeric");
			}
		}else{
			$("#"+name+"Valid").remove();
			thisis.parent(thisis).removeClass("has-error");
			thisis.parent(thisis).addClass("has-success");
			thisis.css("background-color","#efe");
			for(var i=0;i<error.length;i++){
				if(name+"Numeric" == error[i]){
					error.splice(i,1) ;
				}
			}
		}
	}
}
function validEmptyAndZeroZero(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		$.post(shopUrl+"prefix",{"no":$("[name="+name+"]").val()},function(data){
			if(data != "OK"){
				$("#"+name+"Valid").remove();
				var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"は、市外局番として正しくありません。</span>");
				thisis.before(cusName);
				thisis.parent(thisis).removeClass("has-success");
				thisis.parent(thisis).addClass("has-error");
				thisis.css("background-color","#fee");
				if (error.indexOf(name+"NotOuterNum") == -1){
				  // 存在しない
				　　error.push(name+"NotOuterNum");
				}
			}else{
				for(var i=0;i<error.length;i++){
					if(name+"NotOuterNum" == error[i]){
						error.splice(i,1) ;
					}
				}
				if($("[name="+name+"]").val().match(/^0+[0-9]+$/) ===null){
					$("#"+name+"Valid").remove();
					var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"で数字以外の文字が入力されています</span>");
					thisis.before(cusName);
					thisis.parent(thisis).removeClass("has-success");
					thisis.parent(thisis).addClass("has-error");
					thisis.css("background-color","#fee");
					if (error.indexOf(name+"Numeric") == -1){
					  // 存在しない
					　　error.push(name+"Numeric");
					}
				}else{
					for(var i=0;i<error.length;i++){
						if(name+"Numeric" == error[i]){
							error.splice(i,1) ;
						}
					}
					if(thisis.val().match(/^[0]+$/)){
						$("#"+name+"Valid").remove();
						var cusName = $("<span id=\""+name+"Valid\" class=\"error\">ゼロのみの入力は出来ません。</span>");
						thisis.before(cusName);
						thisis.parent(thisis).removeClass("has-success");
						thisis.parent(thisis).addClass("has-error");
						thisis.css("background-color","#fee");
						if (error.indexOf(name+"Zero") == -1){
						  // 存在しない
						　　error.push(name+"Zero");
						}
					}else{
						$("#"+name+"Valid").remove();
						thisis.parent(thisis).removeClass("has-error");
						thisis.parent(thisis).addClass("has-success");
						thisis.css("background-color","#efe");
						for(var i=0;i<error.length;i++){
							if(name+"Zero" == error[i]){
								error.splice(i,1) ;
							}
						}
					}
				}
			}
		});
	}
}
function validEmptyAndZero(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^[0-9]+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"で数字以外の文字が入力されています</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"Numeric") == -1){
			  // 存在しない
			　　error.push(name+"Numeric");
			}
		}else{

			for(var i=0;i<error.length;i++){
				if(name+"Numeric" == error[i]){
					error.splice(i,1) ;
				}
			}
			if(thisis.val().match(/^[0]+$/)){
				$("#"+name+"Valid").remove();
				var cusName = $("<span id=\""+name+"Valid\" class=\"error\">ゼロのみの入力は出来ません。</span>");
				thisis.before(cusName);
				thisis.parent(thisis).removeClass("has-success");
				thisis.parent(thisis).addClass("has-error");
				thisis.css("background-color","#fee");
				if (error.indexOf(name+"Zero") == -1){
				  // 存在しない
				　　error.push(name+"Zero");
				}
			}else{
				$("#"+name+"Valid").remove();
				thisis.parent(thisis).removeClass("has-error");
				thisis.parent(thisis).addClass("has-success");
				thisis.css("background-color","#efe");
				for(var i=0;i<error.length;i++){
					if(name+"Zero" == error[i]){
						error.splice(i,1) ;
					}
				}
			}
		}
	}
}
function validEmptyAndTel(name,description,joinColum,joinColum2){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^[0-9]+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"で数字以外の文字が入力されています</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"Numeric") == -1){
			  // 存在しない
			　　error.push(name+"Numeric");
			}
		}else{
			for(var i=0;i<error.length;i++){
				if(name+"Numeric" == error[i]){
					error.splice(i,1) ;
				}
			}
			if($("[name="+joinColum+"]").val().match(/^[0]+$/) || $("[name="+joinColum2+"]").val().match(/^[0]+$/) || thisis.val().match(/^[0]+$/)){
				$("#"+name+"Valid").remove();
				var cusName = $("<span id=\""+name+"Valid\" class=\"error\">ゼロのみの入力は出来ません。</span>");
				thisis.before(cusName);
				thisis.parent(thisis).removeClass("has-success");
				thisis.parent(thisis).addClass("has-error");
				thisis.css("background-color","#fee");
				if (error.indexOf(name+"Zero") == -1){
				  // 存在しない
				　　error.push(name+"Zero");
				}
			}else{
					for(var i=0;i<error.length;i++){
						if(name+"Zero" == error[i]){
							error.splice(i,1) ;
						}
					}
				var telno = $("[name="+joinColum+"]").val()+"-"+$("[name="+joinColum2+"]").val()+"-"+thisis.val();
				if(telno.match(/^0\d{1,4}-\d{1,4}-\d{3,4}$/)){
					$("#"+name+"Valid").remove();
					thisis.parent(thisis).removeClass("has-error");
					thisis.parent(thisis).addClass("has-success");
					thisis.css("background-color","#efe");
				}else{
					for(var i=0;i<error.length;i++){
						if(name+"NotTel" == error[i]){
							error.splice(i,1) ;
						}
					}
					$("#"+name+"Valid").remove();
					var cusName = $("<span id=\""+name+"Valid\" class=\"error\">電話番号として正しくありません。</span>");
					thisis.before(cusName);
					thisis.parent(thisis).removeClass("has-success");
					thisis.parent(thisis).addClass("has-error");
					thisis.css("background-color","#fee");
					if (error.indexOf(name+"NotTel") == -1){
					  // 存在しない
					　　error.push(name+"NotTel");
					}
				}
			}
		}
	}
}
function validEmptyAndZip(name,description,joinColum){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^[0-9]+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"で数字以外の文字が入力されています</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"Numeric") == -1){
			  // 存在しない
			　　error.push(name+"Numeric");
			}
		}else{
			for(var i=0;i<error.length;i++){
				if(name+"Numeric" == error[i]){
					error.splice(i,1) ;
				}
			}
			var zipcode = $("[name="+joinColum+"]").val()+thisis.val();
			$.post(shopUrl+"user/zip/",{"zip":zipcode},function(data){
				console.log(data);
				if(data <=0){
					$("#"+name+"Valid").remove();
					var cusName = $("<span id=\""+name+"Valid\" class=\"error\">郵便番号として正しくありません。</span>");
					thisis.before(cusName);
					thisis.parent(thisis).removeClass("has-success");
					thisis.parent(thisis).addClass("has-error");
					thisis.css("background-color","#fee");
					if (error.indexOf(name+"NotZip") == -1){
					  // 存在しない
					　　error.push(name+"NotZip");
					}
				}else{
					$("#"+name+"Valid").remove();
					thisis.parent(thisis).removeClass("has-error");
					thisis.parent(thisis).addClass("has-success");
					thisis.css("background-color","#efe");
					for(var i=0;i<error.length;i++){
						if(name+"NotZip" == error[i] || name+"Numeric" == error[i]){
							error.splice(i,1) ;
						}
					}
				}
			});
		}
	}
}
function validEmptyAndMailFormat(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^([a-zA-Z0-9])+([a-zA-Z0-9¥._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9¥._-]+)+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が正しく入力されていません。</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"MailFormat") == -1){
			  // 存在しない
			　　error.push(name+"MailFormat");
			}
		}else{
			$("#"+name+"Valid").remove();
			thisis.parent(thisis).removeClass("has-error");
			thisis.parent(thisis).addClass("has-success");
			thisis.css("background-color","#efe");
			for(var i=0;i<error.length;i++){
				if(name+"MailFormat" == error[i]){
					error.splice(i,1) ;
				}
			}
		}
	}
}

function validEmptyAndMailFormatAndDiff(name,description,diff){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^([a-zA-Z0-9])+([a-zA-Z0-9¥._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9¥._-]+)+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が正しく入力されていません。</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"MailFormat") == -1){
			  // 存在しない
			　　error.push(name+"MailFormat");
			}
		}else{
			for(var i=0;i<error.length;i++){
				if(name+"MailFormat" == error[i]){
					error.splice(i,1) ;
				}
			}
			if($("[name="+name+"]").val() != $("[name="+diff+"]").val()){
				$("#"+name+"Valid").remove();
				var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が一致しません</span>");
				thisis.before(cusName);
				thisis.parent(thisis).removeClass("has-success");
				thisis.parent(thisis).addClass("has-error");
				thisis.css("background-color","#fee");
				if (error.indexOf(name+"MailDiff") == -1){
				  // 存在しない
				　　error.push(name+"MailDiff");
				}
			}else{
				$("#"+name+"Valid").remove();
				thisis.parent(thisis).removeClass("has-error");
				thisis.parent(thisis).addClass("has-success");
				thisis.css("background-color","#efe");
				for(var i=0;i<error.length;i++){
					if(name+"MailDiff" == error[i]){
						error.splice(i,1) ;
					}
				}
			}
		}
	}
}
function validEmptyAndMailFormatAndSame(name,description){
	var thisis = $("[name="+name+"]");
	if($("[name="+name+"]").val() ==""){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		thisis.before(cusName);
		thisis.parent(thisis).removeClass("has-success");
		thisis.parent(thisis).addClass("has-error");
		thisis.css("background-color","#fee");
		if (error.indexOf(name+"Empty") == -1){
		  // 存在しない
		　　error.push(name+"Empty");
		}
	}else{
		for(var i=0;i<error.length;i++){
			if(name+"Empty" == error[i]){
				error.splice(i,1) ;
			}
		}
		if($("[name="+name+"]").val().match(/^([a-zA-Z0-9])+([a-zA-Z0-9¥._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9¥._-]+)+$/) ===null){
			$("#"+name+"Valid").remove();
			var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が正しく入力されていません。</span>");
			thisis.before(cusName);
			thisis.parent(thisis).removeClass("has-success");
			thisis.parent(thisis).addClass("has-error");
			thisis.css("background-color","#fee");
			if (error.indexOf(name+"MailFormat") == -1){
			  // 存在しない
			　　error.push(name+"MailFormat");
			}
		}else{
			for(var i=0;i<error.length;i++){
				if(name+"MailFormat" == error[i]){
					error.splice(i,1) ;
				}
			}
			var mailaddr = $("[name="+name+"]").val();
			$.post(shopUrl+"user/same/",{"mail":mailaddr},function(data){
				console.log(data);
				if(data >= 1){
					$("#"+name+"Valid").remove();
					var cusName = $("<span id=\""+name+"Valid\" class=\"error\">同一の"+description+"で登録されています。</span>");
					thisis.before(cusName);
					thisis.parent(thisis).removeClass("has-success");
					thisis.parent(thisis).addClass("has-error");
					thisis.css("background-color","#fee");
					if (error.indexOf(name+"MailSame") == -1){
					  // 存在しない
					　　error.push(name+"MailSame");
					}
				}else{
					$("#"+name+"Valid").remove();
					thisis.parent(thisis).removeClass("has-error");
					thisis.parent(thisis).addClass("has-success");
					thisis.css("background-color","#efe");
					for(var i=0;i<error.length;i++){
						if(name+"MailSame" == error[i]){
							error.splice(i,1) ;
						}
					}
				}
			});
		}
	}
}
function validPaymentMethod(name,description){
	if($("[name="+name+"]:checked").val() ==null){
		$("#"+name+"Valid").remove();
		var cusName = $("<span id=\""+name+"Valid\" class=\"error\">"+description+"が入力されていません.</span>");
		$("#payment_method").before(cusName);
		error.push(name+"Payment");
	}else{
		$("#"+name+"Valid").remove();
		for(var i=0;i<error.length;i++){
			if(name+"Payment" == error[i]){
				error.splice(i,1) ;
			}
		}
	}
}
