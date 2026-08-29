<?php

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload() {
	
		//レイアウトの設定
		$option = array(
			"layout" => "main_layout",
			"layoutPath" => APPLICATION_PATH . "/layouts/scripts/"
		);
		Zend_Layout::startMvc($option);
		
		//Moduleフォルダ名でモジュールを識別
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => dirname(__FILE__),
        ));
        return $autoloader;
		
    }
	/*
	*/
    protected function _initNoErrorHandler()
    {
		/*
        // エラーハンドラプラグインの無効化（登録させない）
        $front = Zend_Controller_Front::getInstance();
        $front->setParam('noErrorHandler', true);
		*/
    }
}