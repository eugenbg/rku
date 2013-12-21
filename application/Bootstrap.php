<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_front;

    protected function _initViewController() {
    $this->bootstrap('FrontController');
    $this->_front = $this->getResource('FrontController');
    }

    protected function _initAutoLoad() {
        $modelLoader = new Zend_Application_Module_Autoloader(array(
                        'namespace' => '',
                        'basePath' => APPLICATION_PATH
        ));
        return $modelLoader;
    }

    protected function _initCreateMenus() {
                $identity = Zend_Auth::getInstance()->getIdentity();
                if ($identity) {
                    if ($identity->role == 'user') {
                        $menuContainer = new Zend_Navigation(array (
                                        array (
                                        'label' => 'Отчет по месяцам',
                                        'controller' => 'report',
                                        'action' => 'index'),
                                        array (
                                        'label' => 'Выйти',
                                        'controller' => 'auth',
                                        'action' => 'logout')
                                        ));

                    } elseif ($identity->role == 'admin') {
                        $menuContainer = new Zend_Navigation(array (
                                        array (
                                        'label' => 'Обновить базу данных',
                                        'controller' => 'updatedb',
                                        'action' => 'index'),
                                        /*array (
                                        'label' => 'Зарегистрировать нового клиента',
                                        'controller' => 'user',
                                        'action' => 'register'),
                                        array (
                                        'label' => 'Удалить клиента',
                                        'controller' => 'user',
                                        'action' => 'delete'),*/
                                        array (
                                        'label' => 'Выйти',
                                        'controller' => 'auth',
                                        'action' => 'logout'))
                                );// new Zend_Navigation params
                    }
                } else // if not logged in
                {
                    $menuContainer = new Zend_Navigation(array (
                                    array (
                                    'label' => 'Войти',
                                    'controller' => 'auth',
                                    'action' => 'login')));
                }
                $this->bootstrap('layout');
                $layout = $this->getResource('layout');
                $layout->menu = $menuContainer;
            }



}

