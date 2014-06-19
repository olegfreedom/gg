<?php
namespace Admin\Model;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Stdlib\StringWrapper\Iconv;

class SendEmail extends \Application\Base\Model
{
    /**
     * Send Email
     * @param string $email
     * @param string $title
     * @param string $content
     */
    private function sendEmail($email = null, $title = null, $content = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($email !== null && $title !== null && $content !== null){ 
            $iconv = new Iconv();
            
            $iconv->setEncoding(mb_detect_encoding($title, mb_detect_order(), true), 'UTF-8');
            $title = $iconv->convert($title);
            
            $iconv->setEncoding(mb_detect_encoding($content, mb_detect_order(), true), 'UTF-8');
            $content = $iconv->convert($content);
            
            $htmlTpl = '<html>';
            $htmlTpl .= '<head>';
            $htmlTpl .= '<meta charset="utf-8">';
            $htmlTpl .= '<title>'.$title.'</title>';
            $htmlTpl .= '</head>';
            $htmlTpl .= '<body>';
            $htmlTpl .= $content;
            $htmlTpl .= '<br><br><hr><a href="' . $this->basePath() . '/profile">'. $this->basePath() . '/profile</a>';
            $htmlTpl .= '</body>';
            $htmlTpl .= '</html>';

            $html = new MimePart($htmlTpl);
            $html->type = 'text/html';
            $html->charset = 'utf-8';

            $body = new MimeMessage();
            $body->setParts(array($html));

            $message = new Message();
            $message->setTo($email)
                    ->setFrom($this->getSiteEmail(), $this->getSiteName())
                    ->setReplyTo($this->getSiteEmail(), $this->getSiteName())
                    ->setSubject($title)
                    ->setBody($body);
            
            //TODO: в некоторых ситуациях мешает отправке писем
            //$message->setEncoding('UTF-8');
            
            $transport = new SendmailTransport();
            $transport->send($message);
        }  
    }

    /**
     * Get e-mail template
     * @param string $url
     * @return array
     */
    public function getNotification($url = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $ret = null;
        
        if($url !== null){
            $select = $this->select()
                ->from(self::TABLE_EMAIL_NOTIFICATIONS)
                ->columns(array(
                    'title',
                    'text'
                ))
                ->where(array(
                    'url' => $url,
                ));

            $result = $this->fetchRowSelect($select);
            
            if($result){
                $ret = $result;
            }
        }
        return $ret;
    }

    /**
     * Create e-mail for registration
     * @param string $username
     */
    public function registration($username = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($username !== null) {            
            $result = $this->getNotification(self::EMAIL_REGISTRATION);

            if (isset($result['text']) && isset($result['title'])) {
                $message = $result['text'];
                $message = str_replace('##login##', $username, $message);
                $this->sendEmail($username, $result['title'], $message);
            }            
        }
    }

    /**
     * Create e-mail for activation user
     * @param string $username
     * @param string $key
     * @param string $activationUrl
     */
    public function activationUser($username = null, $key = null, $activationUrl = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($username !== null && $key !== null && $activationUrl !== null) {
            $result = $this->getNotification(self::EMAIL_ACTIVATION_USER);

            if (isset($result['text']) && isset($result['title'])) {
                $message = $result['text'];
                $link = str_replace('_SET_KEY_', $key, $activationUrl);
                $message = str_replace('##link##', $link, $message);

                $this->sendEmail($username, $result['title'], $message);
            }
        }
    }

    /**
     * Create e-mail for change email
     * @param string $username
     * @param string $changeUrl
     */
    public function changeEmail($username = null, $changeUrl = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($username !== null && $changeUrl !== null) {
            $result = $this->getNotification(self::EMAIL_CHANGE_EMAIL);

            if ($result) {
                $message = $result['text'];
                $message = str_replace('##link##', $changeUrl, $message);

                $this->sendEmail($username, $result['title'], $message);
            }
        }
    }

