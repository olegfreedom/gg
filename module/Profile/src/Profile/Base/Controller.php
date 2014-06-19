<?php
namespace Profile\Base;

use Zend\Mvc\MvcEvent;

class Controller extends \Base\Mvc\Controller
{    
    /**
     * Init session
     */
    public function __construct(){
        $this->sessionStart();
        
        $this->pushTitle('Личный кабинет');

        if($this->getUserId() > 0){
            $this->session('profile')->messages = $this->load('Messages', 'profile')->getCount($this->getUserId());
            $this->session('profile')->balance = $this->load('Wallet', 'profile')->getBalance($this->getUserId());
        }

        if($this->session()->catalog === null){
            $this->session()->catalog = 'uslugi';
        }

        // add css and js
        $this->addHeadLink('/css/medialoader/application.css', false);
        //$this->addHeadScript('/js/tinymce/jquery.tinymce.min.js', false);
        //$this->addHeadScript('/js/libs/bootstrap-formhelpers-phone.js', false);
        $this->addHeadScript('/js/medialoader/application.js', false);
    }
    
    /**
     * Check for login
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(MvcEvent $e){
        if($this->sessionStatus() === false){
            return new \Zend\Session\Exception\RuntimeException('Can\'t create session');
        }
        
        if($this->session('profile')->authId === null && 
            $this->routeNames('controller') != 'login' && 
            $this->routeNames('controller') != 'registration' && 
            $this->routeNames('controller') != 'forgot' && 
            $this->routeNames('controller') != 'error' &&
            $this->routeNames('controller') != 'guest' &&
            !($this->routeNames('controller') == 'wallet' && $this->routeNames('action') == 'postlink')

           ){
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array('controller' => 'error'))
                         );
        }

        return parent::onDispatch($e);
    }
    
    /**
     * Return authId
     * @return null|int
     */
    protected function getUserId(){
        return ($this->session('profile')->authId !== null) ? $this->session('profile')->authId : 0;
    }
    
    /**
     * Return authUsername
     * @return null|string
     */
    protected function getUserNаme(){
        return ($this->session('profile')->authUsername !== null) ? $this->session('profile')->authUsername : null;
    }
    
    /**
     * @param integer $id
     */
    protected function setUserId($id = 0){
        if((int)$id > 0){
            $this->session('profile')->authId = (int)$id;
        }
    }
    
    /**
     * @param string $username
     */
    protected function setUserNаme($username = null){
        if($username !== null){
            $this->session('profile')->authUsername = $username;
        }
    }
    
    /**
     * @param array $user
     */
    protected function setCurrentUser($user = null){
        if($user !== null){
            $this->session('profile')->authCurrentUser = $user;
        }
    }

}