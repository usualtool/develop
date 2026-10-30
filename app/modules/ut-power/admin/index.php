<?php
namespace Controller\Ut_power\Admin;
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
        $do=$_GET["do"] ?? "";
        /**
         * 写入数据
         */
        $this->app->Runin("datalist",Data::QueryData("cms_admin","","","addtime desc","")["querydata"]);
        /**
         * 载入模板
         */
        $this->app->Open("index.cms");
        if($do=="update"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            $state=Inc::SqlCheck($_GET["state"] ?? "");
            if(Data::UpdateData("cms_admin",array("state"=>$state),"id='$id'")){
                Inc::GoUrl(Route::Link("ut-power"),"更新状态成功!");
            }else{
                Inc::GoUrl("-1","更新状态失败!");
            }
        }
    }
}