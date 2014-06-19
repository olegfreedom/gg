<?php
namespace Profile\Controller;

class IndexController extends \Profile\Base\AbstractAdvertsController
{
    public function __construct()
    {
        parent::__construct();

        $this->pushTitle('Управление объявлениями');
        
    }

    public function indexAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $userId = $this->getUserId();
        $page = $this->p_int('page', 1);

        $params = array(
                    'user_id' => $userId,
                    'page' => $page,
                 );

        $ret = array(
            'advertsList' => $this->load('Adverts', 'admin')->getList($params),
        );

        return $this->view($ret);
    }

    public function addAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($this->p_int('add-form') === 1) {
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
                'user_id' => $this->getUserId()
            );

            $arrays = array(
                'gallery' => $this->getFiles('gallery'),
                'phone' => $this->p_array('phone'),
                'mask' => $this->p_array('mask'),
                'options' => $this->p_array('option')
            );

            foreach($arrays['options'] as &$option){
                if(is_array($option)){
                    if(isset($option['checkbox_one'])){
                        $checkboxOne = $option['checkbox_one'];
                        $option = (isset($checkboxOne['hidden']) && !isset($checkboxOne['checkbox'])) ? $checkboxOne['hidden'] : $checkboxOne['checkbox'];
                    }else if(isset($option['checkbox_multi'])){
                        $checkboxMulti = $option['checkbox_multi'];
                        $option = json_encode($checkboxMulti);
                    }
                }
            }

            $this->load('Adverts', 'admin')->add($params, $arrays);

            return $this->redirect()->toUrl(
                $this->easyUrl(array())
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
                'helper' => $this->load('Helps', 'admin')->getTextByUrl(array(
                    'type',
                    'name',
                    'contact_name',
                    'category',
                    'phone',
                    'location',
                    'description',
                    'price',
                ))
            );

            return $this->view($ret);
        }
    }

    public function editAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        if ($this->p_int('edit-form') === 1) {
            $params = array(
                'type' => $this->p_int('type_id'),
                'location' => $this->p_int('location'),
                'name' => $this->p_string('name'),
                'contact_name' => $this->p_string('contact_name'),
                'description' => $this->p_string('description', '', false),
                'price' => $this->p_int('price'),
                'currency' => $this->p_int('currency'),
                'category' => $this->p_int('category_id'),
                'user_id' => $this->getUserId()
            );

            $arrays = array(
                'gallery' => $this->getFiles('gallery'),
                'phone' => $this->p_array('phone'),
                'mask' => $this->p_array('mask'),
                'options' => $this->p_array('option')
            );

            foreach($arrays['options'] as &$option){
                if(is_array($option)){
                    if(isset($option['checkbox_one'])){
                        $checkboxOne = $option['checkbox_one'];
                        $option = (isset($checkboxOne['hidden']) && !isset($checkboxOne['checkbox'])) ? $checkboxOne['hidden'] : $checkboxOne['checkbox'];
                    }else if(isset($option['checkbox_multi'])){
                        $checkboxMulti = $option['checkbox_multi'];
                        $option = json_encode($checkboxMulti);
                    }
                }
            }

            $this->load('Adverts', 'admin')->edit($this->p_int('id'), $params, $arrays, $this->getUserId());

            return $this->redirect()->toUrl(
                $this->easyUrl(array())
            );
        } else {
            $catalog = $this->session()->catalog;
            $id = $this->p_int('id');
            $getOne = $this->load('Adverts', 'admin')->getOne($id);
            $region = $this->load('AdvertLocation', 'admin')->getRegionByCity($getOne['location']);
            $ret = array(
                'getEdit' => $getOne,
                'getType' => $this->load('AdvertType', 'admin')->get($catalog),
                'getLocation' => $this->load('AdvertLocation', 'admin')->getRegions(),
                'region' => $region,
                'getCurrency' => $this->load('AdvertCurrency', 'admin')->get(),
                'getCategory' => $this->load('AdvertCategory', 'admin')->get($catalog),
                'mainCategory' => $this->load('AdvertCategory', 'admin')->getMainCategories(),
                'catalogName' => $catalog,
                'getGallery' => $this->load('AdvertGallery', 'admin')->generateURL($id),
                'getPhone' => $this->load('AdvertPhone', 'admin')->get($id),
                'phoneMask' => $this->load('Phone', 'admin')->getPhoneMask(),
                'phonePlaceholder' => $this->load('Phone', 'admin')->getPlaceholder(),
                'phoneMaskArray' => $this->load('Phone', 'admin')->getMaskArray(),
                'userName' => $this->load('Users', 'admin')->getName($this->getUserId()),
                'helper' => $this->load('Helps', 'admin')->getTextByUrl(array(
                    'type',
                    'name',
                    'contact_name',
                    'category',
                    'phone',
                    'location',
                    'description',
                    'price',
                ))
            );

            return $this->view($ret);
        }
    }

    public function removeAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');

        $this->load('Adverts', 'admin')->remove($id, $this->getUserId());

        return $this->redirect()->toUrl(
            $this->easyUrl(array())
        );
    }

    public function liftAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');

        if ($this->load('Adverts', 'admin')->lift($id, $this->getUserId())) {
            return $this->redirect()->toUrl(
                $this->easyUrl(array())
            );
        } else {
            return $this->redirect()->toUrl(
                $this->easyUrl(array('controller' => 'error', 'action'=> 'wallet'))
            );    
        }
    }

    public function topAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');

        $ret = $this->load('Adverts', 'admin')->top($id, $this->getUserId());

        if($ret == true){
            $urlParams = array();
        }else{
            $urlParams = array('controller' => 'error', 'action'=> 'wallet');
        }

        return $this->redirect()->toUrl(
            $this->easyUrl($urlParams)
        );

    }

    public function prolongAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');

        $this->load('Adverts', 'admin')->prolong($id, $this->getUserId());

        return $this->redirect()->toUrl(
            $this->easyUrl(array())
        );
    }
    
    public function prolongTopAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');
        $days = $this->p_int('days');

        if ($this->load('Adverts', 'admin')->prolong_top($id, $this->getUserId(), $days)) {
            return $this->redirect()->toUrl(
                $this->easyUrl(array())
            );
        } else {
            return $this->redirect()->toUrl(
                $this->easyUrl(array('controller' => 'error', 'action'=> 'wallet'))
            );    
        }
    }

    public function prolongMarkAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $id = $this->p_int('id');
        $days = $this->p_int('days');

        if ($this->load('Adverts', 'admin')->prolong_mark($id, $this->getUserId(), $days)) {
            return $this->redirect()->toUrl(
                $this->easyUrl(array())
            );
        } else {
            return $this->redirect()->toUrl(
                $this->easyUrl(array('controller' => 'error', 'action'=> 'wallet'))
            );    
        }
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
            'price' => $this->p_int('price'),
            'region' => $this->p_int('region'),
            'location' => $this->p_int('location'),
            'phone_count' => $this->p_int('phone_count')
        );

        $error = array();

        $validItem = $this->load('Validator')->validStringLength($params['name'], 2, 50);
        if ($validItem == false) {
            $error['name'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validGreaterThan($params['region']);
        if ($validItem == false) {
            $error['region'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validGreaterThan($params['location']);
        if ($validItem == false) {
            $error['location'] = $validItem;
        }

        $validItem = $this->load('Validator')->validStringLength($params['contact_name'], 2, 50);
        if ($validItem == false) {
            $error['contact_name'] = $validItem;
        }

        if ($requireParams && in_array('phone', $requireParams['fields']) && $params['phone_count'] == 0) {
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

    public function removePhoneAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $id = $this->p_int('id');
        $advert = $this->p_int('advert');

        $ret = array(
            'status' => $this->load('AdvertPhone', 'admin')->remove($advert, $id, $this->getUserId())
        );

        return $this->json($ret);
    }

    public function removeGalleryAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $id = $this->p_int('id');
        $advert = $this->p_int('advert');

        $ret = array(
            'status' => $this->load('AdvertGallery', 'admin')->remove($advert, $id)
        );

        return $this->json($ret);
    }
}
