<?php
namespace Profile\Controller;

class GuestController extends \Profile\Base\AbstractAdvertsController
{


    public function addAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        // add index css and js
        $this->addHeadLink('/css/medialoader/profile/index.css');
        $this->addHeadScript('/js/medialoader/profile/index.js');
        $this->addHeadScript('/js/medialoader/profile/index/add.js');

        if ($this->p_int('add-form') === 1) {

            $userParams = array(
                'username' => $this->p_string('email'),
                'password' => $this->load('User', 'profile')->randString(6),
                'activation_url' => $this->easyUrl(array('controller' => 'registration','action' => 'activation', 'key' => '_SET_KEY_'))
            );
            $userId = $this->load('Registration', 'profile')->authUser($userParams, true);
            $params = array(
                'type' => $this->p_int('type_id'),
                'location' => $this->p_int('location'),
                'name' => $this->p_string('name'),
                'contact_name' => $this->p_string('contact_name'),
                'description' => $this->p_string('description', '', false),
                'price' => $this->p_int('price'),
                'currency' => $this->p_int('currency'),
                'category' => $this->p_int('category_id'),
                'status' => 'n',
                'user_id' => $userId
            );
            
            $arrays = array(
                'gallery' => $this->getFiles('gallery'),
                'phone' => $this->p_array('phone'),
                'mask' => $this->p_array('mask')
            );

            $this->load('Adverts', 'admin')->add($params, $arrays);

            return $this->redirect()->toUrl(
                $this->easyUrl(array('action' => 'success'))
            );
        } else {
            $catalog = $this->session()->catalog;
            $ret = array(
                'getType' => $this->load('AdvertType', 'admin')->get($catalog),
                'getLocation' => $this->load('AdvertLocation', 'admin')->getRegions(),
                'getCurrency' => $this->load('AdvertCurrency', 'admin')->get(),
                'getCategory' => $this->load('AdvertCategory', 'admin')->get($catalog),
                'mainCategory' => $this->load('AdvertCategory', 'admin')->getMainCategories(),
                'catalogName' => $catalog,
                'phoneMask' => $this->load('Phone', 'admin')->getPhoneMask(),
                'phonePlaceholder' => $this->load('Phone', 'admin')->getPlaceholder(),
                'phoneMaskArray' => $this->load('Phone', 'admin')->getMaskArray(),
                'userName' => $this->load('Users', 'admin')->getName($this->getUserId()),
                'userPhone' => $this->load('UsersPhone', 'admin')->get($this->getUserId()),
                'isGuest' => true,
                'helper' => $this->load('Helps', 'admin')->getTextByUrl(array(
                    'type',
                    'name',
                    'contact_name',
                    'category',
                    'phone',
                    'location',
                    'description',
                    'price',
                    'email'
                ))
            );

            return $this->view($ret, 'profile/index/add.phtml');
        }
    }

    public function successAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $ret = array();

        return $this->view($ret);
    }

    public function errorAction(){
        $this->log(__CLASS__.'\\'.__FUNCTION__);

        $ret = array();

        return $this->view($ret);
    }

    public function validatorAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $category_id = $this->p_int('category_id');
        $requireParams = $this->load('RequireParams', 'admin')->getOne($category_id);

        $params = array(
            'name' => $this->p_string('name'),
            'contact_name' => $this->p_string('contact_name'),
            'phoneArray' => $this->p_array('phoneArray'),
            'description' => $this->p_string('description', '', false),
            'price' => $this->p_string('price'),
            'email' => $this->p_string('email'),
            'location' => $this->p_int('location'),
            'region' => $this->p_int('region'),
        );
        
        $error = array();

        $validItem = $this->load('Validator')->validStringLength($params['email'], 5, 100);
        if($validItem == false){
            $error['email'] = $validItem;
        }else{
            $validItem = $this->load('Validator')->validEmail($params['email']);
            if($validItem == false){
                $error['email'] = $validItem;
            }else{
                $validItem = $this->load('User', 'profile')->checkLogin($params['email']);
                if($validItem == true){
                    $error['email'] = false;
                    $error['email_taken'] = true;
                }
            }
        }

        $validItem = $this->load('Validator')->validStringLength($params['name'], 2, 50);
        if ($validItem == false) {
            $error['name'] = $validItem;
        }

        $validItem = $this->load('Validator')->validStringLength($params['contact_name'], 2, 50);
        if ($validItem == false) {
            $error['contact_name'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validGreaterThan($params['region']);
        if ($validItem == false) {
            $error['region'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validGreaterThan($params['location']);
        if ($validItem == false) {
            $error['location'] = $validItem;
        }

        if ($requireParams && in_array('phone', $requireParams['fields'])) {
            foreach ($params['phoneArray'] as $key => $val) {
                $validItem = $this->load('Phone', 'admin')->checkPhone($val);
                if ($validItem == false) {
                    $error['phoneArray'][$key] = $validItem;
                }
            }
        }

        if ($requireParams && in_array('description', $requireParams['fields'])) {
            $validItem = $this->load('Validator')->validStringLength($params['description'], 20, 20000);
            if ($validItem == false) {
                $error['description'] = $validItem;
            }
        }

        
        if ($requireParams && in_array('price', $requireParams['fields'])) {
            $validItem = $this->load('Validator')->validRegex($params['price'], '/^[0-9\,\.]+$/');
            if ($validItem == true) {                
                $validItem = $this->load('Validator')->validBetween((float)$params['price'], 1, 9999999); 
                if ($validItem == false) {
                    $error['price'] = $validItem;
                }
            } else {
                $error['price'] = $validItem;
            }
        }
        
        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        
        return $this->json($ret);
    }
}