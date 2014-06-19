<?php
namespace Application\Controller;

class CatalogUslugiController extends \Application\Base\AbstractCatalogController
{
    protected $CATALOG = 'uslugi';

    public function __construct()
    {
        parent::__construct();

        $this->pushTitle('Главная');
    }
    
    protected function getFormParams($params = array()) {
        $search_text = $this->p_string('search_text');
        $page = $this->p_int('page', 1);
        $advertsType = $this->p_int('type', $this->load('AdvertType', 'admin')->getDefaultTypeId($this->CATALOG));
        $params['page'] = $page;
        $params['type'] = $advertsType;
        $params['price_min'] = $this->p_int('price_min');
        $params['price_max'] = $this->p_int('price_max');
        $params['rate_min'] = $this->p_int('rate_min');
        $params['rate_max'] = $this->p_int('rate_max');
        $params['region'] = $this->p_int('region');
        $params['town'] = $this->p_int('town');
        $params['category'] = $this->p_string('category', $this->CATALOG);
        $params['image'] = $this->p_select('image', 'n', array('n', 'y'));
        $params['top'] = $this->p_select('top', 'n', array('n', 'y'));
        $params['map'] = $this->p_select('map', 'n', array('n', 'y'));
        $params['search_text'] = $search_text;
        
        return $params;
    }
    
    public function validatorAction() {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $error = array();

        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        
        return $this->json($ret);
    }
    
}
