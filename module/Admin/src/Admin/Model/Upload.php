<?php
namespace Admin\Model;

use Base\Filter;

class Upload extends \Application\Base\Model
{ 
    private $imgType = array('png', 'jpg', 'jpeg');
    
    /**
     * Upload file
     * @param mixed $file
     * @param null|array $type
     * @param array $params
     * @param bool $crop
     * @return null|string
     */
    public function save($file = null, $type = null, $params = array('width' => 1280, 'height' => 800), $crop = false){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if($file !== null && isset($file['error']) && $file['error'] == 0){
            $image = new Filter\ImageClass();
            
            $image_ext = strtolower($file['name']);
            $image_ext = explode('.', $image_ext);
            $image_ext = end($image_ext);
            
            $file_name = $image->generateImageName($image_ext, $this->dir());
            $file_type = explode('/', $file['type']);
            $file_type = end($file_type);
            
            if((is_array($type) && in_array($file_type, $type)) || $type === null){
                $upload = $this->upload($file_name, $file['tmp_name']);

                if($upload === true){
                    if(in_array($file_type, $this->imgType)){
                        $params['method'] = ($crop === true) ? Filter\ImageResize::METHOD_SCALE_MIN : Filter\ImageResize::METHOD_SCALE_MAX;
                        $this->resize($file_name, $params);
                    }
                    
                    $ret = $file_name;
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * Upload file
     * @param string $file_name
     * @param string $tmp
     * @return boolean
     */
    private function upload($file_name, $tmp){
        $ret = false;
            
        if(!empty($file_name) && !empty($tmp)){            
            if(move_uploaded_file($tmp, $this->dir().$file_name)){
                $ret = true;
            }
        }
        
        return $ret;
    }
    
    /**
     * resize image
     * @param string $file_name
     * @param array $params
     */
    private function resize($file_name, $params){
        $resize = new Filter\ImageResize($params);
        $resize->filter($this->dir().$file_name);
    }
    
    /**
     * Upload dir
     * @return string
     */
    private function dir(){
        return BASE_PATH.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR;
    }
    
    /**
     * Unlink file
     * @param string $url
     * @return boolean
     */
    public function unlink($url){
        $ret = false;
        
        if(is_file($this->dir().$url)){
            @unlink($this->dir().$url);
            $ret = true;
        } 
        
        return $ret;
    }
}