<?php
namespace Controller\Ut_power\Admin;
use usualtool\Lib\Data;
class Role{
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
         * 写入数据
         */
        $this->app->Runin("datalist",Data::QueryData("cms_admin_role","","","id desc","")["querydata"]);
        /**
         * 载入模板
         */
        $this->app->Open("role.cms");
    }
}