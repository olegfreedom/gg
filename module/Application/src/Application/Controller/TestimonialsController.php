<?php
namespace Application\Controller;

class TestimonialsController extends \Application\Base\Controller {
    
    public function addAction() {
        $this->log(__CLASS__.'\\'.__FUNCTION__);
        $this->isAjax();
        
        $params = array (
            'advert_id' => $this->p_int('advert_id'),
            'name' => $this->p_string('name'),
            'email' => $this->p_string('email'),
            'message' => $this->p_string('message'),
            'type' => $this->p_select('type','grate',array('grate','advice','complaint','advert')),
            'rating' => $this->p_int('rating')
        );

        $ret = array('status' => false);
        $check = $this->check($params);
        if($check['status'] == true){
            $ret['status'] = $this->load('Testimonials')->add($params);
        }
        
        return $this->json($ret);
    }
    
    
    public function validatorAction(){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();
        
        $params = array(
            'name' => $this->p_string('name'),
            'email' => $this->p_string('email'),
            'message' => $this->p_string('message')
        );
        
        $ret = $this->check($params);
        
        return $this->json($ret);
    }
    
    
    /**
     * @param array $params
     * @return array
     */
    private function check($params = array()){
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        
        $error = array();
        
        $validItem = $this->load('Validator')->validStringLength($params['name'], 2, 150);
        if($validItem == false){
            $error['name'] = $validItem;
        }
        
        $validItem = $this->load('Validator')->validStringLength($params['email'], 5, 200);
        if($validItem == false){
            $error['email'] = $validItem;
        }else{
            $validItem = $this->load('Validator')->validEmail($params['email']);
            if($validItem == false){
                $error['email'] = $validItem;
            }
        }
        
        $validItem = $this->load('Validator')->validStringLength($params['message'], 5, 1000);
        if($validItem == false){
            $error['message'] = $validItem;
        }
        
        $ret = array(
            'status' => (sizeof($error) > 0 ? false : true),
            'error' => $error
        );
        
        return $ret;
    }
}

