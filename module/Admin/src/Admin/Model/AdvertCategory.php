<?php
namespace Admin\Model;

class AdvertCategory extends \Application\Base\Model
{
    protected static $breadcrumbsArray = null;
    protected static $parentsArray = null;
    protected static $subIdArray = null;
    protected static $categoryArray = null;

    /**
     * Get categories
     * @param string $url
     * @return array|null
     */
    public function get($url = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;

        $ids = $this->getSubIdArray($url);
        

        $select = $this->select()
                       ->from(self::TABLE_ADVERTS_CATEGORIES)
                       ->columns(array(
                           'id',
                           'name',
                           'url',
                           'parent_id'
                       ))
                       ->where(array(
                           'id' => $ids,
                           $this->where()
                               ->greaterThan('id', 1)
                               ->and
                               ->notEqualTo('id', $this->getCategoryIdByUrl($url))
                       ))
                       ->order('id asc');

        $result = $this->fetchSelect($select);
        if($result){
            $ret = $this->generateCategoryList($result, $this->getCategoryIdByUrl($url));
        }
        return $ret;
    }

    /**
     * Get main categories
     * @return array|null
     */
    public function getMainCategories(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = null;

        $select = $this->select()
            ->from(array('c' => self::TABLE_ADVERTS_CATEGORIES))
            ->columns(array(
                'id',
                'name',
                'url',
                'parent_id',
                'disabled' => $this->subQuery(
                                    $this->select()
                                        ->from(array('ac' => self::TABLE_ADVERTS_CATEGORIES))
                                        ->columns(array('id'))
                                        ->where(array(
                                            $this->where()
                                            ->equalTo('ac.parent_id', 'c.id', self::SQL_COL_IDENTIFIER, self::SQL_COL_IDENTIFIER)
                                        ))
                                        ->limit(1)
                              )
            ))
            ->where(array(
                $this->where()
                    ->greaterThan('c.id', 1)
                    ->and
                    ->equalTo('c.parent_id', 1)
            ))
            ->having('disabled is not null')
            ->order('c.id asc');

        $result = $this->fetchSelect($select);
        if ($result){
            $ret = $result;
        }

        return $ret;
    }

    /**
     * Get category ID by URL
     * @param string|int $url
     * @return null
     */
    public function getCategoryIdByUrl($url = null){

        if ($url !== null && is_string($url) && (int)$url === 0){
            if(!isset(self::$categoryArray[$url])){
                $select = $this->select()
                               ->from(self::TABLE_ADVERTS_CATEGORIES)
                               ->columns(array('id'))
                               ->where(array('url' => $url))
                               ->limit(1);
                $id = $this->fetchOneSelect($select);
                self::$categoryArray[$url] = $id;
            }
            $url = self::$categoryArray[$url];
        }

        return (int)$url;
    }
    
    /**
     * Recursively generate category
     * @param array $array
     * @param integer $parent
     * @return array|null
     */
    private function generateCategoryList($array = array(), $parent = 1){        
        $ret = null;

        $parent = $this->getCategoryIdByUrl($parent);
        if(is_array($array)){
            foreach($array as $item){
                if($item['parent_id'] == $parent){
                    if($ret === null){
                        $ret = array();
                    }
                    /* search sub category */
                    $sub = $this->generateCategoryList($array, $item['id']);
                    if(is_array($sub)){
                        $item['subcategory'] = $sub;
                    }
                    $ret[] = $item;
                }
            }            
        }
        
        return $ret;
    }

