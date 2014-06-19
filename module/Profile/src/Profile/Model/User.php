<?php
namespace Profile\Model;

class User extends \Application\Base\Model
{
    /**
     * Get User
     * @param int $id
     * @return array|null
     */
    public function getOne($id = 0)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = null;

        if ($id > 0) {
            $select = $this->select()
                ->from(array('u' => self::TABLE_USER))
                ->columns(array(
                    'id',
                    'username',
                    'name' => $this->expr('case name when "" then username else name end')
                ))
                ->where(array(
                    'id' => $id
                ))
                ->limit(1);

            $result = $this->fetchRowSelect($select);

            if ($result) {
                $select = $this->select()
                    ->from(array('uc' => self::TABLE_USER_CONTACTS))
                    ->columns(array('value'))
                    ->join(
                        array('ct' => self::TABLE_CONTACT_TYPES),
                        'uc.contact_type_id = uc.id',
                        array()
                    )
                    ->addJoinColumns('ct', array(
                        'type_name' => 'name',
                        'type_title' => 'title'
                    ))
                    ->where(array(
                        'user_id' => $id
                    ))
                ;

                $contactResult = $this->fetchSelect($select);

                if ( !empty($contactResult) )
                {
                    foreach ( $contactResult as $row )
                    {
                        if ( empty($result [$row ['type_name']]) )
                        {
                            $result [$row ['type_name']] = $row ['value'];
                        }
                        else
                        {
                            if ( is_array($result [$row ['type_name']]) )
                            {
                                $result [$row ['type_name']] [] = $row ['value'];
                            }
                            else
                            {
                                $result [$row ['type_name']] = array($result [$row ['type_name']], $row ['value']);
                            }
                        }
                    }
                }

                $ret = $result;
            }
        }

        return $ret;
    }

    /**
     * Edit User
     * @param array $params
     * @param int $id
     * @param null|\Base\Mvc\Controller $controller
     * @return bool|string
     */
    public function edit($params = null, $id = 0, $controller = null)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if (isset($params['username']) && isset($params['password']) && isset($params['old_password']) && $id > 0) {

            if ($this->checkLogin($params['username'], $id) == false) {

                $select = $this->select()
                    ->from(self::TABLE_USER)
                    ->columns(array(
                        'id',
                        'username',
                        'key'
                    ))
                    ->where(array(
                        'id' => $id,
                        'password' => $this->expr('md5(concat(?,salt))', $params['old_password']),
                        'level' => self::USERS_LEVEL_USER
                    ))
                    ->limit(1);

                $user = $this->fetchRowSelect($select);

                if (isset($user['id']) && $user['id'] == $id) {
                    $set = array();

                    if (isset($user['username']) && isset($user['key']) && $user['username'] != $params['username']) {
                        if($controller !== null && $controller instanceof \Base\Mvc\Controller){
                            $changeUrl = $controller->easyUrl(
                                            array('module' => 'profile', 'controller' => 'settings', 'action' => 'change-email', 'key' => $user['key']), 
                                            array('email' => $params['username'])
                                         );
                            $this->load('SendEmail', 'admin')->changeEmail($user['username'], $changeUrl);
                            $ret = 'change-email';
                        }
                    }
                    if (!empty($params['password'])) {
                        $salt = $this->generateSalt();
                        $set['password'] = $this->expr('md5(?)', $params['password'] . $salt);
                        $set['salt'] = $salt;
                    }
                    if (count($set) > 0) {
                        $update = $this->update(self::TABLE_USER)
                            ->set($set)
                            ->where(array('id' => $id));
                        $result = $this->execute($update);
                        if ($result) {
                            $url = $controller->easyUrl(array('module' => 'profile','controller' => 'login', 'action'=>'login-by-key')).'key/'.$user['key'];
                            $this->load('SendEmail', 'admin')->changeData($params['username'], $params['password'], $url);
                            $ret = true;
                        }
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Edit email
     * @param null $newEmail
     * @param null $key
     * @return bool
     */
    public function changeEmail($newEmail = null, $key = null)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($newEmail != null && $key != null) {
            $select = $this->select()
                ->from(self::TABLE_USER)
                ->columns(array(
                    'id',
                    'username'
                ))
                ->where(array(
                    'key' => $key
                ))
                ->limit(1);
            $user = $this->fetchRowSelect($select);

            if ($user) {
                $id = $user['id'];
                $set = array();
                $set['username'] = $newEmail;
                $update = $this->update(self::TABLE_USER)
                    ->set($set)
                    ->where(array('id' => $id));
                $result = $this->execute($update);

                if ($result) {
                    $this->load('SendEmail', 'admin')->changeEmailSuccess($newEmail);
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * Get id by login(username)
     * @param string $login
     * @return bool|mixed
     */
    public function getIdByUserName($login = ''){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if (!empty($login)){
            $select = $this->select()
                ->from(self::TABLE_USER)
                ->columns(array('id'))
                ->where(array('username' => $login))
                ->limit(1);
            $result = (int)$this->fetchOneSelect($select);

            if ($result > 0){
                $ret = $result;
            }
        }
        return $ret;
    }

    /**
     * Get user's list
     * @param string $username
     * @param int $userId
     * @return bool|mixed
     */
    public function getUsersList($username = '', $userId = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if (!empty($username)){
            $select = $this->select()
                ->from(array('u' => self::TABLE_USER))
                ->columns(array('username'))
                ->join(array('m_f' => self::TABLE_MESSAGES),
                    'm_f.from_user_id = u.id',
                     array())
                ->where(array($this->where()
                    ->like('u.username', '%' . $username . '%')
                    ->notEqualTo('u.id', $userId)
                    ->andPredicate(
                        $this->where()
                        ->equalTo('m_f.from_user_id', $userId)
                        ->or
                        ->equalTo('m_f.to_user_id', $userId)

                        )
                    )
                )
                ->group('username');

            $result = $this->fetchSelect($select);

            if ($result > 0){
                foreach ($result as $item){
                    $ret[] = $item['username'];
                }
            }
        }
        return $ret;
    }
    /**
     * Check is there this username
     * @param string $login
     * @param int $id
     * @return bool
     */
    public function checkLogin($login = null, $id = 0)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($login !== null) {
            $select = $this->select()
                ->from(self::TABLE_USER)
                ->columns(array('id'))
                ->where(array(
                    'username' => $login,
                    'level' => self::USERS_LEVEL_USER
                ))
                ->limit(1);
            if ($id > 0) {
                $select->where(array(
                    $this->where()
                        ->notEqualTo('id', $id)
                ));
            }

            $result = (int)$this->fetchOneSelect($select);

            if ($result > 0) {
                $ret = true;
            }
        }

        return $ret;
    }

    /**
     * Generate salt string
     * @return string
     */
    public function generateSalt()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        return $this->randString(rand(5, 10));
    }

    /**
     *
     * @param int $id
     * @param int $amount
     * @return bool
     */
    public function payment($id = 0, $amount = 0)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $ret = false;

        if ($id > 0 && $amount > 0) {
            $select = $this->select()
                ->from(self::TABLE_USER)
                ->columns(array('balance'))
                ->where(array('id' => $id))
                ->limit(1);

            $result = $this->fetchOneSelect($select);

            if ((int)$result > 0 && (int)$result >= $amount) {
                $update = $this->update(self::TABLE_USER)
                    ->set(array(
                        'balance' => ((int)$result - $amount)
                    ))
                    ->where(array(
                        'id' => $id,
                    ));

                $ret = (bool)$this->execute($update);
            }
        }

        return $ret;
    }
}