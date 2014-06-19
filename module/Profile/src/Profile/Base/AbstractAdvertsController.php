<?php
namespace Profile\Base;

abstract class AbstractAdvertsController extends Controller {

    public function loadOptionsAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $params = array(
            'advert_id' => $this->p_int('advert_id'),
            'category' => $this->p_int('category')
        );

        $ret = array(
            'optionsList' => $this->load('Options', 'admin')->getList($params, true),
        );

        return $this->json($ret);
    }

    public function getFieldsAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $categoryId = $this->p_int('category_id');

        $ret = $this->load('RequireParams', 'admin')->getOne($categoryId);

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

    public function getCategoriesAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();


        $category = $this->p_string('category');
        $arrayCats = $this->load('AdvertCategory', 'admin')->get($category);

        $ret = array(
            'categoryList' => $this->load('AdvertCategory', 'admin')->getCategorySelect($arrayCats, true, ''),
            'typeList' => $this->load('AdvertType', 'admin')->get($category)
        );

        return $this->json($ret);
    }

    public function loadMainCategoryAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $parent = null;

        $category = $this->p_int('category');

        if ($category > 0){
            $parent = $this->load('AdvertCategory', 'admin')->getParentsArray($category);
            $parent = $this->load('AdvertCategory', 'admin')->getOne($parent[0]);
        }

        $ret = array(
            'category' => $parent['url']
        );

        return $this->json($ret);
    }
}