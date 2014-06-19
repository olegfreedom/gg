<?php
namespace Profile\Controller;

class RegistrationController extends \Profile\Base\Controller
{
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        
        if($this->session('profile')->authId !== null){
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array())
                         );
        }
        if($this->p_int('registration-form') === 1){
            $params = array(
                'username' => $this->p_string('username'),
                'password' => $this->p_string('password'),
                'retry_password' => $this->p_string('retry_password'),
                'activation_url' => $this->easyUrl(array('action' => 'activation', 'key' => '_SET_KEY_')) 
            );

            $check = $this->check($params);
            if($check['status'] == true){
                $this->load('Registration', 'profile')->authUser($params);
            }
        
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array('action' => 'confirm'))
                         );
        }
        
        return $this->view();
        
    }

    public function confirmAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $ret = array();

        return $this->view($ret);
    }

    public function activationAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $key = $this->p_string('key');
        if (!empty($key)) {
            $user = $this->load('Registration', 'profile')->activationUser($key);
            if ($user != null){
                $this->setUserId($user['id']);
                $this->setUserNаme($user['username']);
                
                return $this->redirect()
                             ->toUrl(
                                 $this->easyUrl(array('controller' => 'settings'))
                             );
            }
        } else {
           return $this->redirect()
                        ->toUrl(
                            $this->easyUrl(array('controller' => 'error', 'action' => 'registration'))
                        );
        }
    }

    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $params = array(
            'username' => $this->p_string('username'),
            'password' => $this->p_string('password'),
            'retry_password' => $this->p_string('retry_password'),
            'type' => $this->p_int('type')
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
            }else{
                $validItem = $this->load('User', 'profile')->checkLogin($params['username']);
                if($validItem == true){
                    $error['username'] = $validItem;
                }
            }
        }

        $validItem = $this->load('Validator')->validIdentical($params['password'], $params['retry_password']);
        if($validItem == false){
            $error['retry_password'] = $validItem;
        }else{
            $validItem = $this->load('Validator')->validStringLength($params['password'], 5, 100);
            if($validItem == false){
                $error['password'] = $validItem;
            }
        }
        
        if ($params['type'] === 0) {
            $error['type'] = false;
        }

        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        
        return $ret;
    }
}
