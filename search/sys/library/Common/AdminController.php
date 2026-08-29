<?php
class Common_AdminController extends Common_BaseController {
    public function postDispatch() {
		//管理者以外はそれぞれの権限のモジュールへ転送
		if($this->view->user->kind != 1){
			common::redirect($this->view->user->kind);
		}
		//レイアウトの設定(コントローラー及びアクションごとに設定）
		if($this->view->controller == "item" && $this->view->action == "upload"){
			$option = array(
				"layout" => "noframe",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "item" && $this->view->action == "deleteimage"){
			$option = array(
				"layout" => "noframe",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "order" && $this->view->action == "paper"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "demand" && $this->view->action == "reverse"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "demand" && $this->view->action == "pdf"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "user" && $this->view->action == "postcard"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "sougi" && $this->view->action == "gant"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}elseif($this->view->controller == "sougi" && $this->view->action == "jizen"){
			$option = array(
				"layout" => "nohtml",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}else{
			$option = array(
				"layout" => "main_layout",
				"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
			);
		}
		Zend_Layout::startMvc($option);
	}
}
