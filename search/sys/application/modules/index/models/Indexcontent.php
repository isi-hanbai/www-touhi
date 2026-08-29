<?php
	class Model_Indexcontent extends Model_Indexgeneral {
		//サイトマップを取得
		public function getSitemap(){
			$arr = array("item"=>"","content"=>"","youto"=>"","pref"=>"");
			//カテゴリ別商品一覧を取得
			$arr['item'] = $this->_db->fetchAll(
				$this->_db->select()
				->from("item_category")
				->where("parent=88")
				->where("display=0")
			);
			foreach($arr['item'] as $k=>$v){
				$sql = "SELECT u.*, c.name AS categoryName, s.name AS stockName, sp.name AS shippingName,i.url as imagen
							FROM `item` u
							LEFT JOIN item_category c ON u.category = c.id
							LEFT JOIN stockStatus s ON u.stockFlug = s.id
							LEFT JOIN shipping sp ON u.shipping = sp.id
							LEFT JOIN (select * from `image` group by `item`) i on i.item = u.id";
				$whereArr = array();
				$whereArr[] = $this->_db->quoteInto("u.category=?",$v['id']);
				$whereArr[] = $this->_db->quoteInto("u.active=?",1);
				$whereArr[] = $this->_db->quoteInto("u.parent=?",88);
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
				$sql.= " ORDER BY u.id DESC";
				$arr['item'][$k]['data'] = $this->_db->fetchAll($sql);
			}
			
			//カテゴリ別コンテンツを取得
			$arr['content'] = $this->_db->fetchAll(
				$this->_db->select()
				->from("content_category")
				->where("parent=88")
				->where("display=0")
			);
			foreach($arr['content'] as $k=>$v){
				$sql = "SELECT *
							FROM `content`";
				$whereArr = array();
				$whereArr[] = $this->_db->quoteInto("category=?",$v['id']);
				$whereArr[] = $this->_db->quoteInto("active=?",0);
				$whereArr[] = $this->_db->quoteInto("parent=?",88);
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
				$arr['content'][$k]['data'] = $this->_db->fetchAll($sql);
			}
			//用途一覧を取得
			$arr['youto'] = $this->_db->fetchAll(
				$this->_db->select()
				->from("youto")
				->where("parent=88")
				->where("active=0")
				->order("order")
			);
			//都道府県別市区町村一覧を取得
			$arr['pref'] = $this->_db->fetchAll(
				$this->_db->select()
				->from("prefArea")
			);
			foreach($arr['pref'] as $k=>$v){
				$pref = $this->_db->fetchAll(
					$this->_db->select()
					->from('pref')
					->where("area=?",$v['id'])
				);
				$arr['pref'][$k]['prefs'] = $pref ;
				foreach($pref  as $kk=>$vv){
					$arr['pref'][$k]['prefs'][$kk]['city'] = $this->_db->fetchAll(
						$this->_db->select()
						->from("zip")
						->where("pref=?",$vv['id'])
						->group("city")
					);
				}
			}
			
			return $arr;
		}
	}
?>