    /**
     * Create e-mail for change user data
     * @param string $username
     * @param string $password
     */
    public function changeData($username = null, $password = null, $url = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($username !== null && $password !== null) {
            $result = $this->getNotification(self::EMAIL_CHANGE_DATA);

            if ($result) {
                $message = $result['text'];
                $message = str_replace('##loginUrl##', $url, $message);
                $message = str_replace('##login##', $username, $message);
                $message = str_replace('##password##', $password, $message);

                $this->sendEmail($username, $result['title'], $message);
            }            
        }
    }

    /**
     * Create e-mail for change user email
     * @param string $username
     */
    public function changeEmailSuccess($username = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($username !== null) {
            $result = $this->getNotification(self::EMAIL_CHANGE_EMAIL_SUCCESS);

            if ($result) {
                $message = $result['text'];
                $message = str_replace('##login##', $username, $message);

                $this->sendEmail($username, $result['title'], $message);
            }
        }
    }

    /**
     * Create e-mail for add car
     * @param string $title
     * @param int $user_id
     */
    public function addAdvert($title = null, $user_id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($title !== null && (int)$user_id > 0) {            
            if($this->load('Users', 'admin')->getLevel($user_id) == self::USERS_LEVEL_USER){
                $result = $this->getNotification(self::EMAIL_ADD_ADVERT);

                if ($result) {
                    $message = $result['text'];
                    $message = str_replace('##title##', $title, $message);
                    $username = $this->load('Users', 'admin')->getUsername($user_id);
                    $this->sendEmail($username, $result['title'], $message);
                }
            }
        }

    }

    /**
     * Create e-mail for remove advert
     * @param string $title
     * @param int $user_id
     */
    public function deleteAdvert($title = null, $user_id = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($title !== null && (int)$user_id > 0) {            
            if ($this->load('Users', 'admin')->getLevel($user_id) == self::USERS_LEVEL_USER) {
                $result = $this->getNotification(self::EMAIL_DELETE_ADVERT);

                if ($result) {
                    $message = $result['text'];
                    $message = str_replace('##title##', $title, $message);
                    $username = $this->load('Users', 'admin')->getUsername($user_id);
                    $this->sendEmail($username, $result['title'], $message);
                }
            }
        }

    }

    /**
     * Create e-mail for change status active/inactive
     * @param integer $id
     * @param array $params
     * @param string $title
     * @param int $user_id
     * @param string $status
     */
    public function activationAdvert($id = 0, $title = null, $user_id = 0, $status = null, $params = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($title !== null && (int)$user_id > 0 && $status !== null) {
            if ($this->load('Users', 'admin')->getLevel($user_id) == self::USERS_LEVEL_USER) {
                $result = $this->getNotification(self::EMAIL_ACTIVATION_ADVERT);
                
                if ($result) {
                    $statusText = ($status == 'n' ? 'активировано' : 'заблокировано');
                    $message = $result['text'];
                    $message = str_replace('##advUrl##', $params['advUrl'], $message);
                    $message = str_replace('##uslugi##', $params['servicesUrl'], $message);
                    $message = str_replace('##title##', $title, $message);
                    $message = str_replace('##message##', $statusText, $message);
                    $username = $this->load('Users', 'admin')->getUsername($user_id);
                    $this->sendEmail($username, $result['title'], $message);
                }
            }
        }

    }

    /**
     * Create e-mail for prolong time
     * @param string $title
     * @param int $user_id
     * @param int $type (Тип продления: 1 - mark, 2 - top)
     * @param int $days
     */
    public function prolongTime($title = null, $user_id = 0, $type = 0, $days = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($title !== null && (int)$user_id > 0 && $type > 0 && $days > 0) {            
            if ($this->load('Users', 'admin')->getLevel($user_id) == self::USERS_LEVEL_USER) {

                if ($type == 1) {
                    $type = 'выделено';
                    $result = $this->getNotification(self::EMAIL_EXTEND_TIME);
                } else {
                    $type = 'помещено в ТОП';
                    $result = $this->getNotification(self::EMAIL_EXTEND_TIME_TOP);
                }
                
                $text = explode(' ',trim($this->load('Date', 'admin')->daysText($days)));
                $days_text = $text[0];
                

                if ($result) {
                    $message = $result['text'];
                    $message = str_replace('##title##', $title, $message);
                    $message = str_replace('##days##', $days, $message);
                    $message = str_replace('##type##', $type, $message);
                    $message = str_replace('##days_text##', $days_text, $message);
                    $username = $this->load('Users', 'admin')->getUsername($user_id);
                    $this->sendEmail($username, $result['title'], $message);
                }
            }
        }

    }
    

