<?php
namespace Profile\Controller;

class LoginController extends \Profile\Base\Controller
{    
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $this->unsetAuth();

        if($this->p_int('login-form') === 1){
            $params = array(
                'username' => $this->p_string('username'),
                'password' => $this->p_string('password'),
                'remember' => $this->p_select('remember', 'n', array('y', 'n'))
            );
            $user = null;
            $user = $this->load('Login', 'profile')->authUser($params);
            if ($this->getCookie('loginID') == $user['key']) {
                return $this->setAuth($user);
            }else{
                $check = $this->check($params);
                if($check['status'] == true){
                    if($user === null){
                        return $this->redirect()
                            ->toUrl(
                                $this->easyUrl(array('controller' => 'error', 'action' => 'login'))
                            );
                    }
                }
            }
            return $this->setAuth($user);
        }
        
        return $this->redirect()
                     ->toUrl(
                         $this->easyUrl(array('controller' => 'error'))
                     );
    }
    /**
     * User logout
     */
    public function logoutAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $this->unsetAuth();
        $this->setCookie('loginID','' , (time() - 60*60*24*30), '/');

        return $this->redirect()->toUrl('/');
    }
    
    /**
     * Unset login
     */
    private function unsetAuth(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($this->session('profile')->authId !== null){
            $this->session('profile')->authId = null;
            $this->session('profile')->authUsername = null;
            $this->session('profile')->authCurrentUser = null;
        }
    }
    
    /**
     * Set login
     */
    public function setAuth($user = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $header = null;
        if(isset($user['id']) && isset($user['username'])){
            $this->session('profile')->authId = (int)$user['id'];
            $this->session('profile')->authUsername = (string)$user['username'];
            $this->session('profile')->authCurrentUser = $this->load('Users', 'admin')->getNameAndUsername((int)$user['id']);

            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array())
                         );
        }else{
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array('controller' => 'error', 'action' => 'login'))
                         );
        }
    }

    public function loginByKeyAction() {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $key = $this->p_string('key');

        if (!empty($key)) {
            $user = $this->load('Forgot', 'profile')->getUserByKey($key);
            if(isset($user['id']) && isset($user['username'])){
                return $this->setAuth($user);
            }
        }
        return $this->redirect()
            ->toUrl(
                $this->easyUrl(array('controller' => 'error', 'action' => 'login'))
            );
    }
    
    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $params = array(
            'username' => $this->p_string('username'),
            'password' => $this->p_string('password')
        );
        
        $ret = $this->check($params);
        
        return $this->json($ret);
    }
    
    
    /**
     * @param array $params
     * @return array
     */
    private function check($params = array()){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $error = array();
        
        $validItem = $this->load('Validator')->validStringLength($params['username'], 5, 100);
        if($validItem == false){
            $error['username'] = $validItem;
        }else{
            $validItem = $this->load('Validator')->validEmail($params['username']);
            if($validItem == false){
                $error['username'] = $validItem;
            }
        }
        
        $validItem = $this->load('Validator')->validStringLength($params['password'], 5, 100);
        if($validItem == false){
            $error['password'] = $validItem;
        }
        
        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );

        return $ret;
    }
}
