<?php
namespace Controller\Ut_oauth\Front;
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
        require_once dirname(__FILE__).'/'.'session.php';
        $this->app->Runin("data",$oauth["querydata"]);
        $this->app->Open("index.cms");
        if(($_GET["do"] ?? "")=="update"){
            if(empty($_POST["password"])){
                Inc::GoUrl("-1","新密码不能为空");
            }
            if(Data::UpdateData("oauth_users",array("password"=>md5($_POST["password"])),"id='$oauth_uid'")){
                Inc::GoUrl(Route::Link("ut-oauth"),"更新密码成功");
            }else{
                Inc::GoUrl("-1","更新密码失败");
            }
        }
    }
}