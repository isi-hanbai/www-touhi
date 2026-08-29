<?php
	class Model_Adminorder extends Model_Admingeneral {

		public function getArray(){
			$arr = array();
			foreach($_GET as $k=>$v){
				if(is_array($v)){
					$arr2 = array();
					foreach($v as $kk=>$vv){
						$arr2[$kk] = htmlspecialchars(stripslashes($vv), ENT_QUOTES, 'UTF-8');
					}
					$arr[$k] = json_encode($arr2);
				}else{
					$arr[$k] = htmlspecialchars(stripslashes($v), ENT_QUOTES, 'UTF-8');
				}
			}
			return $arr;
		}
		//注文一覧の取得
		public function getorders($keyword=NULL,$p=0,$limit=1,$parent,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$sex,$birthM,$item,$membership,$point,$gene){
			//SQLコマンドを作成
			//$sql = "SELECT u.*
			//		FROM `order` u";

			$sql = "SELECT u.*,c.l,i.item
					FROM `order` u
					LEFT JOIN (
						SELECT MAX(limit_date) AS l,customerId,id AS cid
						FROM cutomerPoint
						GROUP BY customerId
					) c ON c.customerId = u.member_no
					LEFT JOIN (
						SELECT GROUP_CONCAT(itemId) as item,orderId , id AS iid
						FROM orderItem i
						GROUP BY orderId
					) i ON i.orderId = u.id";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "CONCAT(
						u.cus_name,
						u.cus_name2,
						u.cus_tel,
						u.cus_tel2,
						u.cus_tel3,
						u.cus_kana,
						u.cus_kana2,
						u.cus_zip,
						u.cus_zip2,
						u.cus_addr,
						u.cus_addr2,
						u.cus_campany,
						u.cus_div,
						u.cus_mail,
						u.cus_fax,
						u.delivery_name,
						u.delivery_name2,
						u.delivery_tel,
						u.delivery_tel2,
						u.delivery_tel3,
						u.delivery_kana,
						u.delivery_kana2,
						u.delivery_zip,
						u.delivery_zip2,
						u.delivery_addr,
						u.delivery_addr2,
						u.delivery_campay,
						u.delivery_div,
						u.GMOorder_id,
						u.member_no,
						u.delivery_mail
					) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "u.parent=".$parent;
			//性別
			if($sex >0){
				$whereArr[] = "u.cus_sex =".$sex;
			}
			//生月
			if($birthM >0){
				$whereArr[] = "u.cus_birthM =".$birthM;
			}
			//会員
			if($membership >0){
				$whereArr[] = "u.member_no!=''";
			}

			//商品で検索
			if($item){
				$whereArr[] = "i.item LIKE '%".$item."%'";
			}
			//ポイント有効期限
			if($point >0){
				if($point == 1){
					$whereArr[] = "(c.l<DATE_ADD(NOW(),INTERVAL 1 MONTH))";
				}elseif($point == 2){
					$whereArr[] = "(c.l<DATE_ADD(NOW(),INTERVAL 3 MONTH))";
				}
			}

			if($resive >0){
				$whereArr[] = "u.check_order IS NULL";
			}

			if($gene >0){
				if($gene == 1){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 20 AND 29";
				}elseif($gene == 2){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 30 AND 39";
				}elseif($gene == 3){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 40 AND 49";
				}elseif($gene == 4){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 50 AND 59";
				}elseif($gene == 5){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 60 AND 69";
				}elseif($gene == 6){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 70 AND 79";
				}elseif($gene == 7){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 80 AND 89";
				}elseif($gene == 8){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 90 AND 99";
				}elseif($gene == 9){
					$whereArr[] ="year(NOW()) - u.cus_birthY BETWEEN 10 AND 19";
				}
				//$whereArr[] = "u.check_order IS NULL";
			}




			if($pay >0){
				$whereArr[] = "u.check_payment_end IS NULL";
			}
			if($shipping >0){
				$whereArr[] = "u.check_delivery IS NULL";
			}
			if($n3d >0){
				//$whereArr[] = "(u.check_follow_mail IS NULL AND (u.order_date+ INTERVAL 3 DAY) < NOW())";
				$whereArr[] = "(u.check_follow_mail IS NULL AND u.check_delivery IS NOT NULL AND (u.check_delivery + INTERVAL 4 DAY) < NOW())";
			}
			if($n1m >0){
				$whereArr[] = "(u.check_3w IS NULL AND (u.order_date+ INTERVAL 1 MONTH) < NOW())";
			}
			if($n2m >0){
				$whereArr[] = "(u.check_2m IS NULL AND (u.order_date+ INTERVAL 2 MONTH) < NOW())";
			}
			if($n3m >0){
				$whereArr[] = "(u.check_3m IS NULL AND (u.order_date+ INTERVAL 3 MONTH) < NOW())";
			}
			if($n6m >0){
				$whereArr[] = "(u.check_6m IS NULL AND (u.order_date+ INTERVAL 6 MONTH) < NOW())";
			}
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			$sql.= " ORDER BY `id` DESC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";

			//DBから取得
			$result = $this->_db->fetchAll($sql);
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n,u.*,c.l,i.item
					FROM `order` u
					LEFT JOIN (
						SELECT MAX(limit_date) AS l,customerId,id AS cid
						FROM cutomerPoint
						GROUP BY customerId
					) c ON c.customerId = u.member_no
					LEFT JOIN (
						SELECT GROUP_CONCAT(itemId) as item,orderId , id AS iid
						FROM orderItem i
						GROUP BY orderId
					) i ON i.orderId = u.id".$where;
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		public function getorders2($keyword=NULL,$parent,$resive,$pay,$shipping,$n3d,$n1m,$n2m,$n3m,$n6m,$sex,$birthM,$item,$membership,$point){
			//SQLコマンドを作成
			$sql = "SELECT DISTINCT u.cus_zip,u.cus_zip2,u.cus_pref,u.cus_addr,u.cus_addr2,u.cus_mail,u.cus_name,u.cus_name2,c.l,i.item
					FROM `order` u
					LEFT JOIN (
						SELECT MAX(limit_date) AS l,customerId,id AS cid
						FROM cutomerPoint
						GROUP BY customerId
					) c ON c.customerId = u.member_no
					LEFT JOIN (
						SELECT GROUP_CONCAT(itemId) as item,orderId , id AS iid
						FROM orderItem i
						GROUP BY orderId
					) i ON i.orderId = u.id";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "CONCAT(
						u.cus_name,
						u.cus_name2,
						u.cus_tel,
						u.cus_kana,
						u.cus_zip,
						u.cus_addr,
						u.cus_addr2,
						u.cus_campany,
						u.cus_div,
						u.cus_mail,
						u.cus_fax,
						u.delivery_name,
						u.delivery_tel,
						u.delivery_kana,
						u.delivery_zip,
						u.delivery_addr,
						u.delivery_addr2,
						u.delivery_campay,
						u.delivery_div,
						u.GMOorder_id,
						u.member_no,
						u.delivery_mail
					) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "u.parent=".$parent;
			//性別
			if($sex >0){
				$whereArr[] = "u.cus_sex =".$sex;
			}
			//生月
			if($birthM >0){
				$whereArr[] = "u.cus_birthM =".$birthM;
			}
			//会員
			if($membership >0){
				$whereArr[] = "u.member_no!=''";
			}

			//商品で検索
			if($item){
				$whereArr[] = "i.item LIKE '%".$item."%'";
			}
			//ポイント有効期限
			if($point >0){
				if($point == 1){
					$whereArr[] = "(c.l<DATE_ADD(NOW(),INTERVAL 1 MONTH))";
				}elseif($point == 2){
					$whereArr[] = "(c.l<DATE_ADD(NOW(),INTERVAL 3 MONTH))";
				}
			}

			if($resive >0){
				$whereArr[] = "u.check_order IS NULL";
			}
			if($pay >0){
				$whereArr[] = "u.check_payment_end IS NULL";
			}
			if($shipping >0){
				$whereArr[] = "u.check_delivery IS NULL";
			}
			if($n3d >0){
				$whereArr[] = "(u.check_follow_mail IS NULL AND (u.order_date+ INTERVAL 3 DAY) < NOW())";
			}
			if($n1m >0){
				$whereArr[] = "(u.check_3w IS NULL AND (u.order_date+ INTERVAL 1 MONTH) < NOW())";
			}
			if($n2m >0){
				$whereArr[] = "(u.check_2m IS NULL AND (u.order_date+ INTERVAL 2 MONTH) < NOW())";
			}
			if($n3m >0){
				$whereArr[] = "(u.check_3m IS NULL AND (u.order_date+ INTERVAL 3 MONTH) < NOW())";
			}
			if($n6m >0){
				$whereArr[] = "(u.check_6m IS NULL AND (u.order_date+ INTERVAL 6 MONTH) < NOW())";
			}
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			$sql.= " ORDER BY `id` DESC";

			//DBから取得
			$result = $this->_db->fetchAll($sql);
			return $result;
		}
		//商品情報詳細の取得
		public function getorderDetail($id,$parent){
			/*
			$result = $this->_db->fetchAll(
				"SELECT u.*,sum( p.pin )-sum( p.pout ) AS point, MAX( p.created ) AS created,COUNT(cus_mail) AS c
				FROM `order` u
				LEFT JOIN (
					SELECT  `id` ,`pin` ,`pout` ,  `balance` ,  `customerId` ,  `created`
					FROM cutomerPoint
					ORDER BY  `created`
				) AS p ON p.customerId = u.member_no
				WHERE u.id=".$id." AND u.parent=".$parent;
			);
			*/
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("order")
				->where("id=?",$id)
				->where("parent=?",$parent)
			);

			return $result[0];
		}
		//商品情報詳細の取得
		public function getorderByItems($id,$parent){
			//$sql = "SELECT u.*,i.tax
			$sql = "SELECT u.*
							FROM `orderItem` u
							LEFT JOIN `item` i ON i.id = u.itemId
							WHERE u.orderId={$id} ";
			$result = $this->_db->fetchAll($sql);
			return $result;
		}
		//商品情報詳細の取得
		public function getordersByUser($user){
			$sql = "SELECT *
							FROM `order`
							WHERE ".$this->_db->quoteInto("member_no=?",$user)
							."ORDER BY orderDatetime";
			$result = $this->_db->fetchAll($sql);
			return $result;
		}
		public function getorderByItems2($id){
			$sql = "SELECT i.*,o.quantity AS q,m.url as imagen
							FROM `orderItem` o
							LEFT JOIN `item` i ON i.id = o.itemId
							LEFT JOIN (select * from `image` group by `item`) m on m.item = i.id
							WHERE o.orderId={$id}";
			$result = $this->_db->fetchAll($sql);
			$arr = array();
			foreach($result as $k =>$v){
				$arr[] = array("q"=>$v['q'],"data"=>$v);
			}
			return json_encode($arr);
		}
		public function getorderByItems3($id){
			$sql = "SELECT o.*,o.quantity AS q,m.url as imagen
							FROM `orderItem` o
							LEFT JOIN `item` i ON i.id = o.itemId
							LEFT JOIN (select * from `image` group by `item`) m on m.item = i.id
							WHERE o.orderId={$id}";
			$result = $this->_db->fetchAll($sql);
			$arr = array();
			foreach($result as $k =>$v){
				$arr[] = array("q"=>$v['q'],"data"=>$v);
			}
			return json_encode($arr);
		}

		//在庫管理ステータス一覧
		public function getStockStatus(){
			return $this->_db->fetchAll(
				$this->_db->select()
					->from("stockStatus")
			);
		}
		//商品カテゴリ一覧
		public function getItemCategory(){
			return $this->_db->fetchAll(
				$this->_db->select()
					->from("item_category")
			);
		}

		//設定を読み込み
		public function setting($id){
			//送料を読み込み
			$shipping = $this->_db->fetchAll(
				$this->_db->select()
				->from("shipping")
				->where("parent=?",$id)
			);
			//会社設定
			$company = $this->_db->fetchAll(
				$this->_db->select()
				->from("company")
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

			return array(
				"youto"=>$youto,
				"shipping"=>$shipping,
				"global"=>$global[0],
				"company"=>$company[0],
				"point"=>$point[0],
				"delivery"=>$delivery[0],
				"payment"=>$payment,
				"tax"=>$tax[0],
				"discount"=>$discount[0]
			);
		}
		public function sendmail($autoRes = "",$subject,$body,$fromMail,$fromName,$toMail,$toName,$smtp){

			###################################################################
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");
			include("PHPMailer/class.phpmailer.php");
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
			if(!$mail->Send()) {
				$m_result = "E";
			} else {
				$m_result = "T";
			}
			###################################################################
		}
	}
?>