    /**
     * Create daily e-mail for expiry time in all categories
     * @param integer $days
     */
    /* TODO: Example */
    public function expiryTimeTop($days = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($days > 0){     
            $ret = array();

            $select = $this->select()
                ->from(array('a' => self::TABLE_ADVERTS))
                ->columns(array(
                    'advert_name' => 'name',
                    'user_id',
                ))
                ->join(
                    array('u' => self::TABLE_USER),
                    'u.id = user_id',
                    array(
                        'user_email' => 'username',
                        'user_name' => 'name'
                    )
                )
                ->where(array(
                    'u.level' => self::USERS_LEVEL_USER,
                    $this->where()
                        ->greaterThanOrEqualTo('a.top_lifetime', $this->load('Date','admin')->getDateTime('+ '.($days - 1).' days'))
                        ->and
                        ->lessThan('a.top_lifetime', $this->load('Date','admin')->getDateTime('+ '.($days + 1).' days'))
                ));
            
                $result = $this->fetchSelect($select);

                if ($result) {
                    foreach ($result as $item) {
                        if (isset($item['user_id'])) {
                            if (!isset($ret[$item['user_id']])) {
                                $ret[$item['user_id']] = array();
                            }

                            $ret[$item['user_id']][] = $item;
                        }
                    }
                
                        
                    if (count($ret) > 0) {
                        
                        $notification = $this->getNotification(self::EMAIL_EXPIRY_TIME);
                        $message_tpl = $notification['text'];
                        $email_title = $notification['title'];
                        $top_text = 'Поместить объявление в топ';
                        
                        foreach ($ret as $item) {
                            foreach($item as $value) {
                                $user_email = $value['user_email'];
                                $user_name = $value['user_name'];
                                $advert_title = $value['advert_name'];

                                if(isset($user_email)){
                                    $message_tpl = str_replace('##type##', $top_text, $message_tpl);
                                    $message_tpl = str_replace('##title##', $advert_title, $message_tpl);
                                    $message_tpl = str_replace('##num##', $days, $message_tpl);
                                    $message_tpl = str_replace('##days_text##', $this->load('Date', 'admin')->daysText($days, 'sendmail'), $message_tpl);
                                    $this->sendEmail($user_email, $email_title, $message_tpl);
                                }
                            }   
                        }

                    }
                }

    }
}

