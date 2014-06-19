<?php
namespace Profile\Controller;

class StatisticsController extends \Profile\Base\Controller
{
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
            'inboxList' => $this->load('Statistics', 'profile')->get($userId),
        );

        return $this->view($ret);
    }

    public function getStatisticAction()
    {
        $this->log(__CLASS__ . '\\' . __FUNCTION__);
        $this->isAjax();

        $params = array(
            'advert_id' => $this->p_int('advert_id'),
        );

        $ret = array(
            'list' => $this->load('Statistics', 'profile')->getStatisticByAdvert($params['advert_id']),
        );

        return $this->json($ret);
    }
}