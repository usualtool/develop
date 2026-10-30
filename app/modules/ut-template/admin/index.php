<?php
namespace Controller\Ut_template\Admin;
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
        $do=Inc::SqlCheck($_GET["do"] ?? "");
        $data=Data::QueryData("cms_template","","","","")["querydata"];
        /**
         * 载入模板文件管理器
         */
        $temp=$this->app->GetTempFile();
        $front_temp=$temp["front"];
        $admin_temp=$temp["admin"];
        $this->app->Runin(array("do","tempdata","front","admin"),array($do,$data,$front_temp,$admin_temp));
        /**
         * 载入模板
         */
        $this->app->Open("index.cms");
        if($do=="front" || $do=="admin"){
            $tempid=Inc::SqlCheck($_GET["tempid"] ?? "");
            if(!preg_match('/^[A-Za-z0-9_-]+$/',$tempid)){
                Inc::GoUrl("-1","参数非法!");
            }
            $movedir=APP_ROOT."/template/".$tempid."/move";
            $info = file_get_contents(UTF_ROOT."/.ut.config");
            if($do=="front"){
                if(Data::UpdateData("cms_template",array("makefront"=>0),"id>0")){
                    Data::UpdateData("cms_template",array("makefront"=>1),"tid='$tempid'");
                    $info = preg_replace("/TEMPFRONT=(.*)/","TEMPFRONT={$tempid}",$info);
                    file_put_contents(UTF_ROOT."/.ut.config",$info);
                    if(is_dir($movedir)){
                        $this->app->CreatBak($movedir);
                        Inc::MoveDir($movedir,UTF_ROOT);
                    }
                    Inc::GoUrl(Route::Link("ut-template","index","do=template"),"设置成功!");
                }else{
                    Inc::GoUrl("-1","设置失败!");
                }
            }else{
                if(Data::UpdateData("cms_template",array("makeadmin"=>0),"id>0")){
                    Data::UpdateData("cms_template",array("makeadmin"=>1),"tid='$tempid'");
                    $info = preg_replace("/TEMPADMIN=(.*)/","TEMPADMIN={$tempid}",$info);
                    file_put_contents(UTF_ROOT."/.ut.config",$info);
                    if(is_dir($movedir)){
                        $this->app->CreatBak($movedir);
                        Inc::MoveDir($movedir,UTF_ROOT);
                    }
                    Inc::GoUrl(Route::Link("ut-template","index","do=template"),"设置成功!");
                }else{
                    Inc::GoUrl("-1","设置失败!");
                }
            }
        }
        if($do=="unfront" || $do=="unadmin"){
            $tempid=Inc::SqlCheck($_GET["tempid"] ?? "");
            if(!preg_match('/^[A-Za-z0-9_-]+$/',$tempid)){
                Inc::GoUrl("-1","参数非法!");
            }
            $movedir=APP_ROOT."/template/".$tempid."/move";
            $info = file_get_contents(UTF_ROOT."/.ut.config");
            if($do=="unfront"){
                if(Data::UpdateData("cms_template",array("makefront"=>0),"tid='$tempid'")){
                    $info = preg_replace("/TEMPFRONT=(.*)/","TEMPFRONT=0",$info);
                    file_put_contents(UTF_ROOT."/.ut.config",$info);
                    $this->app->Rollback($movedir);
                    Inc::GoUrl(Route::Link("ut-template","index","do=template"),"取消成功!");
                }else{
                    Inc::GoUrl("-1","取消失败!");
                }
            }else{
                if(Data::UpdateData("cms_template",array("makeadmin"=>0),"tid='$tempid'")){
                    $info = preg_replace("/TEMPADMIN=(.*)/","TEMPADMIN=0",$info);
                    file_put_contents(UTF_ROOT."/.ut.config",$info);
                    $this->app->Rollback($movedir);
                    Inc::GoUrl(Route::Link("ut-template","index","do=template"),"取消成功!");
                }else{
                    Inc::GoUrl("-1","取消失败!");
                }
            }
        }
    }
}