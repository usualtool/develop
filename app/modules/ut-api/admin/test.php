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
        $do=empty($_GET["do"]) ? "handle" : $_GET["do"];
        $app=Data::QueryData("cms_api_app","appid,appsecret","status=1","","1")["querydata"][0] ?? array();
        $this->app->Runin(
            array("do","appid","appsecret","hasapp"),
            array($do,(string)($app["appid"] ?? ""),(string)($app["appsecret"] ?? ""),empty($app) ? 0 : 1)
        );
        $this->app->Open("test.cms");
    }
}