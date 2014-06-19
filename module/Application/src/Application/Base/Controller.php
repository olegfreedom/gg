<?php
namespace Application\Base;

use Zend\Mvc\MvcEvent;

class Controller extends \Base\Mvc\Controller
{        
    /**
     * Init session
     */
    public function __construct(){
        $this->sessionStart();
        
        if($this->session()->catalog === null){
            $this->session()->catalog = 'uslugi';
            $this->load('Adverts')->generateCategoryMenu('uslugi');
        }
    }
    
    /**
     * Check session
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(MvcEvent $e){
        if($this->sessionStatus() === false){
            return new \Zend\Session\Exception\RuntimeException('Can\'t create session');
        }
        if($this->getCookie('loginID') && $this->getUserId() === 0){
            $key = $this->load('Login', 'profile')->authUserViaCookie($this->getCookie('loginID'));
            $this->redirect()
                ->toUrl(
                    $this->easyUrl(array(
                        'module' => 'profile',
                        'controller' => 'login',
                        'action' => 'login-by-key',
                        'key' => $key
                    ))
                );
        }
        return parent::onDispatch($e);
    }

    /**
     * Return profile authId
     * @return integer
     */
    protected function getUserId() {
        return  ($this->session('profile')->authId !== null) ? $this->session('profile')->authId : 0;
    }
}