<?php
class Common_MemberController extends Common_BaseController {
    public function postDispatch() {
		//一般ユーザー以外はそれぞれの権限のモジュールへ転送
		if($this->view->user->kind != 3){
			common::redirect($this->view->user->kind);
		}
		//レイアウトの設定
		$option = array(
			"layout" => "index",
			"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
		);
		Zend_Layout::startMvc($option);
	}
}
