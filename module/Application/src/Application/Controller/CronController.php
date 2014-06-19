<?php
namespace Application\Controller;

class CronController extends \Base\Mvc\Controller
{
    public function dailyAction()
    {
      /** TODO: Example */
        $this->load('SendEmail', 'admin')->expiryTimeTop(1);
        $this->load('SendEmail', 'admin')->expiryTimeTop(3);
        $this->load('SendEmail', 'admin')->expiryTimeTop(7);
        $this->load('SendEmail', 'admin')->expiryTimeMark(1);
        $this->load('SendEmail', 'admin')->expiryTimeMark(3);
        $this->load('SendEmail', 'admin')->expiryTimeMark(7);
        
        die();
    }
}