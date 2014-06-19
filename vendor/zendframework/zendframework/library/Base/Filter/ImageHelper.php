<?php
namespace Base\Filter;

use Zend\View\Helper\AbstractHelper;

class ImageHelper extends AbstractHelper
{    
    /**
     * Render image from url
     * @param string $url
     * @param int $w
     * @param int $h
     * @param boolean $crop default false
     * @param string $default_img
     * @return string
     */
    public function __invoke($url = null, $w = 0, $h = 0, $crop = false, $default_img = ImageClass::DEFAULT_IMG) {
        $image = new ImageClass();
        $crop = ($crop === true) ? 'y' : 'n';
        return $image->get($url, $w, $h, $crop, $default_img);
    }
}