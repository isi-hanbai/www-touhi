<?php
class Api_AddrController extends Common_ApiController {
	private $_db;
	public function init() {
		require_once(APPLICATION_PATH."/modules/api/models/Apiuser.php");
		$this->_db = new Model_Apiuser();
	}
	
	//選択されたデータの削除
	public function indexAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$c = $this->_db->fetchAll(
				$this->_db->select()
				->from("zip")
				->where("CONCAT(`city`, `town`) LIKE  '%{$postArr['c']}%'")
				->where("`zipcode` LIKE  '{$postArr['d']}%'")
			);
			if(count($c) >0){
				echo json_encode($c,true);
			}else{
				echo "NotExsist";
			}
		}else{
			echo "AccessDenied";
		}
	}
	//選択されたデータの削除
	public function zipAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$c = $this->_db->fetchAll(
				$this->_db->select()
				->from("zip")
				->where("`zipcode`= ?",$postArr['z'])
			);
			if(count($c) >0){
				echo json_encode($c,true);
			}else{
				echo "NotExsist";
			}
		}else{
			echo "AccessDenied";
		}
	}
}
?>