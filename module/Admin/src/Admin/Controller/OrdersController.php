<?php
namespace Admin\Controller;

//require_once BASE_PATH.'/data/paysystem/paysys/kkb.utils.php';
class OrdersController extends \Admin\Base\Controller
{  
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $params = array(
            'page' => $this->p_int('page',1)
        );
        
        $ret = array(
            'getOrders' => $this->load('Orders','admin')->getOrders(),
            'config_path' => BASE_PATH.'/data/paysystem/paysys/config.txt',
            'paginator' => $this->load('Orders', 'admin')->getPaginator($params['page'])
        );

        return $this->view($ret);
    }
    
    function changeStatusAction() {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $this->load('Orders', 'admin')->changeStatus($this->p_int('id'));
        
        return $this->redirect()->toUrl(
            $this->easyUrl(array('action'=>'index', 'id' => null), array(), true)
        );
    }
}
