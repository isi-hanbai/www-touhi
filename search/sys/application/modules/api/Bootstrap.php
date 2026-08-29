<?php

class Api_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload() {
        $instance = new stdClass();
        $instance->test = 'login';
        return $instance;
		
		//Moduleフォルダ名でモジュールを識別
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => dirname(__FILE__),
        ));
        return $autoloader;
		
    }

}