<?php
namespace Application\Controller;

class PaymentAgreementController extends \Application\Base\Controller
{
    public function __construct(){
        parent::__construct();
        
        $this->pushTitle('Пользовательское соглашение');
    }
    
    public function indexAction() {
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $ret = array(
            'content' => $this->load('Pages', 'admin')->getOne(4),
        );

        return $this->view($ret);
    }

}
