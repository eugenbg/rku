<?php

class Form_Register extends Zend_Form
{

    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('Register');

        $username = new Zend_Form_Element_Text('username');
        $username   ->setLabel('Имя нового пользователя')
                    ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password   ->setLabel('Пароль')
                    ->setRequired(true);

        $client_id = new Zend_Form_Element_Text('client_id');
        $client_id   ->setLabel('Id клиента, например К1906_Виз_6')
                    ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Добавить');

        $this->addElement($username);
        $this->addElement($password);
        $this->addElement($client_id);
        $this->addElement($submit);

        $this->setMethod('post');

        }


}

