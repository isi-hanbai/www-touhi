<?php
	class Model_Indexyouto extends Model_Indexgeneral {
		//地方一覧を取得
		public function getYouto(){
			$Youto =  $this->_db->fetchAll(
				$this->_db->select()
				->from('youto')
				->order("order")
				->where("active=0")
			);
			return $Youto;
		}
		public function getItemOfYouto($youto = NULL){
			$sql = "SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($youto)){
				$whereArr[] = "u.tag LIKE '%".$youto."%'";
			}
			$whereArr[] = "u.parent=88";
			$whereArr[] = "u.display=1";
			$whereArr[] = "u.active=1";
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			//ORDER句を生成
			$sql.= " ORDER BY u.id DESC";
			//DBから取得
			$result = $this->_db->fetchAll($sql);
			return $result;
		}
	}
?>