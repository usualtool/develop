<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Session{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $app,$m,$p,$config;
        if(isset($_SESSION['admin'])&&isset($_SESSION['admin_id'])&&!empty($_SESSION['admin'])&&!empty($_SESSION['admin_id'])){
            if(($_SERVER["REQUEST_METHOD"] ?? "")=="POST"){
                $selfhost=parse_url($config["APPURL"],PHP_URL_HOST);
                $originhost=parse_url($_SERVER["HTTP_ORIGIN"] ?? "",PHP_URL_HOST);
                $refererhost=parse_url($_SERVER["HTTP_REFERER"] ?? "",PHP_URL_HOST);
                if($originhost!==$selfhost && $refererhost!==$selfhost){
                    Inc::GoUrl("-1","ERROR!");
                }
            }
            /**
            * 获取版本号并载入应用部分设置
            */
            $framework=file_get_contents(UTF_ROOT."/.version.ini");
            $version=substr($framework,0,5);
            $version_time=substr($framework,-6);
            $app->Runin(
                array("version","version_time","update","develop","develop_open","lock","editor"),
                array($version,$version_time,$config["UPDATEURL"],$config["DEVELOP"],$config["DEVELOP_OPEN"],$config["LOCKSCREEN"],$config["EDITOR"])
            );
            /**
             * 接收官方消息
             */
            $message=Inc::Auth("message");
            $app->Runin("message",explode("|",$message));
            /**
             * 底层模块
             * 除开UT-FRAME公共模块
             */
            $app->Runin("modules",Data::QueryData("cms_module","","bid=3 and mid<>'".$config["DEFAULT_MOD"]."'","","")["querydata"]);
            /**
             * 自定义模块
             */
            $app->Runin("custmods",Data::QueryData("cms_module","","bid<>3")["querydata"]);
            /**
             * 当前运行模块及栏目
             * befoitem前端地址集，backtem后端栏目集
             */
            $thismod=Data::QueryData("cms_module","","mid='$m'","","")["querydata"][0];
            $app->Runin(
                array("title","befoitem","backitem"),
                array($thismod["modname"],$thismod["befoitem"],$thismod["backitem"])
            );
            $admin=$_SESSION['admin'];
            $admin_id=$_SESSION['admin_id'];
            $admin_roleid=$_SESSION['admin_roleid'];
            $admin_avatar=$_SESSION['admin_avatar'];
            $adminnum=Data::QueryData("cms_admin","","id='$admin_id' and username='$admin'")["querynum"];
            if($adminnum!==1){
                Inc::GoUrl(Route::Link("ut-frame","login"));
            }else{
                $myrole=Data::QueryData("cms_admin_role","","id='$admin_roleid'","","")["querydata"][0];
                $app->Runin(array("admin_id","admin_role","admin_user","admin_avatar"),array($admin_id,$myrole["role"],$admin,$admin_avatar));
                if(!Inc::Contain($m,explode(",",$myrole["module"]))){
                    Inc::GoUrl("-1","权限不足!");
                }
            }
        }else{
            Inc::GoUrl(Route::Link("ut-frame","login"));
        }
    }
}