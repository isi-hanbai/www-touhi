<?php
class Common_BaseController extends Zend_Controller_Action {
    protected $_acl = NULL;
    protected $_urlRedirect = NULL;
    protected $_whileListModule = array(
        'account',
    );
    public function preDispatch() {
		//共通関数を読み込み
		require_once(dirname(__FILE__)."/../common.php");
		//ルーター設定
        $module = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        $flag = true;
        $auth = Zend_Auth::getInstance();
        if ($flag) {
            $this->_curUser =$auth->getIdentity();
            $this->view->curUser =$this->_curUser;
            $linkUrl = $module . "/" . $controller . "/" . $action;
            $this->view->shortUrl = $linkUrl;
            $this->view->uri = $this->getRequest()->getRequestUri();
        }
		//コントローラーの種別を取得
		$this->view->action = $action;
		$this->view->controller = $controller;
		$this->view->module = $module;
		//ユーザー情報に応じてリダイレクト
		if($module =="admin" || $module =="editor" || $module == "merchant" || $module == "member"){
			if (!$auth->hasIdentity()){
				//ログイン状態でない場合はログイン画面へ
				header("location:".BASEURL."/login/");
			}else{
				//ログイン中のセッション情報を取得
				$auth = Zend_Auth::getInstance();
				$user = $auth->getIdentity();
				$this->view->user = $user;
			}
		}else{
			if ($auth->hasIdentity()){
				//ログイン中のセッション情報を取得
				$auth = Zend_Auth::getInstance();
				$user = $auth->getIdentity();
				$this->view->user = $user;
			}
		}
		//error_reporting(E_ERROR | E_WARNING | E_PARSE);
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECTED );
		
		
		
		
		/*
        // 多言語化
        $translate = new Zend_Translate('Array', APPLICATION_PATH . '/languages/en.php', 'en_US');
        $translate->addTranslation(APPLICATION_PATH . '/languages/vi.php', 'vi_VN');
        $translate->addTranslation(APPLICATION_PATH . '/languages/ja.php', 'ja_JP');
        $translate->setLocale('ja_JP'); // Change value here to change language
        Zend_Registry::set('Zend_Translate ', $translate);
        $this->view->translate = $translate;
        $this->_trans = $translate;
		*/
    }
}
?>