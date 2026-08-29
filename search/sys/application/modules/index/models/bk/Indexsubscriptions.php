<?php
	class Model_Indexsubscriptions extends Model_Indexgeneral {
		//定期コースを取得
		public function getSubscriptions($keyword=NULL,$p=0,$limit=1,$parent){
			$sql = "SELECT u.* ,
				(SELECT COUNT(*) FROM Subscriptions o WHERE o.courseid=u.id) AS n
				FROM SubscriptionsCourse u";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "CONCAT(u.name,u.description) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "u.parent=".$parent;
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			$sql.= " ORDER BY u.id DESC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			$result = $this->_db->fetchAll($sql);
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n FROM `SubscriptionsCourse` u".$where;
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		public function getSubscriptionsDetail($id){
			$sql = "SELECT *
			FROM SubscriptionsCourse 
			WHERE ".$this->_db->quoteInto("`id`=? ",$id);
			return $this->_db->fetchAll($sql);
		}
		public function getSubscriptionsItems($id){
			$sql = "SELECT i.*,o.quantity AS q,m.url as imagen
						FROM `SubscriptionsCourseItem` o
						LEFT JOIN `item` i ON i.id = o.itemId
						LEFT JOIN (select * from `image` group by `item`) m on m.item = i.id
						WHERE ".$this->_db->quoteInto(" o.courseid=?",$id);
			$item = $this->_db->fetchAll($sql);
			$arr = array();
			foreach($item as $k =>$v){
				$arr[] = array("q"=>$v['q'],"data"=>$v);
			}
			return $arr;
		}
		public function getSubscriptionsItems2($id){
			$sql = "SELECT i.*,o.quantity AS q,m.url as imagen
						FROM `SubscriptionsCourseItem` o
						LEFT JOIN `item` i ON i.id = o.itemId
						LEFT JOIN (select * from `image` group by `item`) m on m.item = i.id
						WHERE ".$this->_db->quoteInto(" o.courseid=?",$id);
			return $this->_db->fetchAll($sql);
		}
		
		public function getSubscriptionsOrder($keyword=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT u.*, c.name AS courseName,c.seikyu_cost AS seikyu_cost
						FROM `Subscriptions` u
						LEFT JOIN SubscriptionsCourse c ON c.id = u.courseid";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "CONCAT(
						u.cus_name,
						u.cus_tel	,
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
						u.delivery_mail
					) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "u.parent=".$parent;
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
			$sql2 = "SELECT COUNT(*) AS n FROM `Subscriptions` u".$where;
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		public function getSubscriptionsOrderDetail($id){
			$sql = "SELECT s.* , c.name AS courseName,c.name,c.seikyu_cost,c.tax_total,c.item_total,c.turn,c.id AS cid,DAY(DATE_FORMAT(s.created,'%Y-%m-%d')) AS dDay
				FROM Subscriptions s
				LEFT JOIN SubscriptionsCourse c ON c.id = s.courseid
				WHERE s.id=?
				LIMIT 1";
			 $re = $this->_db->fetchAll($this->_db->quoteInto($sql,$id));
			 return $re[0];
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
	}
?>