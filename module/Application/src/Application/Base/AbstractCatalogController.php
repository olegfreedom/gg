<?php
namespace Application\Base;

abstract class AbstractCatalogController extends Controller {

    public function __construct()
    {
        parent::__construct();

        if($this->session()->catalog !== null && $this->session()->catalog !== $this->CATALOG){
            $this->session()->catalog = $this->CATALOG;
            $this->load('Adverts')->generateCategoryMenu($this->CATALOG);
        }
    }

    public function indexAction($params = array())
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        
        $params = $this->getFormParams($params);
        
        if ($this->p_int('search-form') === 1) {
        
            $params['search_text'] = trim($params['search_text']);
            if (!empty($params['search_text'])) {
                $this->load('SearchLog', 'admin')->add($params['search_text']);
            }
            return $this->redirect()->toUrl($this->easyUrl($this->urlParams($params), $this->urlQuery($params)));
        }
        $category = isset($params['category']) ? $params['category'] : 0;
        $ret = array(
            'categoryList' => $this->load('AdvertCategory', 'admin')->get($this->CATALOG),
            'searchText' => $params['search_text'],
            'currentCategory' => $category,
            'advertList' => $this->load('Adverts')->getList($params),
            'randomTopAdverts' => $this->load('Adverts')->getRandomTopList($params, 5),
            'locationList' => $this->load('AdvertLocation', 'admin')->getRegions(),
            'paginator' => $this->load('Adverts')->getPaginator($params),
            'typeList' => $this->load('AdvertType', 'admin')->get($this->CATALOG),
            'advertsType' => $params['type'],
            'params' => $params,
            'maxPrice' => $this->load('Adverts')->getMaxPrice(),
            'bannersList' => $this->load('Banners', 'admin')->getList(),
            'advertsCount' => $this->load('Adverts')->getListCount($params),
            'catalogUrl' => $this->CATALOG
        );

        return $this->view($ret, 'application/catalog/index.phtml');
    }

    public function searchAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $params = array(
            'category' => $this->p_int('category')
        );

        return $this->indexAction($params);
    }
    
    public function phoneAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $id = $this->p_int('id');
        
        $ret = array(
            'phone' => $this->load('AdvertPhone', 'admin')->get($id)
        );

        return $this->json($ret);
    }

    public function searchValidatorAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $params = array(
            'search_text' => $this->p_string('search_text'),
        );

        $error = array();

        $validItem = $this->load('Validator')->validStringLength($params['search_text'], 0, 100);
        if ($validItem == false) {
            $error['search_text'] = $validItem;
        }

        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        return $this->json($ret);
    }

    public function getCityAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $region_id = $this->p_int('region');

        $ret = array(
            'citiesList' => $this->load('AdvertLocation', 'admin')->getCitiesByRegion($region_id)
        );
        
        return $this->json($ret);
    }

    /**
     * filtered return parameters
     * @param array $params
     * @return array
     */
    public function urlParams($params)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        if (isset($params) && !empty($params)) {
            foreach ($params as $key => $item) {
                if (empty($item) || $item === 'n') {
                    unset($params[$key]);
                }
            }

            if (isset($params['search_text'])) {
                unset($params['search_text']);
            }
            if (isset($params['page'])) {
                unset($params['page']);
            }
        }


        $params['action'] = 'search';
        return $params;
    }

    /**
     * return query param
     * @param array $params
     * @return array
     */
    public function urlQuery($params)
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);

        $query = array();

        if (isset($params['search_text']) && !empty($params['search_text'])) {
            $query['search_text'] = urlencode($params['search_text']);
        }

        return $query;
    }
}
