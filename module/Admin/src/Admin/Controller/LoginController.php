<?php
namespace Admin\Controller;

class LoginController extends \Admin\Base\Controller
{
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $this->unsetAuth();
        $ret = array();
        
        
        if($this->p_int('login-form') === 1){
            $params = array(
                'username' => $this->p_string('username'),
                'password' => $this->p_string('password')
            );
            
            $user = $this->load('Login', 'admin')->authUser($params);
        
            return $this->setAuth($user);
        }
        return $this->view($ret);
    }
    
    public function logoutAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $this->unsetAuth();
            
        return $this->redirect()
                     ->toUrl(
                         $this->easyUrl(array('controller' => 'login'))
                     );
    }
    
    /**
     * Unset login
     */
    private function unsetAuth(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($this->session('admin')->authId !== null){
            $this->session('admin')->authId = null;
            $this->session('admin')->authUsername = null;
        }
    }
    
    /**
     * Set login
     */
    private function setAuth($user = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if(isset($user['id']) && isset($user['username'])){  
            $this->session('admin')->authId = (int)$user['id'];
            $this->session('admin')->authUsername = (string)$user['username'];
            
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array())
                         );
        }else{
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array('controller' => 'login'))
                         );
        }
    }
    
    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $params = array(
            'username' => $this->p_string('username'),
            'password' => $this->p_string('password')
        );
        
        $user = $this->load('Login', 'admin')->authUser($params);
        
        $error = array();
        
        $validItem = $this->load('Validator')->validStringLength($params['username'], 5, 100);
        if($validItem == false){
            $error['username'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validStringLength($params['password'], 5, 100);
        if($validItem == false){
            $error['password'] = $validItem;
        }
        
        if (!$user) {
            $user = false;
        }
        $user = (!$user ? false : true);
        
        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error,
            'user' => $user
        );
        
        return $this->json($ret);
    }
}
