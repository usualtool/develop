<?php
namespace Controller\Ut_plugin\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
use Model\Ut_plugin\Plugin as PluginModel;
class Plugin{
    private $app;
    private $model;
    public function __construct(){
        global $app;
        $this->app=$app;
        $this->model=new PluginModel();
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $do=$_GET["do"] ?? "";
        /**
         * 载入私有插件
         */
        $this->app->Runin("private_plugin",$this->model->Filter($this->model->GetPlugin()));
        /**
         * 载入第三方插件
         */
        $this->app->Runin("pubilc_plugin",$this->model->Filter($this->model->GetPlugin(1)));
        /**
         * 载入订购插件
         */
        $this->app->Runin("buy_plugin",$this->model->Filter($this->model->GetPlugin(2)));
        /**
         * 载入模板
         */
        $this->app->Open("plugin.cms");
        if($do=="install"){
            $t=$_GET["t"] ?? "";
            $pid=str_replace(".","",Inc::SqlCheck($_GET["pid"] ?? ""));
            if($t!="s"){
                $down="";
                if($t=="g"){
                    $down=Inc::Auth("plugin-".$pid);
                }elseif($t=="b"){
                    $down=Inc::Auth("pluginorder-".$pid);
                }
                $downurl=Inc::StrSubstr("<downurl>","</downurl>",$down);
                $filename=basename($downurl);
                $res=Inc::SaveFile($downurl,APP_ROOT."/plugins",$filename,1);
                if(!empty($res)){
                    Inc::Auth("plugindel-".str_replace(".zip","",$filename));
                    $zip=new \ZipArchive;
                    if($zip->open(APP_ROOT."/plugins/".$filename)===TRUE){
                        $zip->extractTo(APP_ROOT."/plugins/");
                        $zip->close();
                        unlink(APP_ROOT."/plugins/".$filename);
                    }else{
                        Inc::GoUrl("-1","plugins目录775权限不足!");
                    }
                }else{
                    Inc::GoUrl("-1","安装权限不足!");
                }
            }
            if(is_dir(APP_ROOT."/plugins/".$pid."/assets")){
                $assets_dir=OPEN_ROOT."/assets/plugins/".$pid;
                if(!is_dir($assets_dir)){
                    Inc::MakeDir($assets_dir);
                }
                Inc::MoveDir(APP_ROOT."/plugins/".$pid."/assets",$assets_dir);
                Inc::DelDir(APP_ROOT."/plugins/".$pid."/assets");
            }
            $pconfig=APP_ROOT."/plugins/".$pid."/usualtool.config";
            $plugins=file_get_contents($pconfig);
            $cate=Inc::StrSubstr("<cate>","</cate>",$plugins);
            $auther=Inc::StrSubstr("<auther>","</auther>",$plugins);
            $title=Inc::StrSubstr("<title>","</title>",$plugins);
            $ver=Inc::StrSubstr("<ver>","</ver>",$plugins);
            $description=Inc::StrSubstr("<description>","</description>",$plugins);
            $installsql=Inc::StrSubstr("<installsql><![CDATA[","]]></installsql>",$plugins);
            if(Data::QueryData("cms_plugin","","pid='$pid'","","1")["querynum"]>0){
                Data::UpdateData("cms_plugin",array(
                    "type"=>$cate,
                    "auther"=>$auther,
                    "title"=>$title,
                    "ver"=>$ver,
                    "description"=>$description),"pid='$pid'");
            }else{
                Data::InsertData("cms_plugin",array(
                    "pid"=>$pid,
                    "type"=>$cate,
                    "auther"=>$auther,
                    "title"=>$title,
                    "ver"=>$ver,
                    "description"=>$description));
            }
            if($installsql=='0'){
                Inc::GoUrl(Route::Link("ut-plugin","plugin"),"成功安装插件!");
            }else{
                if(Data::RunSql($installsql)){
                    Inc::GoUrl(Route::Link("ut-plugin","plugin"),"成功安装插件!");
                }else{
                    Inc::GoUrl("-1","插件安装失败!");
                }
            }
        }
    }
}