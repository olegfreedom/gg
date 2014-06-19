<?php
namespace Admin\Model;

class AdvertType extends \Application\Base\Model
{
    private $defaultTypeId;

    /**
     * Get Type
     * @param string $catalog
     * @return array|null
     */
    public function get($catalog = null)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = null;

        $select = $this->select()
            ->from(self::TABLE_ADVERTS_TYPE)
            ->columns(array('id', 'name', 'catalog'))
            ->order('name desc');

        if ($catalog !== null && (is_string($catalog) || is_numeric($catalog))) {
            $id = $this->load('AdvertCategory', 'admin')->getCategoryIdByUrl($catalog);
            $select->where(array('catalog' => $id));
        }
        $result = $this->fetchSelect($select);

        if ($result) {
            $ret = $result;
        }


        return $ret;
    }
    
    
    /**
     * Get one type
     * @param int $id
     * @return null|array
     */
    public function getOne($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if($id > 0){
            $select = $this->select()
                           ->from(self::TABLE_ADVERTS_TYPE)
                           ->columns(array('id', 'name', 'catalog'))
                           ->where(array('id' => $id))
                           ->limit(1);    
            
            $result = $this->fetchRowSelect($select);

            if($result){
                $ret = $result;
            }
        }

        return $ret;
    }   
    
    /**
     * Get one type
     * @param int $id
     * @return null|array
     */
    public function getTypeById($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if($id > 0){
            $select = $this->select()
                           ->from(self::TABLE_ADVERTS)
                           ->columns(array('type'))
                           ->where(array('id' => $id))
                           ->limit(1);    
            
            $result = $this->fetchOneSelect($select);

            if($result){
                $ret = $result;
            }
        }

        return $ret;
    }    
    
    /**
     * Get default type id
     * @param int|string $catalog
     * @return null|integer
     */
    public function getDefaultTypeId($catalog = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if($this->defaultTypeId === null){            
            $select = $this->select()
                           ->from(self::TABLE_ADVERTS_TYPE)
                           ->columns(array('id'))
                           ->order('name desc')
                           ->limit(1);
            if ($catalog !== null){
                $catalogId = $this->load('AdvertCategory', 'admin')->getCategoryIdByUrl($catalog);
                $select->where(array(
                        'catalog' => $catalogId
                ));
            }

            $result = (int)$this->fetchOneSelect($select);

            if($result > 0){
                $this->defaultTypeId = $result;
            }
        }

        return $this->defaultTypeId;
    }    

    /**
     * Edit type
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function edit($id = 0, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($id > 0 && $params !== null){      
            $update = $this->update(self::TABLE_ADVERTS_TYPE)
                           ->set($params)
                           ->where(array('id' => $id));

            $ret = $this->execute($update);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Add type
     * @param array $params
     * @return bool
     */
    public function add($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if($params !== null){ 
            $insert = $this->insert(self::TABLE_ADVERTS_TYPE)
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

        return $this->load('ForeignKeys', 'admin')->check(self::TABLE_ADVERTS_TYPE, $id);
    }

    /**
     * Remove type
     * @param int $id
     * @return bool
     */
    public function remove($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($id > 0 && $this->checkKeys($id) == false){            
            $delete = $this->delete(self::TABLE_ADVERTS_TYPE)
                           ->where(array('id' => $id));
            
            $ret = $this->execute($delete);
        }
        
        return (bool)$ret;
    }
}