    /**
     * Create daily e-mail for expiry time in all categories
     * @param integer $days
     */
    /* TODO: Example */
    public function expiryTimeMark($days = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if($days > 0){     
            $ret = array();
            
            $select = $this->select()
                ->from(array('a' => self::TABLE_ADVERTS))
                ->columns(array(
                    'advert_name' => 'name',
                    'user_id',
                ))
                ->join(
                    array('u' => self::TABLE_USER),
                    'u.id = user_id',
                    array(
                        'user_email' => 'username',
                        'user_name' => 'name'
                    )
                )
                ->where(array(
                    'u.level' => self::USERS_LEVEL_USER,
                    $this->where()
                        ->greaterThanOrEqualTo('a.mark_lifetime', $this->load('Date','admin')->getDateTime('+ '.($days - 1).' days'))
                        ->and
                        ->lessThan('a.mark_lifetime', $this->load('Date','admin')->getDateTime('+ '.($days + 1).' days'))
                ));

                $result = $this->fetchSelect($select);    

                if ($result) {
                    foreach ($result as $item) {
                        if (isset($item['user_id'])) {
                            if (!isset($ret[$item['user_id']])) {
                                $ret[$item['user_id']] = array();
                            }

                            $ret[$item['user_id']][] = $item;
                        }
                    }
                    
                    
                    if (count($ret) > 0) {

                        $notification = $this->getNotification(self::EMAIL_EXPIRY_TIME);
                        $message_tpl = $notification['text'];
                        $email_title = $notification['title'];
                        $mark_text = 'Выделить объявление';

                        foreach ($ret as $item) {
                            foreach($item as $value) {
                                $user_email = $value['user_email'];
                                $user_name = $value['user_name'];
                                $advert_title = $value['advert_name'];

                                if(isset($user_email)){
                                    $message_tpl = str_replace('##type##', $mark_text, $message_tpl);
                                    $message_tpl = str_replace('##title##', $advert_title, $message_tpl);
                                    $message_tpl = str_replace('##num##', $days, $message_tpl);
                                    $message_tpl = str_replace('##days_text##', $this->load('Date', 'admin')->daysText($days, 'sendmail'), $message_tpl);
                                    $this->sendEmail($user_email, $email_title, $message_tpl);
                                }
                            }
                        }

                    }
                }
                

    }
}

    /**
     * Create e-mail for refill
     * @param string $username
     * @param int $refill_amount
     */
    public function refill($username = null, $refill_amount = 0){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($username !== null && (int)$refill_amount > 0) {            
            $result = $this->getNotification(self::EMAIL_REFILL);

            if ($result) {
                $message = $result['text'];
                $message = str_replace('##num##', $refill_amount, $message);
                $this->sendEmail($username, $result['title'], $message);
            }
        }
    }

    /**
     * Create e-mail for forgot password
     * @param string $email
     * @param string $newPassword
     */
    public function fogotPassword($email = null, $newPassword = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        if ($email !== null && $newPassword !== null) {            
            $result = $this->getNotification(self::EMAIL_FORGOT);

            if ($result) {
                $message = $result['text'];
                $message = str_replace('##new_password##', $newPassword, $message);
                $this->sendEmail($email, $result['title'], $message);
            }
        }
    }
    
    /**
     * Create e-mail for password recovery confirmation
     * @param array $email
     * @param string $key
     * @param string $activationUrl
     */
    public function recoveryConfirmation($params, $key = null, $activationUrl = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($params['username'] !== null && $key !== null) {
            $result = $this->getNotification(self::EMAIL_CONFIRM_RECOVERY);

            if ($result) {
                $message = $result['text'];
                $link = str_replace('__KEY__', $key, $params['link']);
                $message = str_replace('##login##', $email, $message);
                $message = str_replace('##recovery_url##', $link, $message);
                $this->sendEmail($params['username'], $result['title'], $message);
            }
        }
    }

    /**
     * Create e-mail for guest account
     * @param string $email
     * @param string $password
     * @param string $key
     * @param string $activationUrl
     */
    public function guestActivate($email = null, $password = null, $key = null, $activationUrl = null){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($email !== null && $password !== null) {
            $result = $this->getNotification(self::EMAIL_FOR_GUEST);

            if ($result) {
                $message = $result['text'];
                $link = str_replace('_SET_KEY_', $key, $activationUrl);
                $message = str_replace('##login##', $email, $message);
                $message = str_replace('##password##', $password, $message);
                $message = str_replace('##link##', $link, $message);
                $this->sendEmail($email, $result['title'], $message);
            }
        }
    }
    
    public function debugPayment($response,$title){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        require_once BASE_PATH.'/data/paysystem/paysys/kkb.utils.php';
        $path1 = BASE_PATH.'/data/paysystem/paysys/config.txt';
        //$result = process_response(stripslashes($response),$path1);
        
        ob_start();

            $this->debug($response, false);

        $body = ob_get_clean();        
        
        $this->SendEmail('bohdan.harasan@chisw.us', $title, $body);
    }
}