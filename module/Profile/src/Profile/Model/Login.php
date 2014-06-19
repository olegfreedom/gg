<?php
namespace Profile\Model;

class Login extends \Application\Base\Model
{
    /**
     * Authorize User
     * @param array $params
     * @return null|array
     */
    public function authUser($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if(isset($params['username']) && isset($params['password'])){
            $select = $this->select()
                           ->from(self::TABLE_USER)
                           ->columns(array(
                               'id',
                               'username',
                               'key'
                           ))
                           ->where(array(
                               'username' => $params['username'],
                               'password' => $this->expr('md5(concat(?,salt))', $params['password']),
                               'level' => self::USERS_LEVEL_USER,
                               'status' => 'y'
                           ))
                           ->limit(1);
            
            $result = $this->fetchRowSelect($select);

            if($result){
                if($params['remember'] == 'y'){
                    $this->setCookie('loginID', $result['key'], (time() + 60*60*24*30), '/');
                }
                $ret = $result;
            }
        }
        return $ret;
    }

    /**
     * Authorize User via Cookie
     * @param null $params
     * @return mixed|null
     */
    public function authUserViaCookie($params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $ret = null;

        if(isset($params)){
            $select = $this->select()
                           ->from(self::TABLE_USER)
                           ->columns(array(
                                'key'
                           ))
                           ->where(array(
                                'key' => $params
                           ))
                           ->limit(1);
            $result = $this->fetchOneSelect($select);

            if($result ){
                $ret = $result;
            }
        }
        return $ret;
    }
}