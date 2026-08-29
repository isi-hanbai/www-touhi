<?php
// IndexController
class Api_SettingsController extends Common_ApiController {
	private $_db;
	private $_user;
	public function init() {
		require_once(APPLICATION_PATH."/modules/api/models/Apiimageuser.php");
		$this->_db = new Model_Apiimageuser();
		
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		$this->_user = $auth->getIdentity();
	}
	
	//顧客リスト
	public function paymentAction() {
		$postArr = $this->_db->postArray();
		$id = $postArr['id'];
		if($this->_db->delete("payment_method",$this->_db->quoteInto("`id`=?",$id))){
			echo "OK";
		}
	}	
	public function planAction() {
		$postArr = $this->_db->postArray();
		$id = $postArr['id'];
		if($this->_db->delete("plan",$this->_db->quoteInto("`id`=?",$id))){
			echo "OK";
		}
	}	
	
	public function shippingAction() {
		$postArr = $this->_db->postArray();
		$id = $postArr['id'];
		if($this->_db->delete("shipping",$this->_db->quoteInto("`id`=?",$id))){
			echo "OK";
		}
	}	
	
}
?>
