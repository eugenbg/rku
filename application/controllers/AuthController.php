<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function loginAction()
    {
        if(Zend_Auth::getInstance()->hasIdentity()){
            $this->_redirect('index/index');
        }

        $form = new Form_LoginForm();

        $request = $this->getRequest();
        if($request->isPost()) {
            if($form->isValid($this->_request->getPost())){
                $authAdapter = $this->getAuthAdapter();
                $username = $form->getValue('username');
                $password = $form->getValue('password');
                $authAdapter->setIdentity($username);
                $authAdapter->setCredential($password);

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                if($result->isValid()) {
                    $identity = $authAdapter->getResultRowObject();

                    $authStorage = $auth->getStorage();
                    $authStorage->username = $identity->username;
                    $authStorage->password = $identity->password;
                    $authStorage->client = $identity->data_client;
                    $authStorage->role = $identity->role;
                    $authStorage->write($authStorage);

                    $this->_redirect('');
                }
                else {
                    $this->view->errorMessage = 'Ошибка: неверный логин или пароль';
                }
            }
        }


        $this->view->form = $form;


    }

    public function registerAction()
    {
        // action body
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('');
    }

    private function getAuthAdapter()
    {
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $authAdapter->setTableName('users')
                    ->setIdentityColumn('username')
                    ->setCredentialColumn('password');
        return $authAdapter;
    }

}







