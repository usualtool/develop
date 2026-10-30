<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Task{
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
         * 在建项目
         */
        $this->app->Runin("task",array(Inc::Auth("task")));
        /**
         * AD
         */
        $this->app->Runin("ad",explode("^",explode("|",Inc::Auth("upapi"))[1]));
        /**
         * 载入模板
         */
        $this->app->Open("task.cms");
    }
}