<?php
namespace Profile\Model;

class Statistics extends \Application\Base\Model
{
    /**
     * Get statistic by user
     * @param int $user_id
     * @return array|bool
     */
    public function get($user_id = 0){ // TODO: не используется, может понадобится в дальнейшем
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($user_id > 0){
            $select = $this->select()
                        ->from(array('a' => self::TABLE_ADVERTS))
                        ->columns(array(
                                    'id',
                                    'name',
                                    'price',
                                    'user_id',
                                    'img_id' => $this->subQuery(
                                            $this->select()
                                                ->from(array('ra_sub' => self::TABLE_ADVERTS_GALLERY))
                                                ->columns(array('id'))
                                                ->where(array(
                                                    'advert_id' => $this->expr('a.id')
                                                ))
                                                ->order('ra_sub.id asc')
                                                ->limit(1)
                                                ),
                                    ))
                        ->where(array('a.user_id' => $user_id));
            $result = $this->fetchSelect($select);
            if ($result){
                foreach ($result as &$item){
                    $select = $this->select()
                            ->from(self::TABLE_ADVERT_STATISTICS)
                            ->where(array('advert_id' => $item['id']));
                        $statistic = $this->fetchSelect($select);
                    $item['statistic'] = $statistic;
                }
            }
            if ($result){
                $ret = $result;
            }
        }
        return $ret;
    }

    /**
     * Get statistic by advert
     * @param int $advert_id
     * @return array|bool
     */
    public function getStatisticByAdvert($advert_id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($advert_id){
            $select = $this->select()
                ->from(self::TABLE_ADVERT_STATISTICS)
                ->where(array(
                    'advert_id' => $advert_id,
                    $this->where()
                        ->greaterThan('date', date(self::MYSQL_DATE_FORMAT, strtotime('-3 day')))
                ));
            $statistic = $this->fetchSelect($select);

            if ($statistic){
                foreach($statistic as &$item){
                    $newTime = strtotime($item['date']);
                    $item['date'] = array(
                        'y' => (int)date('Y', $newTime),
                        'm' => (int)date('m', $newTime),
                        'd' => (int)date('d', $newTime),
                        'h' => (int)date('H', $newTime),
                        'i' => (int)date('i', $newTime),
                        's' => (int)date('s', $newTime),
                    );
                }
                $ret = $statistic;
            }
        }
        return $ret;
    }



    /**
     * @param int $advert_id
     * @param str $action
     * @return bool
     */
    public function updateStatistic($advert_id = 0, $action = null)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($advert_id > 0 && !empty($action)) {
            $params = array(
                'advert_id' => $advert_id,
                'action' => $action,
                'count' => $this->expr('count+1'),
            );
            if ($action == 'view') {
                $select = $this->select()
                            ->from(self::TABLE_ADVERT_STATISTICS)
                            ->columns(array('id'))
                            ->where(array(
                                'advert_id' => $advert_id,
                                'action' => 'view',
                                $this->where()
                                    ->greaterThan('date', date(self::MYSQL_DATETIME_FORMAT, strtotime('-1 hour')))
                            ))
                            ->limit(1)
                            ->order('id desc');
                $lastId = $this->fetchOneSelect($select);
                }
                if ((!isset($lastId) || $lastId == false) || $action !== 'view'){
                    if ($action !== 'view'){
                        $params['count'] = $this->getLastCount($advert_id);
                    }
                    $params['date'] = $this->load('Date', 'admin')->getDateTime();
                    $insert = $this->insert(self::TABLE_ADVERT_STATISTICS)
                        ->values($params);
                    $result = $this->execute($insert);
                } else {
                    $update = $this->update(self::TABLE_ADVERT_STATISTICS)
                                ->set($params)
                                ->where(array('id' => $lastId));
                    $result = $this->execute($update);
                }
            if ($result) {
                $ret = (bool)$result;
            }
        }
        return $ret;
    }

    /**
     * Get Last Count
     * @param int $advert_id
     * @return bool|int
     */
    public function getLastCount($advert_id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($advert_id > 0){
            $select = $this->select()
                ->from(self::TABLE_ADVERT_STATISTICS)
                ->columns(array('count'))
                ->where(array('advert_id' => $advert_id, 'action' => 'view'))
                ->order('date desc')
                ->limit(1);
            $result = $this->fetchOneSelect($select);
            if ($result){
                $ret = $result;
            }
        }
        return $ret;
    }
}