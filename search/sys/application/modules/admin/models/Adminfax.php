<?php
	class Model_Adminfax extends Model_Merchantgeneral {
		
		//ユーザーリストの取得
		public function getfax($keyword=NULL,$p=0,$limit=1,$year,$month,$id){
			//SQLコマンドを作成
			$sql = "SELECT * FROM `fax`";
			//検索クエリを作成
			$whereArr = array();
			//検索キーワードが指定された場合
			if(!empty($keyword)){
				$key = mb_convert_kana($keyword,"s","UTF-8");
				$keyArr = split(" ",$key);
				foreach($keyArr as $v){
					$whereArr[] = "concat(name, addr,tel) LIKE '%".$v."%'";
				}
			}
			$whereArr[] = "(DATE_FORMAT(created, '%Y%m') = '".$year.$month."')";
			//WHERE句を生成
			if(!empty($whereArr)){
				$sql.= " WHERE ".implode(" and ",$whereArr);
				$where = " WHERE ".implode(" and ",$whereArr);
			}
			//LIMIT句を生成
			$start = $p*$limit;
			$sql.= " ORDER BY created DESC";
			$sql.= " LIMIT {$start} , {$limit}";
			//DBから取得
			$sql2 = "SELECT COUNT(*) AS n FROM fax".$where;
			$sql3 = "SELECT SUM(count) AS c, COUNT(*) AS n FROM fax WHERE (DATE_FORMAT(created, '%Y%m') = '".$year.$month."')";
			$result = $this->_db->fetchAll($sql);
			$result2 = $this->_db->fetchAll($sql2);
			$result3 = $this->_db->fetchAll($sql3);
			return array($result,$result2[0]['n'],$result3[0]);
		}
	}
?>