<?php
namespace Admin\Model;

class Adverts extends \Application\Base\Model
{
    const POST_PER_PAGE = 20;
    
    private function getSQL(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $select = $this->select()
                       ->from(array('a' => self::TABLE_ADVERTS))
                       ->columns(array())
                       ->join(
                           array('ac' => self::TABLE_ADVERTS_CATEGORIES),
                           'a.category = ac.id',
                           array()
                       )
                       ->join(
                           array('at' => self::TABLE_ADVERTS_TYPE),
                           'a.type = at.id',
                           array()
                       )
                       ->join(
                           array('al' => self::TABLE_ADVERTS_LOCATION),
                           'a.location = al.id',
                           array()
                       )
                       ->join(
                           array('cur' => self::TABLE_ADVERTS_CURRENCY),
                           'a.currency = cur.id',
                           array()
                       )
                       ->join(
                            array('u' => self::TABLE_USER),
                            'a.user_id = u.id',
                            array()
                       );
        
        return $select;
    }
    
    /**
     * Set params
     * @param \Zend\Db\Sql\Select $select
     * @param array $params
     * @return \Zend\Db\Sql\Select
     */
    private function setParams(\Zend\Db\Sql\Select $select, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if(isset($params['category'])){
            $catArray = $this->load('AdvertCategory', 'admin')->getSubIdArray($params['category']);
            $categories = is_array($catArray) ? $catArray : $params['category'];
            $select->where(array('ac.id' => $categories));
        }

        if(isset($params['type']) && (int)$params['type'] > 0){
            $select->where(array('at.id' => $params['type']));
        }

        if(isset($params['user_id']) && (int)$params['user_id'] > 0){
            $select->where(array('a.user_id' => $params['user_id']));
        } 
        
        return $select;
    }

