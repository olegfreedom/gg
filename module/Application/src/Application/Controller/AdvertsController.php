<?php
namespace Application\Controller;

class AdvertsController extends \Application\Base\Controller
{
    public function __construct(){
        parent::__construct();

        $this->pushTitle('Главная');
    }

    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        return $this->redirect()->toUrl(
            $this->easyUrl(array('controller' => 'catalog'))
        );
    }
    
    public function viewAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $id = $this->p_int('id');
        $advertsType = $this->p_int('type');
        $userId = $this->getUserId();
        
        if($id > 0){            
            $this->load('AdvertCounter')->up($id);
            $this->load('Statistics', 'profile')->updateStatistic($id, 'view');


            $ret = array(
                'advert' => $this->load('Adverts')->getOne($id, $userId),
                'related' => $this->load('Adverts')->getRelated($id),
                'getGallery' => $this->load('AdvertGallery', 'admin')->generateURL($id),
                'userId' => $userId,
                'typeList' => $this->load('AdvertType', 'admin')->get($this->session()->catalog),
                'advertsType' => $advertsType,
                'advertComments' => $this->load('Adverts')->getAdvertComments($id),
                'advertOptions' => $this->load('Options', 'admin')->getAdvertViewOptions($id),
                'currentUser' => $this->load('Users', 'admin')->getNameAndUsername($userId)
            );

            return $this->view($ret);
        }else{
            $this->indexAction();
        }
    }
    
    public function phoneAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        $this->isAjax();
        
        $id = $this->p_int('id');
        
        $ret = array(
            'phone' => $this->load('AdvertPhone', 'admin')->get($id)
        );
        
        return $this->json($ret);
    }
}
