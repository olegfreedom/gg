<?php
namespace Application\Model;

class Validator extends \Application\Base\Model
{    
    /**
     * Validate Email
     * @param string $val
     * @return bool
     */
    public function validEmail($val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\EmailAddress();
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Hostname
     * @param string $val
     * @return bool
     */
    public function validHostname($val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\Hostname();
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Digits
     * @param int|float $val
     * @return bool
     */
    public function validDigits($val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\Digits();
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate InArray
     * @param string $val
     * @param array $array
     * @return bool
     */
    public function validInArray($val = null, $array = array()){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null && sizeof($array) > 0){
            $validator = new \Zend\Validator\InArray(array('haystack' => $array));
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Ip
     * @param string $val
     * @return bool
     */
    public function validIp($val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\Ip();
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate NotEmpty
     * @param string $val
     * @return bool
     */
    public function validNotEmpty($val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\NotEmpty();
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Regex
     * @param string $val
     * @param regexp $pattern
     * @return bool
     */
    public function validRegex($val = null, $pattern = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null && $pattern !== null){
            $validator = new \Zend\Validator\Regex($pattern);
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate StringLength
     * @param string $val
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function validStringLength($val = null, $min = 1, $max = 100){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\StringLength(array('min' => $min, 'max' => $max));
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Between
     * @param string $val
     * @param int $min
     * @param int $max
     * @param bool $inclusive
     * @return bool
     */
    public function validBetween($val = null, $min = 0, $max = 100, $inclusive = true){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\Between(array('min' => $min, 'max' => $max, 'inclusive' => $inclusive));
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate GreaterThan
     * @param string $val
     * @param int $min
     * @param bool $inclusive
     * @return bool
     */
    public function validGreaterThan($val = null, $min = 0, $inclusive = false){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\GreaterThan(array('min' => $min, 'inclusive' => $inclusive));
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate LessThan
     * @param string $val
     * @param int $max
     * @param bool $inclusive
     * @return bool
     */
    public function validLessThan($val = null, $max = 100, $inclusive = false){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($val !== null){
            $validator = new \Zend\Validator\LessThan(array('max' => $max, 'inclusive' => $inclusive));
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * Validate Identical
     * @param string $origin
     * @param string $val
     * @return bool
     */
    public function validIdentical($origin = null, $val = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($origin !== null && $val !== null){
            $validator = new \Zend\Validator\Identical($origin);
            
            if($validator->isValid($val)){
                $ret = true;
            }
        }
        
        return $ret;
    }
}