    /**
     * Get all list
     * @param array $params
     * @param int $userId
     * @param bool $favorite
     * @return null|array
     */
    public function getList($params = null, $userId = 0, $favorite = false){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;

        $select = $this->getSQL();

        if($select instanceof \Zend\Db\Sql\Select){
            $select->columns(array(
                       'id',
                       'name',
                       'price',
                       'status',
                       'user_id',
                       'contact_name',
                       'counter',
                       'mark_lifetime',
                       'rating' => $this->subQuery(
                                $this->select()
                                    ->from(array('ra_sub' => self::TABLE_ADVERTS))
                                    ->columns(array(
                                        'count' => $this->expr('count(ra_sub.id)')
                                    ))
                                    ->join(
                                        array('u_sub' => self::TABLE_USER),
                                        'u_sub.id = ra_sub.user_id',
                                        array()
                                    )
                                    ->where(array(
                                        'ra_sub.status' => 'y',
                                        $this->where()
                                            ->greaterThanOrEqualTo('ra_sub.timestamp', $this->expr('a.timestamp'))
                                            ->equalTo('ra_sub.type', $this->expr('a.type'))
                                    ))
                                    ->order('ra_sub.timestamp desc')
                            ),
                       'rating_top' => $this->subQuery(
                            $this->select()
                                ->from(array('rt_sub' => self::TABLE_ADVERTS))
                                ->columns(array(
                                    'count' => $this->expr('count(rt_sub.id)')
                                ))
                                ->join(
                                    array('u_sub' => self::TABLE_USER),
                                    'u_sub.id = rt_sub.user_id',
                                    array()
                                )
                                ->where(array(
                                    'rt_sub.status' => 'y',
                                    $this->where()
                                        ->greaterThan('rt_sub.top_lifetime', $this->load('Date', 'admin')->getDateTime())
                                        ->greaterThanOrEqualTo('rt_sub.timestamp', $this->expr('a.timestamp'))
                                        ->equalTo('rt_sub.type', $this->expr('a.type'))
                                        ->addPredicate(
                                            $this->where()
                                                ->equalTo('u_sub.level', self::USERS_LEVEL_ADMIN)
                                                ->or
                                                ->greaterThan('rt_sub.top_lifetime', $this->load('Date', 'admin')->getDateTime())
                                        )
                                ))
                                ->order('rt_sub.timestamp desc')
                        ),
                       'rating_cat' => $this->subQuery(
                                $this->select()
                                    ->from(array('rc_sub' => self::TABLE_ADVERTS))
                                    ->columns(array(
                                        'count' => $this->expr('count(rc_sub.id)')
                                    ))
                                    ->join(
                                        array('u_sub' => self::TABLE_USER),
                                        'u_sub.id = rc_sub.user_id',
                                        array()
                                    )
                                    ->where(array(
                                        'rc_sub.category' => $this->expr('a.category'),
                                        'rc_sub.status' => 'y',
                                        $this->where()
                                            ->greaterThanOrEqualTo('rc_sub.timestamp', $this->expr('a.timestamp'))
                                            ->addPredicate(
                                                $this->where()
                                                    ->equalTo('u_sub.level', self::USERS_LEVEL_ADMIN)
                                            )


                                    ))
                                    ->order('rc_sub.timestamp desc')
                            ),
                       'img_url' => $this->subQuery(
                                    $this->select()
                                        ->from(array('ra_sub' => self::TABLE_ADVERTS_GALLERY))
                                        ->columns(array('url'))
                                        ->where(array(
                                            'advert_id' => $this->expr('a.id')
                                        ))
                                        ->order('ra_sub.id asc')
                                        ->limit(1)
                                ),
                       'top_lifetime' => $this->expr('unix_timestamp(a.top_lifetime)'),
                       'mark_lifetime' => $this->expr('unix_timestamp(a.mark_lifetime)'),
                       'created' => $this->expr('unix_timestamp(a.created)')
                   ))
                   ->addJoinColumns('ac', array(
                        'category_id' => 'id',
                        'category' => 'name'
                   ))
                   ->addJoinColumns('at', array(
                        'type_id' => 'id',
                        'type' => 'name'
                   ))
                   ->addJoinColumns('al', array(
                        'location_id' => 'id',
                        'location' => 'name'
                   ))
                   ->addJoinColumns('cur', array(
                        'currency_id' => 'id',
                        'currency' => 'name'
                   ))
                   ->addJoinColumns('u', array(
                        'user_status' => 'status',
                        'user_level' => 'level'
                   ))
                   ->order('a.timestamp desc');

            if($favorite == true && $userId > 0){
                $select->join(
                    array('f' => self::TABLE_FAVORITES),
                    'f.advert_id = a.id',
                    array(
                        'favorite_id' => 'id'
                    )
                )
                    ->where(array(
                        'f.user_id' => $userId,
                    ))
                    ->reset('order')
                    ->order('f.timestamp desc');
            }
            
            // set params
            $select = $this->setParams($select, $params);

            if(isset($params['page']) && (int)$params['page'] > 0){
                $select->limitPage($params['page'], self::POST_PER_PAGE);
            }
            
            $result = $this->fetchSelect($select);
            
            if($result){
               foreach($result as &$item){
                    
                    $item['top_days_left'] = $this->load('Date', 'admin')->daysLeft($item['top_lifetime']);
                    $item['top_days_text'] = $this->load('Date', 'admin')->daysText($item['top_days_left']);
                    
                    $item['mark_days_left'] = $this->load('Date', 'admin')->daysLeft($item['mark_lifetime']);
                    $item['mark_days_text'] = $this->load('Date', 'admin')->daysText($item['mark_days_left']);

                    $item['mark']  = ($item['mark_lifetime'] > time() ? 'y' : 'n');
                    
                    $item['active_status'] = (bool)($item['status'] == 'y' || $item['user_level'] == self::USERS_LEVEL_ADMIN);
                    $item['is_admin'] = (bool)($item['user_level'] == self::USERS_LEVEL_ADMIN);
                    
                    $item['is_top'] = ($item['top_lifetime'] > time()) ? true : false;
                    $item['is_not_active'] = ($item['status'] == 'n') ? true : false;
                    
                    if ($item['active_status'] == true) {
                       $item['rating_text'] = $item['rating'];
                    } else {
                       $item['rating_text'] = 0;
                    }
                    $breadcrumbsArray = $this->load('AdvertCategory', 'admin')->getBreadcrumbsArray($item['category_id']);
                    if(is_array($breadcrumbsArray)){
                        $item['breadcrumbs'] = $breadcrumbsArray;
                    }
               }
               
               $ret = $result; 
            }
        }
        return $ret;
    }
    
    /**
     * Get one
     * @param int $id
     * @param int $userId
     * @return null|array
     */
    public function getOne($id = 0, $userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        $select = $this->getSQL();
        
        if($select instanceof \Zend\Db\Sql\Select){
            if((int)$id > 0){                
                $select->columns(array(
                           'id',
                           'category',
                           'type',
                           'location',
                           'name',
                           'description',
                           'contact_name',
                           'price',
                           'currency',
                           'status',
                           'user_id',
                           'top_lifetime' => $this->expr('unix_timestamp(a.top_lifetime)'),
                           'mark_lifetime' => $this->expr('unix_timestamp(a.mark_lifetime)'),
                           'created' => $this->expr('unix_timestamp(a.created)')
                       ))
                       ->addJoinColumns('u', array(
                            'user_level' => 'level'
                       ))
                       ->where(array('a.id' => $id))
                       ->limit(1);
                
                if($userId > 0){
                    $select->where(array('a.user_id' => $userId));
                }

                $result = $this->fetchRowSelect($select);

                if($result){
                   $ret = $result; 
                }
            }
        }
        
        return $ret;
    }
       
