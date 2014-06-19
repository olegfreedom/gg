<?php
namespace Base\Filter;

class ImageClass 
{
    const WATERMARK_IMG = 'watermark.png';
    const DEFAULT_IMG = 'no_photo.png';
    const HSHIFT = 5;
    const VSHIFT = 5;
    const DIRECTORY_NAME_LENGTH = 3;
    const SUB_DIRECTORY_DEPTH = 10;

    /**
     * Render image from url
     * @param string $url
     * @param int $w
     * @param int $h
     * @param string $crop "y" or "n"
     * @param string $default_img
     * @return string
     */
    public function get($url = null, $w = 0, $h = 0, $crop = 'n', $default_img = self::DEFAULT_IMG){
        $ret = $this->getUrl(DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$default_img);
        
        if($url !== null && !empty($url)){
            // image path
            $image_path = BASE_PATH.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.$url;
            $watermark_path = PUBLIC_PATH.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.self::WATERMARK_IMG;

            if(is_file($image_path) && is_file($watermark_path)){            
                // image time
                $image_time = filemtime($image_path);
                $watermark_time = filemtime($watermark_path);

                // determine image format
                list($width, $height, $type) = getimagesize($image_path); 
                
                $cacheDir = DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
                $cacheImage = $cacheDir.$url;

                // check image from cache
                if(
                    is_file(PUBLIC_PATH.$cacheImage) && 
                    is_readable(PUBLIC_PATH.$cacheImage) && 
                    filemtime(PUBLIC_PATH.$cacheImage) > $image_time && 
                    filemtime(PUBLIC_PATH.$cacheImage) > $watermark_time
                  ){
                    $ret = $this->getUrl($cacheImage);
                }else{
                    $this->createImageDirs(PUBLIC_PATH.$cacheImage);
                    
                    $image = $this->render($image_path, $width, $height, $type, $watermark_path);
                    file_put_contents(PUBLIC_PATH.$cacheImage, $image);
                    
                    $ret = $this->getUrl($cacheImage);
                }

                // Small image
                if((int)$w > 0 && (int)$h > 0 && in_array($crop, array('y', 'n'))){
                    $copyDir = DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'images_'.$w.'_'.$h.(($crop=='y') ? '_crop' : '').DIRECTORY_SEPARATOR;
                    $copyImage = $copyDir.$url;
                    
                    if(
                       is_file(PUBLIC_PATH.$copyImage) && 
                       is_readable(PUBLIC_PATH.$copyImage) && 
                       filemtime(PUBLIC_PATH.$copyImage) > $image_time && 
                       filemtime(PUBLIC_PATH.$copyImage) > $watermark_time
                      ){
                        $ret = $this->getUrl($copyImage);
                    }else{
                        $this->createImageDirs(PUBLIC_PATH.$copyImage);
                        
                        if(copy(PUBLIC_PATH.$cacheImage, PUBLIC_PATH.$copyImage)){
                            $method = ($crop == 'y') ? ImageResize::METHOD_SCALE_MIN : ImageResize::METHOD_SCALE_MAX; 
                            $resize = new ImageResize(array('width' => $w, 'height' => $h, 'method' => $method));
                            $resize->filter(PUBLIC_PATH.$copyImage);
                            
                            $ret = $this->getUrl($copyImage);
                        }
                    }
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * 
     * @param string $image_path
     * @param int $width
     * @param int $height
     * @param int $type
     * @param string $watermark_path
     * @return mixed
     * @throws \Exception
     */
    private function render($image_path, $width, $height, $type, $watermark_path){        
        // creating png image of watermark
        $watermark = imagecreatefrompng($watermark_path);   

        // getting dimensions of watermark image
        $watermark_width = imagesx($watermark);  
        $watermark_height = imagesy($watermark);
        
        $image = $this->imageCreate($image_path, $type);
        //something went wrong 
        if ($image === false) {
            return false;
        } 
        
        // placing the watermark 5px from bottom and right
        $dest_x = $width - $watermark_width - self::HSHIFT;  
        $dest_y = $height - $watermark_height - self::VSHIFT;
        
        // blending the images together
        imagealphablending($image, true);
        imagealphablending($watermark, true); 

        if($watermark_width < $width && $watermark_height < $height){
            // creating the new image
            imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);            
        }

        // Define outputing function
        ob_start();
        switch ($type)
        {
            case IMAGETYPE_GIF:
                imagegif($image);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image, null, 100); // best quality
                break;
            case IMAGETYPE_PNG:
                imagepng($image, null, 0); // no compression
                break;
            default:
                ob_end_clean();
                throw new \Exception('Unknow image type');
        }
        
        // destroying and freeing memory
        imagedestroy($image);  
        imagedestroy($watermark); 
        
        $finalImage = ob_get_clean();
        
        return $finalImage;          
    }
    
    /**
     * 
     * @param string $image_path
     * @param int $type
     * @return bool|mixed
     * @throws \Exception
     */
    private function imageCreate($image_path, $type){
        if (!is_file($image_path) || !is_readable($image_path)) {
           throw new \Exception('Unable to open file');
           return false;
        }

        switch ($type) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($image_path);
                break;

            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($image_path);
                break;

            case IMAGETYPE_PNG:
                return imagecreatefrompng($image_path);
                break;
        }
        
        throw new \Exception('Unsupport image type');
        return false;
    }
    
    /**
     * Generate unique image name
     * @param string $imageExt Image Extension
     * @param string $dir      Directory
     *
     * @return null|string
     */
    public function generateImageName($imageExt = null, $dir = null){
        $ret = null;
        
        if ($imageExt !== null && $dir !== null) {

            $image = md5(uniqid(rand().'_', true)).'_'.microtime(true).'.'.$imageExt;
            $ret   = $this->generateDirsForImageName($image, $dir);

        }
        
        return $ret;
    }
    
    /**
     * Generate and create directories for image
     * @param string $image
     * @param string $dir
     * @return null|string
     */
    private function generateDirsForImageName($image = null, $dir = null){
        $ret = null;
        
        if($image !== null && $dir !== null){
            $imageDir = '';
            
            for($i = 0; $i < self::SUB_DIRECTORY_DEPTH; $i++){
                $imageDir .= substr($image, ($i*self::DIRECTORY_NAME_LENGTH), self::DIRECTORY_NAME_LENGTH).DIRECTORY_SEPARATOR;
            }
            
            $ret = $imageDir.$image;
            
            $this->createImageDirs($dir.$ret);
        }
        
        return $ret;
    }
    
    /**
     * Create directories for image
     * @param string $image
     */
    private function createImageDirs($image = null){          
        if($image !== null && !empty($image)){
            $dir = dirname($image);
            if(!empty($dir) && !file_exists($dir)){                
                mkdir($dir, 0777, true);
            }
        }
    }
    
    /**
     * Get full URL
     * @param string $image
     * @return string
     */
    private function getUrl($image = null){        
        if($image !== null){
            $storage = new \Base\Storage();
            $image = (($storage->basePath !== null) ? $storage->basePath : '').$image;
        }
        
        return $image;
    }
}
