<?php
	require_once(APPLICATION_PATH."/modules/api/models/Apigeneral.php");
	class Model_Apiimageuser extends Model_Apigeneral {

		//管理者及びマーチャントリスト
		public function getUsers(){
			$sql = "SELECT u.company, u.name, u.kana, u.mail, u.kind, k.name AS kindName
							FROM ImageUser u
							LEFT JOIN ImageUserKind k ON u.kind = k.id";
			return $this->_db->fetchAll($sql);
		}
		public function getUsersSelect($arr){
			$sql = "SELECT u.company, u.name, u.kana, u.mail, u.kind, k.name AS kindName
							FROM ImageUser u
							LEFT JOIN ImageUserKind k ON u.kind = k.id";
			$whereArr = array();
			foreach($arr as $v){
				$whereArr[] = $this->_db->quoteInto("u.id=?",$v);
			}
			$where = " WHERE ".implode(" OR ",$whereArr);
			$sql.=$where;
			$sql.="ORDER BY u.id DESC";
			return $this->_db->fetchAll($sql);
		}

		//顧客リスト
		public function getcustomers($kind =NULL,$keyword=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT u.*, k.name AS kindName, SUM( p.pin )-SUM( p.pout ) AS point, MAX( p.created ) AS created
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`pin` ,`pout` ,  `balance` ,  `customerId` ,  `created`
							FROM cutomerPoint
							ORDER BY  `created`
						) AS p ON p.customerId = u.id
						LEFT JOIN ImageUserKind k ON u.kind = k.id";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "concat(u.company, u.name, u.kana, u.mail) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "parent=".$parent;
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
		//ポイント
		public function getcustomerPoint($member_no){
			$sql = "SELECT SUM( p.pin ) - SUM( p.pout ) AS POINT
			FROM ImageUser u
			LEFT JOIN (
			SELECT  `id` ,  `pin` ,  `pout` ,  `balance` ,  `customerId` ,  `created`
			FROM cutomerPoint
			ORDER BY  `created`
			) AS p ON p.customerId = u.id
			WHERE u.id =".$member_no."
			GROUP BY u.id
			LIMIT 1";

			//SQLコマンドを作成
			/*
			$sql = "SELECT SUM( p.pin )-SUM( p.pout ) AS point
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`pin` ,`pout` ,  `balance` ,  `customerId` ,  `created`
							FROM cutomerPoint
							ORDER BY  `created`
						) AS p ON p.customerId = u.id";
			//検索クエリを作成
			$whereArr = array();
			$whereArr[] = "u.id=".;
			//WHERE句を生成
			if(!empty($whereArr)){
				$sql.= " WHERE ".implode(" and ",$whereArr);
			}
			$sql.= " GROUP BY u.id";
			$sql.= " LIMIT 1";
			//DBから取得
			*/
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}


		public function getItemOfCategory($merchant,$category){
			$sql = "SELECT u.company, u.name, u.kana, u.mail, u.kind, k.name AS kindName
							FROM item u
							LEFT JOIN ImageUserKind k ON u.kind = k.id";
			return $this->_db->fetchAll($sql);
		}
		public function getItemCategory($merchant){
			return $this->_db->fetchAll(
				$this->_db->select()
				->from("item_category")
				->where("parent=?",$merchant)
			);
		}
		public function getItemsOFCategory($merchant,$category){
			return $this->_db->fetchAll('SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id
							WHERE '.$this->_db->quoteInto("u.parent=?",$merchant).'
							AND '.$this->_db->quoteInto("u.category=?",$category).'
							ORDER BY u.id DESC');
		}


		public function getItemsDetail($merchant,$number){
			$result = $this->_db->fetchAll('SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id
							WHERE '.$this->_db->quoteInto("u.parent=?",$merchant).'
							AND '.$this->_db->quoteInto("u.number=?",$number).'
							ORDER BY u.id DESC');
			return $result[0];
		}
		public function getItemsDetail2($number){
			$result = $this->_db->fetchAll('SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id
							AND '.$this->_db->quoteInto("u.id=?",$number));
			return $result[0];
		}
		public function getOrderDetail($id){
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("order",array(
					"seikyu_cost",
					"id"
				))
				->where("`id`=?",$id)
			);
			return $result[0];
		}


		//葬儀オーダー用
		public function getSougiItemCategory($merchant){
			return $this->_db->fetchAll(
				$this->_db->select()
				->from("sougi_item_category")
				->where("parent=?",$merchant)
			);
		}
		public function getSougiItemsOFCategory($merchant,$category,$kaijou=NULL){
			$result = $this->_db->fetchAll('SELECT u.*, c.name AS categoryName
							FROM `sougi_item` u
							LEFT JOIN sougi_item_category c ON u.category = c.id
							WHERE '.$this->_db->quoteInto("u.category=?",$category).'
							ORDER BY u.id DESC');
			$arr = array();
			if($kaijou != NULL){
				foreach($result as $k=>$v){
					$re = $this->_db->fetchAll(
						"SELECT COUNT(*) AS n from `sougi_item_kaijou_relation`
						WHERE `sougi_item` =".$v['id']." AND kaijou=".$kaijou
					);
					if($re[0]['n'] >0){
						$arr[] = $result[$k];
					}
				}
			}else{
				$arr = $result;
			}
			return $arr;
		}
		public function getSougiItemsDetail($merchant,$number){
			$result = $this->_db->fetchAll('SELECT u.*, c.name AS categoryName
							FROM `sougi_item` u
							LEFT JOIN sougi_item_category c ON u.category = c.id
							WHERE '.$this->_db->quoteInto("u.id=?",$number).'
							ORDER BY u.id DESC');
			$result[0]['order'] = 0;
			$result[0]['specification'] = "";
			return $result[0];
		}




		public function sendmail($autoRes = false,$subject,$body,$fromMail,$fromName,$toMail,$toName,$smtp,$attachfile){
			mb_language("Japanese");
			mb_internal_encoding("UTF-8");
			date_default_timezone_set("Asia/Tokyo");
			$mineArr = array(
				"pdf"	=>	'application/pdf',
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
				'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presenta'
			);
			function mbCnv($string) {
				return mb_convert_encoding($string, 'ISO-2022-JP', 'UTF-8');
			}
			function mbMime($string) {
				return mb_encode_mimeheader(mbCnv($string), 'ISO-2022-JP', 'B');
			}

			$mail = new Zend_Mail('ISO-2022-JP');
			$mail->setSubject(mbCnv($subject));
			$mail->addTo($toMail, mbMime($toName));
			$mail->setFrom($fromMail, mbMime($fromName));
			$mail->setBodyText(mbCnv($body), null, Zend_Mime::ENCODING_7BIT);
			if(is_array($attachfile)){
				for($i=0;$i<count($attachfile);$i++){
					$extention = end(explode(".",IMG_DIR.$attachfile[0]));
					$at = new Zend_Mime_Part(IMG_DIR.$attachfile[$i]);
					$at->type        = $mineArr[$extention];
					//$at->type        = $finfo->file( IMG_DIR.$attachfile[$i], FILEINFO_MIME_TYPE);
					$at->disposition = Zend_Mime::DISPOSITION_INLINE;
					$at->encoding    = Zend_Mime::ENCODING_BASE64;
					$at->filename    = $attachfile[$i];

					$mail->addAttachment($at);
				}
			}
			$config = array(
				'port'=>$smtp['SMTPport'],
				'auth' => '',
				'username' => $smtp['SMTPuser'],
				'password' => $smtp['SMTPpass']
			);
			$smtp = new Zend_Mail_Transport_Smtp($smtp['SMTPhost'], $config);
			try {
				$mail->send($smtp);
				return 1;
			} catch (Zend_Exception $e) {
				//echo $e or die;
				return 2;
			}
		}
	}
?>
