<?php
namespace Application\Controller;

class IndexController extends \Application\Base\Controller
{
    public function __construct(){
        parent::__construct();

        $this->pushTitle('Главная');
        
    }

    public function indexAction($params = array()){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $ret = array(
            'typeList' => $this->load('AdvertType', 'admin')->get($this->session()->catalog),
            'advertsType' => isset($params['type']) ? $params['type'] : $this->load('AdvertType', 'admin')->getDefaultTypeId()
        );

        return $this->view($ret, 'application/index/index.phtml');
    }

    public function addSubscribeAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $this->isAjax();

        $ret = false;

        $params = array(
            'email' => $this->p_string('subscribe_email')
        );

        if (!empty($params['email'])) {
            $ret = $this->load('SubscribeEmails')->add($params);
        }

        $ret = array(
            'status' => $ret
        );

        return $this->json($ret);
    }
    
    public function searchAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $params = array(
            'type' => $this->p_int('type')
        );

        return $this->indexAction($params);
    }

    public function validatorAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $params = array(
            'subscribe_email' => $this->p_string('subscribe_email'),
        );

        $error = array();


        $validItem = $this->load('Validator')->validStringLength($params['subscribe_email'], 5, 100);
        if($validItem == false){
            $error['subscribe_email'] = $validItem;
        }else{
            $validItem = $this->load('Validator')->validEmail($params['subscribe_email']);
            if($validItem == false){
                $error['subscribe_email'] = $validItem;
            }else{
                $validItem = $this->load('SubscribeEmails')->checkEmail($params['subscribe_email']);
                if($validItem == true){
                    $error['subscribe_email'] = $validItem;
                }
            }
        }

        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );

        return $this->json($ret);
    }
}
