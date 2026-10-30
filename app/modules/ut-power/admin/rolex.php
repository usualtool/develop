<?php
namespace Controller\Ut_power\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Rolex{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $id=Inc::SqlCheck($_GET["id"] ?? "");
        $do=Inc::SqlCheck($_GET["do"] ?? "");
        /**
         * 写入模块数据
         */
        $this->app->Runin("modlist",Data::QueryData("cms_module","","bid>0","","")["querydata"]);
        /**
         * 传递参数过程
         */
        if(!empty($id)){
            /**
             * 写入参数
             */
            $this->app->Runin("id",$id);
            /**
             * 写入数据
             */
            $this->app->Runin("data",Data::QueryData("cms_admin_role","","id='$id'","","")["querydata"]);
        }
        /**
         * 载入模板
         */
        $this->app->Open("rolex.cms");
        /**
         * 操作数据
         */
        if($do=="add"){
            if(Data::InsertData("cms_admin_role",array(
                "role"=>Inc::SqlCheck($_POST["role"] ?? ""),
                "module"=>Inc::SqlCheck(implode(",",$_POST["module"] ?? array()))))){
                Inc::GoUrl(Route::Link("ut-power","role"),"创建成功!");
            }else{
                Inc::GoUrl("-1","创建失败!");
            }
        }
        if($do=="mon"){
            $id=Inc::SqlCheck($_POST["id"] ?? "");
            if(Data::UpdateData("cms_admin_role",array(
                "role"=>Inc::SqlCheck($_POST["role"] ?? ""),
                "module"=>Inc::SqlCheck(implode(",",$_POST["module"] ?? array()))),"id='$id'")){
                Inc::GoUrl(Route::Link("ut-power","role"),"编辑成功!");
            }else{
                Inc::GoUrl("-1","编辑失败!");
            }
        }
        if($do=="del"){
            if($id==1){
                Inc::GoUrl(Route::Link("ut-power","role"),"删除失败,第一条记录不可删除!");
            }else{
                if(Data::DelData("cms_admin_role","id='$id'")){
                    Inc::GoUrl(Route::Link("ut-power","role"),"删除成功!");
                }else{
                    Inc::GoUrl("-1","删除失败!");
                }
            }
        }
    }
}