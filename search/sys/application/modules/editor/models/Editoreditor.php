<?php
	class Model_Editoreditor extends Model_Editorgeneral {

		//ユーザーリストの取得
		public function getUsers($kind =NULL,$keyword=NULL,$Authority=NULL,$division=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT DISTINCT u.*
						FROM ImageUser u";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($Authority)){
				$whereArr[] = "Authority=".$Authority;
			}
			if(!empty($division)){
				$whereArr[] = "division=".$division;
			}
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = mb_split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "concat(u.company, u.name, u.name2, u.kana, u.kana2, u.mail, u.tel, u.tel2, u.tel3) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "parent=".$parent;
			$whereArr[] = "kind=2";
			$whereArr[] = "outsource=0";
			//WHERE句を生成
			if(!empty($whereArr)){
				$sql.= " WHERE ".implode(" and ",$whereArr);
				$where = implode(" and ",$whereArr);
			}
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " GROUP BY u.id";
			$sql.= " ORDER BY `id` DESC";
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			$sql2 = "SELECT COUNT(*) AS n FROM ImageUser u WHERE ".$where;
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		//ユーザーリストの取得
		public function getOutSource($kind =NULL,$keyword=NULL,$factory=NULL,$division=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT DISTINCT u.*
						FROM ImageUser u";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($factory)){
				$whereArr[] = "factory=".$factory;
			}
			if(!empty($division)){
				$whereArr[] = "division=".$division;
			}
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = mb_split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "concat(u.company, u.name, u.name2, u.kana, u.kana2, u.mail, u.tel, u.tel2, u.tel3) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "parent=".$parent;
			$whereArr[] = "kind=2";
			$whereArr[] = "outsource=1";
			//WHERE句を生成
			if(!empty($whereArr)){
				$sql.= " WHERE ".implode(" and ",$whereArr);
				$where = implode(" and ",$whereArr);
			}
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " GROUP BY u.id";
			$sql.= " ORDER BY `id` DESC";
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			$sql2 = "SELECT COUNT(*) AS n FROM ImageUser u WHERE ".$where;
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}

		//ユーザー詳細情報の取得（ポイント）
		public function getUserDetail($id){
			$sql = "SELECT u.*, k.name AS kindName, sum( p.pin )-sum( p.pout ) AS point, MAX( p.created ) AS created
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`pin` ,`pout` ,  `balance` ,  `customerId` ,  `created`
							FROM cutomerPoint
							ORDER BY  `created`
						) AS p ON p.customerId = u.id
						LEFT JOIN ImageUserKind k ON u.kind = k.id";
			$sql.= " WHERE u.id={$id}";
			$sql.= " GROUP BY u.id";
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		//設定を読み込み
		public function setting($id){
			//会社設定
			$company = $this->_db->fetchAll(
				$this->_db->select()
				->from("company")
				->where("parent=?",$id)
			);
			/*
			//送料を読み込み
			$shipping = $this->_db->fetchAll(
				$this->_db->select()
				->from("shipping")
				->where("parent=?",$id)
			);
			//サイト設定
			$global = $this->_db->fetchAll(
				$this->_db->select()
				->from("global")
				->where("parent=?",$id)
			);
			//ポイント設定
			$point = $this->_db->fetchAll(
				$this->_db->select()
				->from("point")
				->where("parent=?",$id)
			);
			//配達日設定
			$delivery = $this->_db->fetchAll(
				$this->_db->select()
				->from("delivery")
				->where("parent=?",$id)
			);
			//配達日設定
			$tax = $this->_db->fetchAll(
				$this->_db->select()
				->from("tax")
				->where("parent=?",$id)
			);
			//決済方法設定
			$payment = $this->_db->fetchAll(
				$this->_db->select()
				->from("payment_method")
				->where("parent=?",$id)
			);
			//割引・特別料金設定
			$discount = $this->_db->fetchAll(
				$this->_db->select()
				->from("discount")
				->where("parent=?",$id)
			);
			//用途設定
			$youto = $this->_db->fetchAll(
				$this->_db->select()
				->from("youto")
				->where("parent=?",$id)
			);
			*/

			return array(
				"company"=>$company[0],
				/*
				"youto"=>$youto,
				"shipping"=>$shipping,
				"global"=>$global[0],
				
				"point"=>$point[0],
				"delivery"=>$delivery[0],
				"payment"=>$payment,
				"tax"=>$tax[0],
				"discount"=>$discount[0]
				*/
			);
		}
		public function sendmail($autoRes = "",$subject,$body,$fromMail,$fromName,$toMail,$toName,$smtp,$AddAttachment = ""){

			###################################################################
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");
			include("PHPMailer/class.phpmailer.php");
			include("PHPMailer/class.smtp.php");
			date_default_timezone_set("Asia/Tokyo");
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth   =$smtp['SMTPcertificate'];
			$mail->SMTPSecure = $smtp['SMTPsecur'];
			$mail->Host       =$smtp['SMTPhost'];
			$mail->Port       = $smtp['SMTPport'];
			$mail->Username   = $smtp['SMTPuser'];
			$mail->Password   = $smtp['SMTPpass'];
			$mail->CharSet    = "iso-2022-jp";
			$mail->Encoding   = "7bit";
			//$mail->SMTPDebug = 1;
			$mail->IsHTML(false);
			$mail->From       = $fromMail;//送信者メール
			$mail->FromName   = mb_encode_mimeheader(mb_convert_encoding($fromName, "JIS", "utf-8"));
			$mail->AddReplyTo($fromMail, mb_encode_mimeheader(mb_convert_encoding($fromMail, "JIS", "utf-8")));

			if($autoRes){
				$mail->AddBCC($fromMail, mb_encode_mimeheader(mb_convert_encoding($fromMail, "JIS", "utf-8")));
			}
			$mail->Subject    = mb_convert_encoding($subject, "JIS", "utf-8");
			$mail->Body       = mb_convert_encoding($body, "JIS", "utf-8");
			$mail->AddAddress($toMail, mb_encode_mimeheader(mb_convert_encoding($toMail, "JIS", "utf-8")));

			if($AddAttachment !=""){
				$mail->AddAttachment($AddAttachment);
			}
			if(!$mail->Send()) {
				return "error";
			} else {
				return true;
			}
			###################################################################
		}
	}
?>
