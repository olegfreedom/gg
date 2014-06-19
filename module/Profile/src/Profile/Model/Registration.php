<?php
namespace Profile\Model;

class Registration extends \Application\Base\Model
{
    /**
     * Authorize User
     * @param array $params
     * @param bool $guest
     * @return null|array
     */
    public function authUser($params = null, $guest = false){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if(isset($params['username']) && isset($params['password']) && isset($params['activation_url'])){
            if($this->load('User', 'profile')->checkLogin($params['username']) == false){  
                $salt = $this->load('User', 'profile')->generateSalt();
                
                $insert = $this->insert(self::TABLE_USER)
                               ->values(array(
                                   'username' => $params['username'],
                                   'password' => $this->expr('md5(?)', $params['password'].$salt),
                                   'salt' => $salt,
                                   'level' => self::USERS_LEVEL_USER,
                                   'timestamp' => $this->load('Date', 'admin')->getDateTime(),
                                   'key' => $this->expr('md5(?)', $params['username'].$salt)
                               ));

                $result = $this->execute($insert);

                $ret = $this->insertId($result);

                if ($result && $guest === true){
                    $key = md5($params['username'].$salt);
                    $this->load('SendEmail', 'admin')->guestActivate($params['username'], $params['password'], $key, $params['activation_url']);
                }elseif($result){
                    $key = md5($params['username'].$salt);
                    $this->load('SendEmail', 'admin')->activationUser($params['username'], $key, $params['activation_url']);
                }
            }
        }
        
        return $ret;
    }

    /**
     * Activation User
     * @param string $key
     * @return null|array
     */
    public function activationUser($key = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = null;

        if ($key !== null) {
            $select = $this->select(self::TABLE_USER)
                           ->columns(array(
                               'id',
                               'username',
                               'status'
                           ))
                           ->where(array(
                               'key' => $key,
                           ))
                           ->limit(1);

            $user = $this->fetchRowSelect($select);

            if (isset($user['id']) && (int)$user['id'] > 0 && isset($user['username']) && $user['status'] == 'n') {
                $update = $this->update(self::TABLE_USER)
                               ->set(array(
                                   'status' => 'y',
                               ))
                               ->where(array(
                                   'id' => $user['id']
                               ));

                $result = $this->execute($update);

                if ($result){
                    $this->load('SendEmail', 'admin')->registration($user['username']);
                }
            }
            if ($user) {
                $ret = $user;
            }
        }
        
        return $ret;
    }
}