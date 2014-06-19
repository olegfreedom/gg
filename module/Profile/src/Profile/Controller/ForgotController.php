<?php
namespace Profile\Controller;

class ForgotController extends \Profile\Base\Controller
{    
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        if($this->session{'profile'}->authId !== null){
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array())
                         );
        }
        
        $ret = array();
        
        if($this->p_int('forgot-form') === 1){
            $params = array(
                'username' => $this->p_string('username'),
                'link' => $this->easyUrl(array('action' => 'confirm-recovery', 'key'=> '__KEY__'))
            );
            
            $check = $this->check($params);
            if($check['status'] == true){
                $user = $this->load('Forgot', 'profile')->confirmRecovery($params);
                
                if($user){
                    return $this->redirect()
                                 ->toUrl(
                                     $this->easyUrl(array('action' => 'recover'))
                                 );
                }
            }
        }
        return $this->view($ret);
    }
    
    public function confirmRecoveryAction() {
        $key = $this->p_string('key');
        
        $user = $this->load('Forgot', 'profile')->getUserByKey($key);
        
        $params = array(
            'username' => $user['username']
        );
        $user = $this->load('Forgot', 'profile')->recover($params);
            if($user){
                return $this->redirect()
                             ->toUrl(
                                 $this->easyUrl(array('action' => 'success'))
                             );
            } else {
                return $this->redirect()
                             ->toUrl(
                 $this->easyUrl(array('action' => 'error'))
             );
            }   
    }
    
    public function successAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        $ret = array();
        
        return $this->view($ret);
    }
    
    public function recoverAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__); 
        
        $ret = array();
        
        return $this->view($ret);
    }
    
    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $params = array(
            'username' => $this->p_string('username')
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
                if($validItem == false){
                    $error['username'] = false;
                    $error['username_taken'] = false;
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
