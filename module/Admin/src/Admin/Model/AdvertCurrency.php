<?php
namespace Admin\Model;

class AdvertCurrency extends \Application\Base\Model
{

    /**
     * Get сurrency
     * @return array|null
     */
    public function get(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
                     
        $select = $this->select()
                       ->from(self::TABLE_ADVERTS_CURRENCY)
                       ->columns(array('id', 'name'))
                       ->order('name asc');

        $result = $this->fetchSelect($select);

        if($result){
            $ret = $result;
        }
        
        return $ret;
    }

    /**
     * get one сurrency
     * @param int $id
     * @return array
     */
    public function getOne($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $res = null;
        
        if($id > 0){
            $select = $this->select()
                    ->from(self::TABLE_ADVERTS_CURRENCY)
                    ->columns(array('id', 'name'))
                    ->where(array('id' => $id))
                    ->limit(1);    

            $result = $this->fetchRowSelect($select);
            
            if($result){
                $res = $result;
            }
        }
        
        return $res;
    }
    
    /**
     * Edit сurrency
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function edit($id = 0, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if($id > 0 && $params !== null){            
            $update = $this->update(self::TABLE_ADVERTS_CURRENCY)
                           ->set($params)
                           ->where(array('id' => $id));

            $ret = $this->execute($update);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Add сurrency
     * @param array $params
     * @return bool
     */
    public function add($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if($params !== null){ 
            $insert = $this->insert(self::TABLE_ADVERTS_CURRENCY)
                           ->values($params);

            $ret = $this->execute($insert);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Remove check
     * @param int $id
     * @return bool
     */
    public function checkKeys($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        return $this->load('ForeignKeys', 'admin')->check(self::TABLE_ADVERTS_CURRENCY, $id);
    }
    
    /**
     * Remove сurrency
     * @param int $id
     * @return bool
     */
    public function remove($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        if($id > 0 && $this->checkKeys($id) == false){            
            $delete = $this->delete(self::TABLE_ADVERTS_CURRENCY)
                           ->where(array('id' => $id));
            
            $ret = $this->execute($delete);
        }
        
        return (bool)$ret;
    }

}
