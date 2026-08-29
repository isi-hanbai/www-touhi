<?php
// IndexController
class Api_SkilController extends Common_ApiController {
	private $_db;
	private $_user;
	private $_setting;
	public function init() {
		require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
		$this->_db = new Model_Apiimageuser();
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		$this->_user = $auth->getIdentity();
	}
	//選択されたデータの削除
	public function indexAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$skil = $this->_db->fetchAll(
				$this->_db->quoteInto("SELECT * FROM `skil` WHERE `division`=?",$postArr['division'])
			);
			echo json_encode($skil);
		}
	}
	public function editorskilAction() {
		if($this->_request->isPost()){
			$postArr = $this->_db->postArray();
			$skil = $this->_db->fetchAll(
				$this->_db->quoteInto("SELECT * FROM `editorSkil` WHERE `editor`=?",$postArr['editor'])
			);
			echo json_encode($skil);
		}
	}
}
?>
