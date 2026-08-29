<?php
// マイページ用コントローラー
class Admin_IndexController extends Common_AdminController {
	//初期化メソッドの定義
	private $_user;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			$this->_user = $auth->getIdentity();
		}
		//$this->_fax = new Model_Adminfax;
	}
	//2016.06.20　テンプレート更新用タグ demo
	public function tempAction(){
		$temp = file_get_contents("http://localhost:8888/ishikai/contents/tmp.html");
		$tempArr = preg_split("/<!---content--->/",$temp);
		//headerの処理
		$str = "<title><?=strip_tags($"."this->title".")?></title><script>var baseurl = '".BASEURL."';</script>\n";
		$header = preg_replace("/<title>(.*?)<\/title>/s",$str,$tempArr[0]);
		$header = preg_replace("/<!--the_title-->/s","<h1 class=\"qodef-page-title entry-title\"><?=strip_tags($"."this->title".")?></h1>",$header);
		$header = preg_replace("/<!--the_title_-->/s","<?=strip_tags($"."this->title".")?>",$header);
		$header = preg_replace("/<i class=\"fas fa-user margin-right-10\"><\/i><a href=\"#\">ログイン<\/a>/","<?="."$"."this->loginout"."?>",$header);

		//headerの処理
		$footer = preg_replace("/<\/body>/s","<?=$"."this->add_script"."?></body>",$tempArr[1]);;
		$header_file = APPLICATION_PATH.'/layouts/scripts/header.phtml';
		$r1 = file_put_contents($header_file, $header,LOCK_EX);
		$footer_file = APPLICATION_PATH.'/layouts/scripts/footer.phtml';
		$r1 = file_put_contents($footer_file, $footer,LOCK_EX);
		header("location:".BASEURL."/admin/");
	}
	public function indexAction() {
		$this->view->title = '<i class="fa fa-tachometer"></i>　ダッシュボード';
		$this->view->bread = '<li>'.$this->view->title.'</li>';
		//該当月のFax送信履歴を表示

		/*
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
		$kaijou = $this->_fax->getfax($keyword,$p,$limit,$year,$month,$this->_user->id);
		$this->view->users = $kaijou[0];
		//ページャーを生成
		common::userpager($keyword,$kaijou[1],$limit,$p,"/merchant/fax/");
		$this->view->c = $kaijou[2]["c"];
		$this->view->n = $kaijou[2]["n"];
		$this->view->year = $year;
		$this->view->month = $month;
		*/
	}
}
?>
