<?php
namespace Base\Mvc;

use Zend\View\Helper\AbstractHelper;

class SessionHelper extends AbstractHelper
{    
    /**
     * Set value to session array
     * @param string $storage (optional, default: 'application')
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @example $this->session({storage})->{name}={value}; Set value
     * @example $catalog=$this->session({storage})->{name}; Get value
     */
    public function __invoke($storage = 'application') {
        return new SessionClass($storage);
    }
}