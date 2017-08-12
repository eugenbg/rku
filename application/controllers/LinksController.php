<?php

class LinksController extends Zend_Controller_Action
{

    public function init()
    {
        $this->identity = Zend_Auth::getInstance()->getIdentity();
    }

    public function indexAction()
    {
        if (!isset($this->identity)){
            $this->view->message = "Авторизуйтесь на сайте, используя ваши логин и пароль";
            return;
        } else {
            $this->view->loggedin = true;
        }

        $form = new Form_ReportForm();
        $this->view->productCodes = $form->getProductCodes();
        $this->view->controller = $this;
    }
}

