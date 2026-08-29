<?php
	class Model_Adminsettings extends Model_Admingeneral {
		public function insertUpdate($table,$postArr,$id,$n){

			if($n<1){
				//データがDBに存在しない場合はインサート;
				//echo 123;
				$postArr['parent'] = $id;
				if($this->_db->insert($table,$postArr)){
					return true;
				}
			}else{
				//データがDBに存在する場合はアップデート
				//echo 234;
				if($this->_db->update($table,$postArr,$this->_db->quoteInto("parent=?",$id))){
					return true;
				}
			}
		}
	}
?>