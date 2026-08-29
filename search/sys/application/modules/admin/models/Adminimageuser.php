<?php
	class Model_Adminimageuser extends Model_Admingeneral {
		
		//ユーザーリストの取得
		public function getUsers($kind =NULL,$keyword=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT DISTINCT u.*, k.name AS kindName,pl.size AS max_size
						FROM ImageUser u
						LEFT JOIN ImageUserKind k ON u.kind = k.id
						LEFT JOIN plan pl ON u.plan = pl.id";
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
		public function getUsersDemand($kind =NULL,$keyword=NULL,$p=0,$limit=1,$parent){
			//SQLコマンドを作成
			$sql = "SELECT u.*, k.name AS kindName, SUM( p.din )-SUM( p.dout ) AS demand, MAX( p.created ) AS created
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`din` ,`dout`  ,  `customer` ,  `created` 
							FROM demand
							ORDER BY  `created`
						) AS p ON p.customer = u.id
						LEFT JOIN ImageUserKind k ON u.kind = k.id
						LEFT JOIN plan pl ON u.plan = pl.id";
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
			$whereArr[] = "demand=1";
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
		//選択されたユーザーリストの取得
		public function getUsersSelect($idArr){
			//SQLコマンドを作成
			$sql = "SELECT u.*, k.name AS kindName
							FROM ImageUser u
							LEFT JOIN ImageUserKind k ON u.kind = k.id";
			//WHERE句を生成
			$whereArr = array();
			foreach($idArr as $v){
				$whereArr[] ="u.id=".$v;
			}
			$where = implode(" OR ",$whereArr);
			$sql.= " WHERE ".$where;
			//DBから取得
			$result = $this->_db->fetchAll($sql);
			return $result;
		}
		
		//ユーザー詳細情報の取得（ポイント）
		public function getUserDetail($id){
			$sql = "SELECT u.*, k.name AS kindName, sum( p.pin )-sum( p.pout ) AS point, MAX( p.created ) AS created,pl.size AS max_size
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`pin` ,`pout` ,  `balance` ,  `customerId` ,  `created` 
							FROM cutomerPoint
							ORDER BY  `created`
						) AS p ON p.customerId = u.id
						LEFT JOIN ImageUserKind k ON u.kind = k.id
						LEFT JOIN plan pl ON u.plan = pl.id";
			$sql.= " WHERE u.id={$id}";
			$sql.= " GROUP BY u.id";
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		//ユーザー詳細情報の取得（売掛）
		public function getUserDetail2($id){
			$sql = "SELECT u.*, k.name AS kindName, sum( p.din )-sum( p.dout ) AS demand, MAX( p.created ) AS created
						FROM ImageUser u
						LEFT JOIN (
							SELECT  `id` ,`din` ,`dout`  ,  `customer` ,  `created` 
							FROM demand
							ORDER BY  `created`
						) AS p ON p.customer = u.id
						LEFT JOIN ImageUserKind k ON u.kind = k.id";
			$sql.= " WHERE u.id={$id}";
			$sql.= " GROUP BY u.id";
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		
		public function unDemandOrder($id){
			$arr = array(
				"delivery_campay",
				"delivery_name",
				"delivery_tel",
				"delivery_date",
				"delivery_zip",
				"delivery_addr",
				"delivery_addr2",
				"id",
				"order_id",
				"order_date",
				"member_no",
				"seikyu_cost"
				);
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("order",$arr)
				->where("member_no=?",$id)
				->where("check_payment IS NULL")
				->where("order_kind=?",0)
				->joinLeft("pref", "`order`.delivery_pref =pref.id","name as delivery_prefName")
			);
			return $result;
		}
		public function getDemand($id){
			$result = $this->_db->fetchAll(
				$this->_db->select()
				->from("demand")
				->where("customer=?",$id)
				->where("din !=0")
			);
			return $result;
		}
	}
?>