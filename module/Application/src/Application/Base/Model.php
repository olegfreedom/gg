<?php
namespace Application\Base;

abstract class Model extends \Base\Mvc\Model
{

    // Table constant
    const TABLE_ADVERTS = 'adverts';
    const TABLE_ADVERTS_CATEGORIES = 'advert_categories';
    const TABLE_ADVERTS_CURRENCY = 'advert_currency';
    const TABLE_ADVERTS_LOCATION = 'advert_location';
    const TABLE_ADVERTS_TYPE = 'advert_type';
    const TABLE_ADVERTS_PHONE = 'advert_phone';
    const TABLE_ADVERTS_GALLERY = 'advert_gallery';
    const TABLE_ADVERTS_OPTIONS = 'advert_options';
    const TABLE_ADVERT_TO_MESSAGE = 'advert_to_message';
    const TABLE_ADVERTS_LOCATION_REGIONS = 'advert_location_regions';
    const TABLE_ADVERT_STATISTICS = 'advert_statistics';

    const TABLE_SQL_UPDATES = '_sql_updates';
    const TABLE_USER = 'users';
    const TABLE_USER_CONTACTS = 'user_contacts';
    const TABLE_CONTACT_TYPES = 'contact_types';
    const TABLE_USER_PHONE = 'user_phone';
    const TABLE_PAGES = 'pages';
    const TABLE_TESTIMONIALS = 'testimonials';
    const TABLE_PHONE_MASK = 'phone_mask';
    const TABLE_EMAIL_NOTIFICATIONS = 'email_notifications';
    const TABLE_HELPS = 'helps';


    const TABLE_SEARCH_LOG = 'search_log';

    const TABLE_FAVORITES = 'favorite';

    const TABLE_CATEGORY_TO_OPTION = 'category_to_option';
    const TABLE_OPTIONS = 'options';
    const TABLE_SUBSCRIBE_EMAILS = 'subscribe_emails';
    const TABLE_FAQ = 'faq';
    const TABLE_BANNERS = 'banners';
    const TABLE_MESSAGES = 'messages';
    const TABLE_CONTACT_US = 'contact_us';
    const TABLE_TESTIMONIALS_TO_ADVERT = 'testimonials_to_advert';
    const TABLE_REQUIRE_PARAMS = 'require_params';

    const TABLE_ORDERS = 'orders';




    // Users level
    const USERS_LEVEL_ADMIN = 'admin';
    const USERS_LEVEL_USER = 'user';

    // Email Notifications
    const EMAIL_REGISTRATION = 'registration';
    const EMAIL_ACTIVATION_USER = 'activation_user';
    const EMAIL_CHANGE_DATA = 'change_data';
    const EMAIL_CHANGE_EMAIL = 'change_email';
    const EMAIL_CHANGE_EMAIL_SUCCESS = 'change_email_success';
    const EMAIL_ADD_ADVERT = 'add_advert';
    const EMAIL_DELETE_ADVERT = 'delete_advert';
    const EMAIL_ACTIVATION_ADVERT = 'activation_advert';
    const EMAIL_EXTEND_TIME = 'extend_time';
    const EMAIL_EXTEND_TIME_TOP = 'extend_time_top';
    const EMAIL_EXPIRY_TIME = 'expiry_time';
    const EMAIL_REFILL = 'refill';
    const EMAIL_FORGOT = 'forgot';
    const EMAIL_FOR_GUEST = 'email_for_guest';
    const EMAIL_CONFIRM_RECOVERY = 'confirm_recovery';

    const STARS_PROLONG = 5;
    const PRICE_TOP = 10;
    
    static $topPrice = array(
        3 => 200,
        7 => 350,
        15 => 500,
        30 => 800
    );

    static $markPrice = array(
        7 => 100,
        15 => 200,
        30 => 350
    );
    
    const LIFT_PRICE = 50;
    

    /**
     * Get limiter
     * @param array $params search params
     * @return null|array
     */
    public function getLimiter($params = array()){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        return $this->limiter($this->getLimit($params));
    }

    /**
     * Get limit
     * @param array $params search params
     * @return int|string
     */
    protected function getLimit($params = array()){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $limit = isset($params['limit']) ? $params['limit'] : 10;
        return $limit;
    }
}
