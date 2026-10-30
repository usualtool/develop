<?php
namespace Controller\Ut_api\Admin;
use usualtool\Lib\Data;
class Test{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        $do=empty($_GET["do"]) ? "handle" : $_GET["do"];
        $api=Data::QueryData("cms_admin","","","id asc","1")["querydata"][0];
        $this->app->Runin(
            array("do","key","secret"),
            array($do,md5($config["UTCODE"]),$api["username"].",".$api["password"])
        );
        $this->app->Open("test.cms");
    }
}