    /**
     * Add
     * @param array $params
     * @param array $arrays
     * @return bool
     */
    public function add($params = null, $arrays = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;
        if($params !== null){
            $params['timestamp'] = microtime(true);
            $params['created'] = $this->load('Date', 'admin')->getDateTime();
            $insert = $this->insert(self::TABLE_ADVERTS)
                           ->values($params);

            $ret = $this->execute($insert);
            $id = $this->insertId();

            if ($id > 0) {
                if (isset($params['name']) && isset($params['user_id'])) {
                    $this->load('SendEmail', 'admin')->addAdvert($params['name'], $params['user_id']);
                }

                if (isset($arrays['phone'])) {
                    $maskArray = isset($arrays['mask']) ? $arrays['mask'] : null;
                    $this->load('AdvertPhone', 'admin')->add($id, $arrays['phone'], $maskArray);
                }
                if (isset($arrays['gallery'])) {
                    $this->load('AdvertGallery', 'admin')->add($id, $arrays['gallery']);
                }

                if (isset($arrays['options']) && isset($params['category'])) {
                    $this->load('Options', 'admin')->addAdvertOptions($id , $arrays['options'], $params['category']);
                }
            } 
        }
        
        return (bool)$ret;
    }
    
    /**
     * Edit
     * @param int $id
     * @param array $params
     * @param array $arrays
     * @param int $userId
     * @return bool
     */
    public function edit($id = 0, $params = null, $arrays = null, $userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if((int)$id > 0 && $params !== null){
            $advert = $this->getOne($id);
            $update = $this->update(self::TABLE_ADVERTS)
                           ->set($params)
                           ->where(array('id' => $id));

            if($userId > 0){
                $update->where(array('user_id' => $userId));
            }

            $ret = $this->execute($update);

            if(isset($arrays['phone'])){
                $maskArray = isset($arrays['mask']) ? $arrays['mask'] : null;
                $this->load('AdvertPhone', 'admin')->add($id, $arrays['phone'], $maskArray);
            }                 
            if(isset($arrays['gallery'])){
                $this->load('AdvertGallery', 'admin')->add($id, $arrays['gallery']);
            }

            if (isset($arrays['options'])) {
                $this->load('Options', 'admin')->editAdvertOptions($id , $arrays['options'], $params['category']);
            }
        }
        return (bool)$ret;
    }
    
