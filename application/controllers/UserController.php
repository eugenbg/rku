<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function registerAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity->role == 'admin') {
            $request = $this->getRequest();
            if ($request->isPost()){
                //заносим нового юзера в базу
                $form = new Form_Register();
                if ($form->isValid($request->getPost())) {
                    $username = $form->getValue('username');
                    $password = $form->getValue('password');
                    $client_id = $form->getValue('client_id');
                    $modelUsers = new Zend_Db_Table('users');
                    $modelUsers->insert(array('user_id'=>'','username'=>$username, 'password'=>$password, 'data_client'=>$client_id, 'role'=>'user'));
                    $this->view->message = 'Новый пользователь добавлен успешно';
                }
            } else {
                //вывод формы регистрации
                $form = new Form_Register;
                $this->view->form = $form;
            }
        } else {
            $this->view->message = 'Недостаточно прав';
        }
    }

    public function deleteAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity->role == 'admin') {
            $modelUsers = new Zend_Db_Table('users');
            $params = $this->_request->getParams();
            if ($params['id']>0){
                $where = $modelUsers->getAdapter()->quoteInto('user_id = ?', $params['id']);
                $modelUsers->delete($where);
                $this->_redirect('user/delete');
            }
            $result = $modelUsers->fetchAll()->toArray();
            $this->view->users = $result;
        }
    }

    public function changePasswordAction()
    {
        // action body
    }


}







