<?php
	class Model_Indexitem extends Model_Indexgeneral {
		
		//商品一覧の取得
		public function getItems($category=NULL,$keyword=NULL,$p=0,$limit=1,$parent){
			
			//SQLコマンドを作成
							
			$sql = "SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				if(strpos(" ",$key)){
					$keyArr = split(" ",$key);
					foreach($keyArr as $v){
						$whereArr[] = $this->_db->quoteInto("CONCAT(u.name,u.number,u.tag,u.description) LIKE ?","%".$v."%");
					}
				}else{
					$whereArr[] = $this->_db->quoteInto("CONCAT(u.name,u.number,u.tag,u.description) LIKE ?","%".$key."%");
				}
			}
			//カテゴリが指定された場合
			if(!empty($category)){
				$whereArr[] = $this->_db->quoteInto("u.category=?",$category);
			}
			$whereArr[] = $this->_db->quoteInto("u.parent=?",$parent);
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			//ORDER句を生成
			//$sql.= " ORDER BY u.id DESC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n FROM `item` u".$where;
			/**/
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			
			return array($result,$result2[0]['n']);
		}
		public function getItemsoku($p=0,$limit=1,$parent){
			
			//SQLコマンドを作成
							
			$sql = 
				"SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id";
			//検索クエリを作成
			$whereArr = array();
			$whereArr[] = $this->_db->quoteInto("u.realtimeFlug=?",0);
			$whereArr[] = $this->_db->quoteInto("u.category!=?",49);
			$whereArr[] = $this->_db->quoteInto("u.category!=?",50);
			$whereArr[] = $this->_db->quoteInto("u.category!=?",53);
			$whereArr[] = $this->_db->quoteInto("u.category!=?",54);
			$whereArr[] = $this->_db->quoteInto("u.category!=?",57);
			$whereArr[] = $this->_db->quoteInto("u.display=?",1);
			$whereArr[] = $this->_db->quoteInto("u.parent=?",$parent);
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" AND ",$whereArr);
				$sql.=$where;
			}
			//ORDER句を生成
			$sql.= " ORDER BY u.id DESC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n FROM `item` u".$where;
			/**/
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			
			return array($result,$result2[0]['n']);
		}
		//商品情報詳細の取得
		public function getItemDetail($id){
			$sql = $this->_db->quoteInto("SELECT u.*, c.name AS categoryName, c.id AS categoryId, s.name AS stockName
							FROM item u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							WHERE u.id=?",$id);
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		
		public function getItemDetailOfNumber($number){
			$sql = $this->_db->quoteInto("SELECT u.*, c.name AS categoryName, c.id AS categoryId, s.name AS stockName
							FROM item u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							WHERE u.number=?",$number);
			$result = $this->_db->fetchAll($sql);
			return $result[0];
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
		//成分を取得（画像つき）
		public function getItemOfSeibun(){
			$sql = $this->_db->quoteInto("SELECT s.* ,i.thumb as thumbB
			FROM `seibun` s
			LEFT JOIN (select * from `seibunImage` group by `seibun`) i ON i.seibun = s.id");
			return $this->_db->fetchAll($sql);
		}
		
	}
?>