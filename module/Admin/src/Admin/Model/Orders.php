<?php
namespace Admin\Model;

class Orders extends \Application\Base\Model {
    
    const ORDERS_PER_PAGE = 20;

    /**
     * Get orders
     * @return array|null
     */
    public function getOrders() {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = null;

        $select = $this->select()
                    ->from(array('o' => self::TABLE_ORDERS))
                    ->columns(array(
                                'id', 
                                'user_id',
                                'amount',
                                'status',
                                'timestamp'
                              ))
                    ->join(
                       array('u' => self::TABLE_USER),
                       'u.id = o.user_id',
                       array(
                           'username'
                       )
                    )
                    ->order('o.timestamp desc');

        $result = $this->fetchSelect($select);

        if($result){
            $ret = $result;
        }

        return $ret;
    }
    
    /**
     * Set status
     * @param int $id
     * @return bool
     */
    public function changeStatus($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($id > 0){            
            $select = $this->select()
                           ->from(self::TABLE_ORDERS)
                           ->columns(array(
                               'status'
                           ))
                           ->where(array('id' => $id))
                           ->limit(1);
            
            $result = $this->fetchRowSelect($select);
            
            if (isset($result['status']) && $result['status'] !== 'y') {
                $this->load('Wallet','profile')->approveOrder($id);
            }
        }
       
        return (bool)$ret;
    }
    
     /**
     * get paginator
     * @param int $page
     * @return null|array
     */
    public function getPaginator($page = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $count = 0;
        $page = ($page > 0) ? $page : 1;

        $select = $this->select()
                       ->from(self::TABLE_ORDERS)
                       ->columns(array(
                           'count' => $this->expr('count(*)')
                       ));                       

        $count = (int)$this->fetchOneSelect($select);

        return $this->paginator($page, $count, self::ORDERS_PER_PAGE);
    }
}
