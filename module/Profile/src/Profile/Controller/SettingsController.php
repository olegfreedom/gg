<?php
namespace Profile\Controller;

class SettingsController extends \Profile\Base\Controller
{
    public function __construct(){
        parent::__construct();
        
        $this->pushTitle('Настройки профиля');
    }
    
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        $ret = array(
            'getEdit' => $this->load('User', 'profile')->getOne($this->getUserId())
        );
        
        return $this->view($ret);
    }
    
    public function editAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        if($this->p_int('settings-form') === 1){
            $params = array(
                'username' => $this->p_string('username'),
                'old_password' => $this->p_string('old_password'),
                'password' => $this->p_string('password'),
                'retry_password' => $this->p_string('retry_password')
            );

            $check = $this->check($params);

            if($check['status'] == true){
                $res = $this->load('User', 'profile')->edit($params, $this->getUserId(), $this);
                if($res === true){
                    return $this->redirect()
                                 ->toUrl(
                                     $this->easyUrl(array('action' => 'success'))
                                 );
                }else if($res === 'change-email'){
                    return $this->redirect()
                        ->toUrl(
                            $this->easyUrl(array('action' => 'email-activate'))
                        );
                } else {
                    return $this->redirect()
                        ->toUrl(
                            $this->easyUrl(array('action' => 'index'))
                        );
                }
            }
        }
        
        return $this->redirect()
                     ->toUrl(
                         $this->easyUrl(array('action' => 'error'))
                     );
    }
    
    public function successAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        $ret = array();
        
        return $this->view($ret);
    }

    public function emailActivateAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $ret = array();

        return $this->view($ret);
    }

    public function changeEmailAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        $newEmail = $this->p_string('email');
        $key = $this->p_string('key');

        if ($newEmail !== null && $key !== null){
            $change = $this->load('User', 'profile' )->changeEmail($newEmail, $key);
            if ($change){
                // update current user
                $this->setUserNаme($newEmail);
                $this->setCurrentUser($this->load('Users', 'admin')->getNameAndUsername($this->getUserId()));
                return $this->redirect()
                    ->toUrl(
                        $this->easyUrl(array('action' => 'success'))
                    );
            }
        }
        return $this->redirect()
            ->toUrl(
                $this->easyUrl(array('action' => 'error'))
            );
    }
    
    public function errorAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        $ret = array();
        
        return $this->view($ret);
    }
    
    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $params = array(
            'username' => $this->p_string('username'),
            'old_password' => $this->p_string('old_password'),
            'password' => $this->p_string('password'),
            'retry_password' => $this->p_string('retry_password')
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
                $validItem = $this->load('User', 'profile')->checkLogin($params['username'], $this->getUserId());
                if($validItem == true){
                    $error['username'] = $validItem;
                }
            }
        }
        
        $validItem = $this->load('Validator')->validStringLength($params['old_password'], 5, 100);
        if($validItem == false){
            $error['old_password'] = $validItem;
        }
        
        if(!empty($params['password'])){
            $validItem = $this->load('Validator')->validIdentical($params['password'], $params['retry_password']);
            if($validItem == false){
                $error['retry_password'] = $validItem;
            }else{                
                $validItem = $this->load('Validator')->validStringLength($params['password'], 5, 100);
                if($validItem == false){
                    $error['password'] = $validItem;
                }
            }
        }
        
        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        
        return $ret;
    }
}
