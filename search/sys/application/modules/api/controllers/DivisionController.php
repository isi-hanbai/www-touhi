<?php
// IndexController
class Api_DivisionController extends Common_ApiController {
	private $_db;
	public function init() {
			require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
			$this->_db = new Model_Apiimageuser();
	}

	//選択されたデータの削除
	public function deleteAction() {
		if($this->_request->isPost()){
			$idArr = $this->_request->getPost("ids");
			$whereArr = array();
			if(is_array($idArr)){
				foreach($idArr as $v){
					$whereArr[] ="`id`=".$v;
				}
			}else{
				$whereArr[] ="`id`=".$idArr;
			}
			$where = implode(" OR ",$whereArr);
			if($this->_db->delete("division",$where)){
				echo "OK";
			}
		}
	}

}
?>
