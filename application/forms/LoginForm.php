<?php

class Form_LoginForm extends Zend_Form {

    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('login');

        $username = new Zend_Form_Element_Text('username');
        $username   ->setLabel('Имя пользователя')
                    ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password   ->setLabel('Пароль')
                    ->setRequired(true);

        $login = new Zend_Form_Element_Submit('login');
        $login->setLabel('Login');

        $this->addElement($username);
        $this->addElement($password);
        $this->addElement($login);

        $this->setMethod('post');
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().('login'));
        }
}
