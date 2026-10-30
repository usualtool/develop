<?php
namespace Controller\Ut_oauth\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Help{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        require_once dirname(__FILE__).'/'.'session.php';
        $this->app->Open("help.cms");
    }
}