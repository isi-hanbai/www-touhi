<?php
	class Model_Indexpref extends Model_Indexgeneral {
		//地方一覧を取得
		public function getPref(){
			$prefArea =  $this->_db->fetchAll(
				$this->_db->select()
				->from('prefArea')
				->order("sort")
			);
			foreach($prefArea as $k=>$v){
				$pref = $this->_db->fetchAll(
					$this->_db->select()
					->from('pref')
					->where("area=?",$v['id'])
				);
				$prefArea [$k]['prefs'] = $pref ;
			}
			return $prefArea;
		}
		
		
		//ランダムに即納商品を抽出
		public function getSokuItem(){
			return $this->_db->fetchAll(
				"SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id
							WHERE u.orderToDeli=0
							AND u.category !=49
							AND u.category !=50
							AND u.category !=53
							AND u.category !=54
							AND u.category !=57
							AND  u.display=1
							AND  u.active=1
							ORDER BY RAND()
							LIMIT 5"
			);
		}
		public function getCityOfPref($pref){
			$prefArr = $this->_db->fetchAll(
				$this->_db->select()
				->from("pref")
				->where("`id`=?",$pref)
			);
			$cityArr = $this->_db->fetchAll(
				$this->_db->select()
				->from("zip")
				->where("pref=?",$pref)
				->group("city")
			);
			$prefArr[0]['city'] = $cityArr;
			return $prefArr[0];
		}
		public function getTownOfCity($city){
			return $this->_db->fetchAll(
				$this->_db->select()
				->from("zip")
				->where("city=?",$city)
			);
		}

	}
?>