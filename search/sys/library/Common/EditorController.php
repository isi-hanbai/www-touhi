<?php
class Common_EditorController extends Common_BaseController {
  public function postDispatch() {
		//画像編集者以外はそれぞれの権限のモジュールへ転送
		if($this->view->user->kind != 2){
			common::redirect($this->view->user->kind);
		}
    /*
		//セッションを取得
		$auth = Zend_Auth::getInstance();
		//ログインしていない場合はログイン画面へ
		if($auth->hasIdentity()){
			$this->_user = $auth->getIdentity();
      $this->view->m = $this->_user->m;
      $this->view->Auth = $this->_user->Authority;
      $this->view->outsource = $this->_user->outsource;
		}
    */
		//layoutの設定
		$option = array(
			"layout" => "main_layout",
			"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
		);
		$layout = Zend_Layout::startMvc($option);
    //規定容量より保存
		$setting = new Model_Indexsettings;
    $this->view->over = $_SESSION['Zend_Auth']['storage']->over;
	}
}
