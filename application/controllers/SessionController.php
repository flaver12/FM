<?php
namespace FM\App\controllers;

use FM\Framework\Controller\BaseController;
use FM\App\Forms\SingUpForm;
use FM\App\Forms\LoginForm;
use FM\App\Models\User;
use FM\Framework\Session;

class SessionController extends BaseController {

    public function registerUserAction() {

        if($this->request->isPost()) {
            $username = $this->request->getPost('username');
            $password = sha1($this->request->getPost('password'));

            $user = new User();
            $user->setUsername($username);
            $user->setPassword($password);

            //save user
            $user->save($user);

        } else {
            $this->set('singUpForm', new SingUpForm());
        }

    }

    public function loginUserAction() {

      if ($this->request->isPost()) {

          $username = $this->request->getPost('username');
          $password = sha1($this->request->getPost('password'));

          $user = User::findBy(array('username' => $username, 'password' => $password));

          //AS_TODO: when user not found then handel that
          if($user != array()) {
            Session::set('user', User::find($user[0]->getId()));
            $this->response->redirect('/');
          }

      } else {
          $this->set('loginForm', new LoginForm());
      }

    }

    public function logoutUserAction() {
        Session::delete('user');
        $this->response->redirect('/');
    }

}