    /**
     * Remove
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function remove($id = 0, $userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;

        if((int)$id > 0){ 
            $advert = $this->getOne($id);

            $delete = $this->delete(self::TABLE_ADVERTS)
                           ->where(array('id' => $id));
            
            if((int)$userId > 0){
                $delete->where(array('user_id' => $userId));
            }

            $this->load('AdvertGallery', 'admin')->remove($id);
            $ret = $this->execute($delete);

            if($ret){
                if (isset($advert['name']) && isset($advert['user_id'])) {
                    $this->load('SendEmail', 'admin')->deleteAdvert($advert['name'], $advert['user_id']);
                }
            }
        }
        
        return (bool)$ret;
    }
    
    /**
     * Set status
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function setStatus($id = 0, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        
        if($id > 0){            
            $select = $this->select()
                           ->from(array('a' => self::TABLE_ADVERTS))
                           ->columns(array(
                               'name',
                               'status',
                               'user_id',
                           ))
                           ->join(
                                array('u' => self::TABLE_USER),
                                'u.id = a.user_id',
                                array(
                                    'user_level' => 'level'
                                )
                           )
                           ->where(array('a.id' => $id))
                           ->limit(1);
            
            $result = $this->fetchRowSelect($select);
            
            if(isset($result['status']) && isset($result['user_level'])){                
                $update = $this->update(self::TABLE_ADVERTS)
                               ->set(array(
                                   'status' => ($result['status'] == 'y' ? 'n' : 'y')
                               ))
                               ->where(array('id' => $id));
                
                $ret = $this->execute($update);

                if ($ret && isset($result['name']) && isset($result['user_id'])){
                    $this->load('SendEmail', 'admin')->activationAdvert($id, $result['name'], $result['user_id'], $result['status'], $params);
                }
            }
        }
       
        return (bool)$ret;
    }

    /**
     * lift advert
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function lift($id = 0, $userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if($id > 0){
            $pay = $this->load('User', 'profile')->payment($userId, self::LIFT_PRICE);
            
            if ($pay) {      
                $update = $this->update(self::TABLE_ADVERTS)
                    ->set(array(
                        'timestamp' => microtime(true)
                    ))
                    ->where(array('id' => $id));

                if($userId > 0){
                    $update->where(array('user_id' => $userId));
                }
                $ret = (bool)$this->execute($update);
                $this->load('Statistics', 'profile')->updateStatistic($id, 'up');
            }

        }

        return $ret;
    }

    /** TODO Old method ... new prolong_top();
     * Put advert in top
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function top($id = 0, $userId = 0)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($userId > 0 && $id > 0) {

            $pay = $this->load('User', 'profile')->payment($userId, self::PRICE_TOP);

            if ($pay) {
                $update = $this->update(self::TABLE_ADVERTS)
                    ->set(array(
                        'top' => 'y'
                    ))
                    ->where(array('id' => $id));

                $ret = (bool)$this->execute($update);

            }
        }

        return $ret;
    }

    /**
     * Prolong mark for advert
     * @param int $id
     * @param int $userId
     * @param int $days
     * @return bool
     */
    public function prolong_mark($id = 0, $userId = 0, $days = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;
        $availableDays = array(7, 15, 30);

        if($id > 0 && $days > 0 && in_array($days, $availableDays)) {

            $pay = $this->load('User', 'profile')->payment($userId, self::$markPrice[$days]);

            if ($pay) {
                $select = $this->select()
                    ->from(self::TABLE_ADVERTS)
                    ->columns(array(
                        'name',
                        'user_id',
                        'mark_lifetime' => $this->expr('unix_timestamp(mark_lifetime)')
                    ))
                    ->where(array('id' => $id))
                    ->limit(1);

                $advert = $this->fetchRowSelect($select);

                $newTime = $this->load('Date', 'admin')->setInterval(
                    isset($advert['mark_lifetime']) ? $advert['mark_lifetime'] : 0,
                    '+'.$days.' days'
                );

                $update = $this->update(self::TABLE_ADVERTS)
                    ->set(array(
                        'mark_lifetime' => date(self::MYSQL_DATETIME_FORMAT, $newTime)
                    ))
                    ->where(array('id' => $id));

                if ($userId > 0) {
                    $update->where(array('user_id' => $userId));

                    $ret = (bool)$this->execute($update);

                } else {
                    $ret = (bool)$this->execute($update);
                }

                if ($ret && isset($advert['name']) && isset($advert['user_id'])) {
                    $this->load('SendEmail', 'admin')->prolongTime($advert['name'], $advert['user_id'], $type = 1 ,$days);
                    $this->load('Statistics', 'profile')->updateStatistic($id, 'mark');
                }
            }

        }

        return $ret;
    }
    
    /**
     * Prolong advert
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function prolong_top($id = 0, $userId = 0, $days = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = false;
        $availableDays = array(3, 7, 15, 30);
        
        if($id > 0 && $days > 0 && in_array($days, $availableDays)) { 
            
            $pay = $this->load('User', 'profile')->payment($userId, self::$topPrice[$days]);
            
            if ($pay) {
                $select = $this->select()
                               ->from(self::TABLE_ADVERTS)
                               ->columns(array(
                                   'name',
                                   'user_id',
                                   'top_lifetime' => $this->expr('unix_timestamp(top_lifetime)')
                               ))
                               ->where(array('id' => $id))
                               ->limit(1);

                $advert = $this->fetchRowSelect($select);

                $newTime = $this->load('Date', 'admin')->setInterval(
                                isset($advert['top_lifetime']) ? $advert['top_lifetime'] : 0, 
                                '+'.$days.' days'
                           );

                $update = $this->update(self::TABLE_ADVERTS)
                               ->set(array(
                                   'top_lifetime' => date(self::MYSQL_DATETIME_FORMAT, $newTime)
                               ))
                               ->where(array('id' => $id));

                if ($userId > 0) {
                    $update->where(array('user_id' => $userId));

                    $ret = (bool)$this->execute($update);

                } else {
                    $ret = (bool)$this->execute($update);
                }

                if ($ret && isset($advert['name']) && isset($advert['user_id'])) {
                    $this->load('SendEmail', 'admin')->prolongTime($advert['name'], $advert['user_id'], $type = 2 ,$days);
                    $this->load('Statistics', 'profile')->updateStatistic($id, 'top');
                }
            }
        
    }
        
        return $ret;
    }
    
    
    /**
     * get paginator
     * @param array $params
     * @return null|array
     */
    public function getPaginator($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $count = 0;
        $page = 1;
        
        $select = $this->getSQL();
        
        if($select instanceof \Zend\Db\Sql\Select){
            $select->columns(array(
                       'count' => $this->expr('count(*)')
                   ));
            
            // set params
            $select = $this->setParams($select, $params);
            
            if(isset($params['page']) && (int)$params['page'] > 0){
                $page = $params['page'];
            }
            
            $count = (int)$this->fetchOneSelect($select);
        }

        return $this->paginator($page, $count, self::POST_PER_PAGE);
    }
    
}
