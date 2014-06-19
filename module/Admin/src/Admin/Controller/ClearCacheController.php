<?php
namespace Admin\Controller;

class ClearCacheController extends \Admin\Base\Controller
{    
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $ret = $this->run();
        
        return $this->view($ret);
    }
    
    public function removeAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $this->run(true);
        
        return $this->redirect()->toUrl(
            $this->easyUrl(array('action' => 'index'))
        );
    }
    
    /**
     * @param bool $delete
     * @return array
     */
    private function run($delete = false){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $dir = PUBLIC_PATH.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR;
        $it = new \RecursiveDirectoryIterator($dir);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        $dir_count = 0;
        $file_count = 0;
        foreach($files as $file) {
            if($file->getFilename() === '.' || $file->getFilename() === '..' ||  $file->getFilename() === '.gitignore'){
                continue;
            }
            if($file->isDir()){
                if($delete === true){
                    rmdir($file->getRealPath());
                }
                $dir_count++;
            }else{
                if($delete === true){
                    unlink($file->getRealPath());
                }
                $file_count++;
            }
        }
        
        return array(
                    'dir_count' => $dir_count,
                    'file_count' => $file_count
                );
    }
}
