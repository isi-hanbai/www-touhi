<?php
	class Model_Memberimageuser extends Model_MemberGeneral {
		
		//######## ユーザー詳細情報の取得 ########
		public function getUserDetail($id){
			//SQLコマンドを作成
			$sql = "SELECT u.*, k.name AS kindName
							FROM ImageUser u
							LEFT JOIN ImageUserKind k ON u.kind = k.id
							WHERE u.id={$id}";
			//DBから取得
			$result = $this->_db->fetchAll($sql);
			return $result[0];
		}
		
	}
?>