<?php
	class Model_Editordivision extends Model_Editorgeneral {

		//ユーザーリストの取得
		public function getDivision($keyword=NULL,$p=0,$limit=1,$id){
			//SQLコマンドを作成
			$sql = "SELECT * FROM `division`";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = explode(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "concat(name,addr,addr2,tel,tel2,tel3) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "parent=".$id;
			//WHERE句を生成
			if(!empty($whereArr)){
				$sql.= " WHERE ".implode(" and ",$whereArr);
				$where = " WHERE ".implode(" and ",$whereArr);
			}
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			$sql2 = "SELECT COUNT(*) AS n FROM division".$where;
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		//ユーザー詳細情報の取得（ポイント）
		public function getDivisionDetail($id,$parent){
			$sql = "SELECT * FROM `division`";
			$sql.= " WHERE id={$id} AND parent=".$parent;
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
				"company"=>$company[0],/*
				"youto"=>$youto,
				"shipping"=>$shipping,
				"global"=>$global[0],
				"point"=>$point[0],
				"delivery"=>$delivery[0],
				"payment"=>$payment,
				"tax"=>$tax[0],
				"discount"=>$discount[0]*/
			);
		}
	}
?>
