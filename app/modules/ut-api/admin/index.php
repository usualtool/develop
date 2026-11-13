<?php
namespace Controller\Ut_api\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
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
        global $config;
        $do=$_GET["do"] ?? "";
        $this->app->Runin("apikey",md5($config["UTCODE"]));
        $this->app->Runin("data",Data::QueryData("cms_api","","","","1")["querydata"]);
        $this->app->Open("index.cms");
        if($do=="config"){
            $id=($_POST["id"] ?? "");
            $white=($_POST["white"] ?? "");
            $opentable=($_POST["opentable"] ?? "");
            if(Data::UpdateData("cms_api",array(
                "token"=>"?",
                "white"=>"?",
                "opentable"=>"?",
            ),"id=?",array(md5($config["UTCODE"]),$white,$opentable,intval($id)))){
                Inc::GoUrl(Route::Link("ut-api"),"保存配置成功!");
            }else{
                Inc::GoUrl("-1","保存配置失败!");
            }
        }
    }
}