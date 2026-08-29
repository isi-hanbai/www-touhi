<?php
	class Model_Adminhelp extends Model_Admingeneral {
		//商品一覧の取得
		public function getHelp($keyword=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT * FROM `help` ";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "CONCAT(name,title,code) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "parent=".$parent;
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			//ORDER句を生成
			$sql.= " ORDER BY id DESC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n FROM `help` ".$where;
			/**/
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
		//商品情報詳細の取得
		public function getHelpDetail($id){
			$sql = "SELECT *
					FROM help
					WHERE id={$id}";
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		//商品カテゴリ一覧
		public function getContentCategory(){
			return $this->_db->fetchAll(
				$this->_db->select()
					->from("help_category")
			);
		}
		public function getContentCategory2($keyword=NULL,$p=0,$limit=1,$parent){
			$sql = "SELECT * FROM `help_category`";
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "`name,description` LIKE '%".$v."%'";
				}
			}
			$whereArr[] = $this->_db->quoteInto("`parent`=?",$parent);
			//WHERE句を生成
			if(!empty($whereArr)){
				$where =  " WHERE ".implode(" and ",$whereArr);
				$sql.=$where;
			}
			//ORDER句を生成
			$sql.= " ORDER BY `id` ASC";
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " LIMIT {$start} , {$limit}";
			//全件数の取得
			$sql2 = "SELECT COUNT(*) AS n FROM `help_category`".$where;
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			return array($result,$result2[0]['n']);
		}
	}
?>