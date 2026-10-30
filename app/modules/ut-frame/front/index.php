<?php
namespace Controller\Ut_frame\Front;
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
        $this->app->Open("index.cms");
    }
}