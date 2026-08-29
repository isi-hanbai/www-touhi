<?php
class Common_ApiController extends Common_BaseController {
	public function postDispatch() {
		//ビューとレイアウトを無効
		$this->_helper->viewRenderer->setNoRender(); 
		$this->_helper->layout->disableLayout(); 
	}
}
