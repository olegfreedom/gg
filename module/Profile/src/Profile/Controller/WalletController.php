<?php
namespace Profile\Controller;

require_once(BASE_PATH.'/data/paysystem/paysys/kkb.utils.php');

class WalletController extends \Profile\Base\Controller
{  
    public function __construct(){
        parent::__construct();
        
        $this->pushTitle('Кошелек');
    }
    
    public function indexAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        $userId = $this->getUserId();
        
        $ret = array(
            'userBalance' => $this->session('profile')->balance,
            'getPriceRate' => $this->load('Wallet', 'profile')->const->PRICE_RATE,
            'config_path' => $this->load('Wallet','profile')->getPaymentConfigPath(),
            'username' => $this->load('Users', 'admin')->getUsername($userId)
        );

        return $this->view($ret);
    }
    
    public function successAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $ret = array();

        return $this->view($ret);
    }
    
    public function errorAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        $ret = array();

        return $this->view($ret);
    }
    
    public function getContentAction() {
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        $this->isAjax();

        $params = array(
            'userId' => $this->getUserId(),
            'config_path' => $this->load('Wallet','profile')->getPaymentConfigPath(),
            'amount' => $this->p_int('amount'),
            'accept' => $this->p_int('accept'),
            'order_id' => 0,
            'content' => ''
        );

        $error = array();
        
        $validItem = $this->load('Validator')->validBetween($params['amount'], 100, 99999999999999999900);
        if ($validItem == false) {
            $error['amount'] = $validItem;
        }
        
        if ($params['accept'] === 0) {
            $error['accept'] = false;
        }
        
        $status = (sizeof($error) > 0 ? false : true);
        
        
        if ($status === true) { 
            $params['order_id'] = $this->load('Wallet', 'profile')->makeOrder($params['userId'], $params['amount']); 
            $params['content'] = $this->load('Wallet','profile')->getContent($params['order_id'], $params['amount'], $params['config_path']);
        }

        $ret = array(
            'status' => $status,
            'error' => $error,
            'content' => $params['content']
        );
        
        return $this->json($ret);
    }
    
    public function postlinkAction() {
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        
        require_once($this->load('Wallet','profile')->getPaymentClassPath());
        $configPath = $this->load('Wallet','profile')->getPaymentConfigPath();
        
        $response = isset($_REQUEST['response']) ? $_REQUEST['response'] : '';
        $result = process_response(stripslashes($response),$configPath);
        $order_id = 0;
        
        if ($result['PAYMENT_RESPONSE_CODE'] == '00' && $result['CHECKRESULT'] == '[SIGN_GOOD]') {
            $order_id = (int)($result['ORDER_ORDER_ID']);
            if ($order_id > 0) {
                $check_response = $this->load('Wallet','profile')->checkPayment($order_id, $configPath);
                if ($check_response === true) {
                    $this->load('Wallet','profile')->approveOrder($order_id);
                }
            }
        }
        die();
    }
}
