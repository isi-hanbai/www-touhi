<?php

class Member_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload() {
		//Moduleフォルダ名でモジュールを識別
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => dirname(__FILE__),
        ));
        return $autoloader;
		
    }

}