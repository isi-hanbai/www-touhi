<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initAutoload() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => dirname(__FILE__),
        ));

        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
            'module' => 'error',
            'controller' => 'error',
            'action' => 'error'
        )));
		$front->setDefaultModule('index');
        return $autoloader;
    }
    protected function _initDatabase() {
        $db = $this->getPluginResource('db')->getDbAdapter();
        Zend_Registry::set('db', $db);
    }
    protected function _initControllerHelpers() {
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/Helper', 'helper');
    }
    /*
	*/
    //2015.08.12
    protected function _initLog()
    {
        global $logger;

        $options = new Zend_Config($this->getOptions());

        $writer = new Zend_Log_Writer_Stream( $options->resources->log->stream->writerParams->stream);
        $logger = new Zend_Log($writer);
        $logger->info('start logger');
    }
}
