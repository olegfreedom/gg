<?php
namespace Profile\Model;

class Wallet extends \Application\Base\Model
{
    const PRICE_RATE = 100;
    const CURRENCY_ID = 398;
    
    /**
     * Get User Info
     * @param int $userId
     * @return array
     */
    public function getBalance($userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
            if((int)$userId > 0){
                $select = $this->select()
                               ->from(self::TABLE_USER)
                               ->columns(array(
                                   'balance'
                               ))
                               ->where(array(
                                   'id' => $userId
                               ))
                               ->limit(1);
                
                $result = $this->fetchOneSelect($select);
                
                if($result){
                    $ret = $result;
                }
            }
        
        return $ret;
    }

    /**
     * Return path to kkb.utils.php
     * @return $string path to kkb.utils.php
     */ 
    public function getPaymentClassPath() {
        return BASE_PATH.'/data/paysystem/paysys/kkb.utils.php';
    }
    
    /**
     * Return path to config.txt
     * @return $string path to config.txt
     */
    public function getPaymentConfigPath() {
        return BASE_PATH.'/data/paysystem/paysys/config.txt';
    }
    
    /** Return base64 string for payment service
     * @param integer $order_id
     * @param integer $currency_id
     * @param integer $amount
     * @param string $config_path
     * @return string
     */
    public function getContent($order_id, $amount, $config_path, $currency_id = self::CURRENCY_ID) {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        require_once ($this->getPaymentClassPath());
        
        return process_request($order_id,$currency_id,$amount,$config_path);
    }
    
    /** Return base64 string for payment service
     * @param integer $order_id
     * @param integer $approval_code
     * @param integer $currency_id
     * @param integer $amount
     * @param string $config_path
     * @return string
     */
    public function checkPayment($order_id, $config_file) {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
    
        require_once ($this->getPaymentClassPath());
        
        $xml = process_check_payment($order_id, $config_file);
        $url = 'https://3dsecure.kkb.kz/jsp/remote/checkOrdern.jsp'; 
        $fullUrl = $url.'?'.urlencode($xml);
        
        $result = $this->curl($fullUrl, array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true
        ));
        
        $out = process_response(stripslashes($result),$config_file);
        
        return (isset($out['RESPONSE_PAYMENT']) && $out['RESPONSE_PAYMENT'] == 'true') ? true : false;
        
    }
    
    /**
     * Make an order
     * @param int $userId
     * @param int $amount
     * @return array
     */
    public function makeOrder($userId = 0, $amount = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if((int)$userId > 0 && (int)$amount > 0){
            $params = array (
                'user_id' => $userId,
                'amount' => $amount,
                'status' => 'n',
                'timestamp' => $this->load('Date', 'admin')->getDateTime()
            );

            $insert = $this->insert(self::TABLE_ORDERS)
                           ->values($params);

            $this->execute($insert);
            $id = $this->insertId($insert);
   
            if($id > 0) {
                $ret = $id;
            }
        }
        
        return $ret;
    }
    
    /**
     * Approve an order
     * @param int $order_id
     * @return array
     */
    public function approveOrder($order_id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if((int)$order_id > 0) {
            $select = $this->select()
                           ->from(array('o' => self::TABLE_ORDERS))
                           ->columns(array(
                               'user_id',
                               'amount'
                           ))
                           ->join(
                               array('u' => self::TABLE_USER),
                               'u.id = o.user_id',
                               array(
                                   'username'
                               )
                           )
                           ->where(array(
                               'o.id' => $order_id,
                               'o.status' => 'n'
                           ))
                           ->limit(1);

            $result = $this->fetchRowSelect($select);

            if ($result && isset($result['amount']) && isset($result['user_id'])){

                $update = $this->update(self::TABLE_ORDERS)
                               ->set(array(
                                   'status' => 'y'
                               ))
                               ->where(array('id' => $order_id));

                $updateOrder = $this->execute($update);
                
                if($updateOrder){                    
                    $update = $this->update(self::TABLE_USER)
                                   ->set(array(
                                       'balance' => $this->expr('(balance + '.(int)$result['amount'].')')
                                   ))
                                   ->where(array('id' => (int)$result['user_id']));

                    $updateUser = $this->execute($update);

                    if (isset($result['username']) && $updateUser) {
                        $this->load('SendEmail','admin')->refill($result['username'],$result['amount']);
                    }
                }
                
                
                $ret = true;
            }
        }
        
        return $ret;
    }
}
