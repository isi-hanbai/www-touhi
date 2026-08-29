<?php
// IndexController
class Api_DeleteController extends Common_ApiController {
	private $_db;
	public function init() {
			require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
			$this->_db = new Model_Apiimageuser();
	}

	//選択されたデータの削除
	public function indexAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
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
			if($this->_db->delete($postArr['table'],$where)){
				echo "OK";
			}
		}
	}
}
?>
