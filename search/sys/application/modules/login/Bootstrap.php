<?php

class Login_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoloader() {
        $instance = new stdClass();
        $instance->test = 'login';
        return $instance;
		
		//レイアウトの設定
		$option = array(
			"layout" => "index",
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

}
