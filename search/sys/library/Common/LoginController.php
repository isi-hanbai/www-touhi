<?php
class Common_LoginController extends Common_BaseController {
    public function postDispatch() {
		//レイアウトの設定
		$option = array(
			"layout" => "login_layout",
			"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
		);
		Zend_Layout::startMvc($option);
    }
}