    /**
     * get one category
     * @param integer $id
     * @return array
     */
    public function getOne($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $res = null;

        $id = $this->getCategoryIdByUrl($id);

        if($id > 1){
            $select = $this->select()
                    ->from(self::TABLE_ADVERTS_CATEGORIES)
                    ->columns(array(
                        'id', 
                        'name',
                        'url',
                        'parent_id'
                    ))
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
     * Edit category
     * @param integer $id
     * @param array $params
     * @return bool
     */
    public function edit($id = 0, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if($id > 1 && $params !== null && isset($params['name'])){
            $params['url'] = $this->translit($params['name']);
            
            $update = $this->update(self::TABLE_ADVERTS_CATEGORIES)
                           ->set($params)
                           ->where(array('id' => $id));

            $ret = $this->execute($update);
            
            $this->checkUrl($params['url'], $id);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Add category
     * @param array $params
     * @return bool
     */
    public function add($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if($params !== null && isset($params['name'])){
            $params['url'] = $this->translit($params['name']);

            $insert = $this->insert(self::TABLE_ADVERTS_CATEGORIES)
                           ->values($params);

            $ret = $this->execute($insert);
            $id = $this->insertId();
            
            $this->checkUrl($params['url'], $id);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Check and update url
     * @param string $url
     * @param integer $id
     */
    private function checkUrl($url = null, $id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($url !== null && !empty($url) && $id > 0){
            $select = $this->select()
                           ->from(self::TABLE_ADVERTS_CATEGORIES)
                           ->columns(array('id'))
                           ->where(array(
                               'url' => $url,
                               $this->where()->notEqualTo('id', $id)
                           ))
                           ->limit(1);
            
            $result = $this->fetchOneSelect($select);
            
            if($result){
                $update = $this->update(self::TABLE_ADVERTS_CATEGORIES)
                               ->set(array('url' => ($url.'-'.$id)))
                               ->where(array('id' => $id));
                
                $this->execute($update);
            }
        }
    }


    /**
     * Remove check
     * @param integer $id
     * @return bool
     */
    public function checkKeys($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        return $this->load('ForeignKeys', 'admin')->check(self::TABLE_ADVERTS_CATEGORIES, $id);
    }
    
    /**
     * Remove category
     * @param integer $id
     * @return bool
     */
    public function remove($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($id > 1 && $this->checkKeys($id) == false){            
            $delete = $this->delete(self::TABLE_ADVERTS_CATEGORIES)
                           ->where(array('id' => $id));
            
            $ret = $this->execute($delete);
        }
        
        return (bool)$ret;
    }
    
    /**
     * Get breadcrumbs array
     * @param integer $id
     * @return null|array
     */
    public function getBreadcrumbsArray($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;

        if((int)$id > 0){
            if(self::$breadcrumbsArray === null){                
                $select = $this->select()
                               ->from(self::TABLE_ADVERTS_CATEGORIES)
                               ->columns(array(
                                   'id',
                                   'name',
                                   'url',
                                   'parent_id'
                               ))
                               ->where(array(
                                   $this->where()->greaterThan('id', 1)
                               ))
                               ->order('id asc');

                $result = $this->fetchSelect($select);
                
                if($result){
                    self::$breadcrumbsArray = $result;
                }
            }
            
            $ret = $this->generateBreadcrumbsArray(self::$breadcrumbsArray, $id);            
        }
        
        return $ret;
    }
    
    /**
     * Recursively generate breadcrumbs
     * @param array $array
     * @param integer $id
     * @return null|array
     */
    private function generateBreadcrumbsArray($array = array(), $id = 1){
        $ret = null;
        
        if(is_array($array) && $id > 1){
            foreach($array as $item){
                if($item['id'] == $id){
                    if($ret === null){
                        $ret = array();
                    }
                    
                    /* search parent category */
                    $parent = $this->generateBreadcrumbsArray($array, $item['parent_id']);
                    if(is_array($parent)){
                       $ret = $parent;
                    }
                    
                    $ret[] = array(
                                'id' => $item['id'],
                                'name' => $item['name'],
                                'url' => $item['url']
                             );
                }
            }            
        }
        
        return $ret;
    }
    
    /**
     * Get parents array
     * @param integer $id
     * @return null|array
     */
    public function getParentsArray($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;

        if((int)$id > 0){
            if(self::$parentsArray === null){                
                $select = $this->select()
                               ->from(self::TABLE_ADVERTS_CATEGORIES)
                               ->columns(array(
                                   'id',
                                   'parent_id'
                               ))
                               ->where(array(
                                   $this->where()->greaterThan('id', 1)
                               ))
                               ->order('id asc');

                $result = $this->fetchSelect($select);
                
                if($result){
                    self::$parentsArray = $result;
                }
            }
            
            $ret = $this->generateParentsArray(self::$parentsArray, $id);            
        }
        
        return $ret;
    }
    
    /**
     * Recursively generate parents
     * @param array $array
     * @param integer $id
     * @return null|array
     */
    private function generateParentsArray($array = array(), $id = 1){
        $ret = null;
        
        if(is_array($array) && $id > 1){
            foreach($array as $item){
                if($item['id'] == $id){
                    if($ret === null){
                        $ret = array();
                    }
                    
                    /* search parent category */
                    $parent = $this->generateParentsArray($array, $item['parent_id']);
                    if(is_array($parent)){
                       $ret = $parent;
                    }
                    
                    $ret[] = $item['id'];
                }
            }            
        }
        
        return $ret;
    }
  
    /**
     * Get sub array
     * @param integer $id
     * @return array
     */
    public function getSubIdArray($id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = array();

        $id = $this->getCategoryIdByUrl($id);
        if((int)$id > 0){
            if(self::$subIdArray === null){                
                $select = $this->select()
                               ->from(self::TABLE_ADVERTS_CATEGORIES)
                               ->columns(array(
                                   'id', 
                                   'parent_id'
                               ))
                               ->where(array(
                                   $this->where()->greaterThan('id', 1)
                               ))
                               ->order('id asc');

                $result = $this->fetchSelect($select);

                if($result){
                    self::$subIdArray = $result;
                }
            }

            $sub = $this->generateSubIdArray(self::$subIdArray, $id);

            if(is_array($sub)){
                $ret = $sub;
            }

            $ret[] = (int)$id;
        }
        
        return $ret;
    }
    
    /**
     * Recursively generate sub id
     * @param array $array
     * @param integer $parent
     * @return null|array
     */
    private function generateSubIdArray($array = array(), $parent = 1){
        $ret = null;

        if(is_array($array)){
            foreach($array as $item){
                if($item['parent_id'] == $parent){
                    if($ret === null){
                        $ret = array();
                    }
                    /* search sub category */
                    $sub = $this->generateSubIdArray($array, $item['id']);
                    if($ret === null){
                        $ret = array();
                    }
                    if(is_array($sub)){
                        foreach($sub as $val){
                            $ret[] = $val;
                        }
                    }
                    $ret[] = (int)$item['id'];
                }
            }
        }
        return $ret;
    }

    public function getCategorySelect($array = null, $disabled = false, $tab = null)
    {
        $ret = null;
        if ($array !== null) {
            foreach ($array as $item) {
                if (isset($array) && is_array($array) && isset($tab)) {
                    $setDisabled = (bool)(isset($disabled) && $disabled == true && isset($item['subcategory']));

                    if ($ret === null) {
                        $ret = array();
                    }

                    $ret[] = array('disabled' => $setDisabled, 'name' => $tab.$item['name'], 'url' => $item['url'], 'id' =>  $item['id']);

                    if (isset($item['subcategory'])) {
                        $sub = $this->getCategorySelect($item['subcategory'], $setDisabled, $tab . '&nbsp;&bull;&nbsp;');

                        if ($sub !== null) {
                            foreach ($sub as $val) {
                                $ret[] = array('disabled' => $val['disabled'], 'name' => $val['name'], 'url' => $val['url'], 'id' =>  $val['id']);
                            }

                        }
                    }
                }
            }
        }
        return $ret;
    }


}
