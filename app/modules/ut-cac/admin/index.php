<?php
namespace Controller\Ut_cac\Admin;
class Index{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        /**
         * 载入模板
         */
        $this->app->Open("index.cms");
    }
}