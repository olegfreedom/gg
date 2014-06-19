<?php
namespace Admin\Base;

use Zend\Mvc\MvcEvent;

class Controller extends \Base\Mvc\Controller
{
    /**
     * Init session
     */
    public function __construct(){
        $this->sessionStart();
        
        if ($this->session('admin')->catalog === null){
            $this->session('admin')->catalog = 'all';
        }        
    }

    /**
     * Check for login
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(MvcEvent $e){
        if($this->sessionStatus() === false){
            return new \Zend\Session\Exception\RuntimeException('Can\'t create session');
        }

        if($this->session('admin')->authId === null && $this->routeNames('controller') != 'login'){
            return $this->redirect()
                         ->toUrl(
                             $this->easyUrl(array('controller' => 'login'))
                         );
        }

        return parent::onDispatch($e);
    }

    protected function getUserId(){
        return ($this->session('admin')->authId !== null) ? $this->session('admin')->authId : 0;
    }
    
    protected function getUserNаme(){
        return $this->session('admin')->authUsername;
    }
}