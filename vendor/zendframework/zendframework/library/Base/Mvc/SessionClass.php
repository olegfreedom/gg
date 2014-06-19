<?php
namespace Base\Mvc;

class SessionClass
{
    private $storage = null;
    
    public function __construct($storage = 'application') {
        $this->storage = $storage;
    }
    
    /**
     * Get session value by key
     * @param string $name
     * @return mixed
     */
    public function __get($name = null) {
        return ($name !== null && isset($_SESSION[$this->storage][$name])) ? $_SESSION[$this->storage][$name] : null;
    }
    
    /**
     * Set value to session array
     * @param string $name
     * @param mixed $value
     */
    public function __set($name = null, $value = null) {
        if($name !== null){
            if(!isset($_SESSION[$this->storage])){
                $_SESSION[$this->storage] = array();
            }
            $_SESSION[$this->storage][$name] = $value;
        }
        
    }
}
