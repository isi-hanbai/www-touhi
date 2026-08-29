<?php
// マイページ用コントローラー
class Editor_IndexController extends Common_EditorController {
	//初期化メソッドの定義
	private $_user;
	public function init(){
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if(!$auth->hasIdentity()){
			header("location:".BASEURL."/login/");
		}else{
			header("location:".BASEURL."/editor/content/");
			/*
			$this->_user = $auth->getIdentity();
      $this->view->m = $this->_user->m;
      $this->view->Auth = $this->_user->Authority;
      $this->view->outsource = $this->_user->outsource;
			*/
		}
	}
	/*
	public function indexAction() {
		//header("location:".BASEURL."/editor/dashboad/");
		$this->view->title = '<i class="fa fa-user"></i>　ダッシュボード';
		$this->view->bread = '<li>
														<a href="'.BASEURL.'/editor/"><i class="fa fa-tachometer"></i>　ダッシュボード</a>
													</li>';
	}
	*/
}
?>
