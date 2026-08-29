<?php
// IndexController
class Merchant_FaxController extends Common_MerchantController {
	//初期化メソッドの定義
	private $_db;
	private $_user;
	public function init(){
		//ユーザー情報の取得
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		$this->_db = new Model_Merchantfax();
		//$this->view->user = $this->_db->getUserDetail($this->_user->id);
		//各種設定を読み込み
		//$this->_setting = $this->_db->setting(88);
		/*
		$this->setting = $this->_setting;
		$this->view->setting = $this->setting;
		*/
	}
	public function indexAction() {
		$this->view->title = '<i class="fa fa-tachometer"></i>　FAX送信履歴';
		$this->view->bread = '<li><a href="'.BASEURL.'/merchant/"><i class="fa fa-tachometer"></i>　ダッシュボード</a></li>';
		$this->view->bread = '<li>'.$this->view->title.'</li>';
		//該当月のFax送信履歴を表示
		//GETパラメータを取得
		$keyword = $this->_request->getParam("keyword");
		if(!$this->_request->getParam("year")){
			$year = date("Y");
		}else{
			$year = $this->_request->getParam("year");
		}
		if(!$this->_request->getParam("month")){
			$month = date("m");
		}else{
			$month = $this->_request->getParam("month");
		}
		$month = str_pad($month, 2, 0, STR_PAD_LEFT);
		$p = $this->_request->getParam("p");
		$limit = $this->_request->getParam("limit");
		if($limit<1){
			$limit = 10;
		}
		$start = $limit*$p;
		//Faxリストの読み込み
		$kaijou = $this->_db->getfax($keyword,$p,$limit,$year,$month,$this->_user->id);
		$this->view->users = $kaijou[0];
		//ページャーを生成
		common::userpager($keyword,$kaijou[1],$limit,$p,"/merchant/fax/");
		var_dump($kaijou[2]);
		$this->view->c = $kaijou[2]["c"];
		$this->view->n = $kaijou[2]["n"];
		$this->view->year = $year;
		$this->view->month = $month;
		/*
		*/
	}
}
?>
