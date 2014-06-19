<?php
namespace Base\Func;

/**
 * Default methods
 */
trait TraitDefault
{    
    /**
     * save log
     * @param str $str __CLASS__.'\\'.__FUNCTION__
     */
    public final function log($str){
        return new \Base\Log();
    }
    
    /**
     * get storage
     * @return \Base\Storage
     */
    public final function storage(){
        return new \Base\Storage();
    }
     
    /**
     * Returns site's base path.
     * 
     * @return string
     */
    public final function basePath(){
        return ($this->storage()->basePath !== null) ? $this->storage()->basePath : '';
    }
         
    /**
     * Returns site email.
     * @return string
     */
    public final function getSiteEmail(){        
        return isset($this->storage()->siteConfig['email']) ? $this->storage()->siteConfig['email'] : 'info@'.$_SERVER['HTTP_HOST'];
    }
    
    /**
     * Returns site name.
     * @return string
     */
    public final function getSiteName(){
        return isset($this->storage()->siteConfig['name']) ? $this->storage()->siteConfig['name'] : 'Site Name';
    } 
    
    /**
     * Load models
     * @param str $model
     * @param str $module
     * @return object
     * @throws \Zend\Mvc\Exception\InvalidArgumentException
     */
    public final function load($model = null, $module = 'application') {
        return \Base\Mvc\ModelsLoader::load($model, $module);
    }
    
    /**
     * Debuger
     * @param mixed $obj
     * @param boolean $isDie
     */
    public final function debug($obj, $isDie = true){        
        \Zend\Debug\Debug::dump($obj);
        
        if($isDie === true){
            die();
        }
    }
    
    /**
     * Json Decode
     * @param json $value
     * @param boolean $toArray
     * @return array|mixed
     */
    public final function jsonDecode($value, $toArray = true){
        if($this->isJson($value) === true){            
            $type = ($toArray === true) ? \Zend\Json\Json::TYPE_ARRAY : \Zend\Json\Json::TYPE_OBJECT;
            return \Zend\Json\Json::decode($value, $type);
        }else{
           return $value; 
        }
    }
    
    /**
     * Json Encode
     * @param array $value
     * @return json|mixed
     */
    public final function jsonEncode($value){
        if(is_array($value)){
            return \Zend\Json\Json::encode($value);
        }else{
            return $value;
        }
    }
    
    
    /**
     * Check json string
     * @param json $json
     * @return boolean
     */
    public final function isJson($json){
        $ret = false;
        
        @json_decode($json);
        if(json_last_error() === JSON_ERROR_NONE){
            $ret = true;
        }
        
        return $ret;
    } 
    
    /**
     * Set Cookie
     * @param string $name
     * @param string $value
     * @param string $expire
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $http_only
     * @return boolean
     */
    public final function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false, $http_only = false){
        return setcookie($name, $value, $expire, $path, $domain, $secure, $http_only);
    }
    
    /**
     * Get Cookie
     * @param string $keyName
     * @param mixed $default
     * @return mixed
     */
    public final function getCookie($keyName, $default = null){
        return isset($_COOKIE[$keyName]) ? $_COOKIE[$keyName] : $default;
    }
    
    
    /**
     * Start session
     */
    public final function sessionStart(){
        $expire = (60*60*4);
        ini_set('session.cache_expire', $expire);
        ini_set('session.gc_maxlifetime', $expire);
        session_start();
    }
    
    /**
     * Get session status
     * @return boolean
     */
    public final function sessionStatus(){
        return (session_status() === PHP_SESSION_ACTIVE) ? true : false;
    }
    
    /**
     * Set value to session array
     * @param string $storage (optional, default: 'application')
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @example $this->session({storage})->{name}={value}; Set value
     * @example $catalog=$this->session({storage})->{name}; Get value
     */
    public final function session($storage = 'application'){
        return new \Base\Mvc\SessionClass($storage);
    }
}
