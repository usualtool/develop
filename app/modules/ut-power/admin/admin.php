<?php
namespace Controller\Ut_power\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Admin{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $id=($_GET["id"] ?? "");
        $do=($_GET["do"] ?? "");
        /**
         * 写入角色数据
         */
        $this->app->Runin("role",Data::QueryData("cms_admin_role","","","","")["querydata"]);
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
            $this->app->Runin("data",Data::QueryData("cms_admin","","id=?","","",array(intval($id)))["querydata"]);
        }
        /**
         * 载入模板
         */
        $this->app->Open("admin.cms");
        /**
         * 操作数据
         */
        if($do=="add"){
            $password=($_POST["password"] ?? "");
            $passwords=($_POST["passwords"] ?? "");
            if($password==$passwords){
                $passwordx=password_hash($password,PASSWORD_BCRYPT,array('cost'=>12));
                if(Data::InsertData("cms_admin",array(
                    "roleid"=>"?",
                    "username"=>"?",
                    "password"=>$passwordx,
                    "avatar"=>"?",
                    "addtime"=>date('Y-m-d H:i:s',time())),array(
                    ($_POST["roleid"] ?? ""),
                    ($_POST["username"] ?? ""),
                    ($_POST["avatar"] ?? "")))){
                    Inc::GoUrl(Route::Link("ut-power"),"创建成功!");
                }else{
                    Inc::GoUrl("-1","创建失败!");
                }
            }else{
                Inc::GoUrl("-1","两次密码不一致!");
            }
        }
        if($do=="mon"){
            $id=($_POST["id"] ?? "");
            $password=($_POST["password"] ?? "");
            $passwords=($_POST["passwords"] ?? "");
            $update=array(
                "roleid"=>"?",
                "username"=>"?",
                "avatar"=>"?"
            );
            $param=array(
                ($_POST["roleid"] ?? ""),
                ($_POST["username"] ?? ""),
                ($_POST["avatar"] ?? "")
            );
            if($password!="" || $passwords!=""){
                if($password!=$passwords){
                    Inc::GoUrl("-1","两次密码不一致!");
                }
                $update["password"]=password_hash($password,PASSWORD_BCRYPT,array('cost'=>12));
            }
            if(Data::UpdateData("cms_admin",$update,"id=?",array_merge($param,array(intval($id))))){
                Inc::GoUrl(Route::Link("ut-power"),"编辑成功!");
            }else{
                Inc::GoUrl("-1","编辑失败!");
            }
        }
        if($do=="del"){
            $adminnum=Data::QueryData("cms_admin","","","","","0")["querynum"];
            if($adminnum==1){
                Inc::GoUrl("-1","删除失败,已经是最后一条记录!");
            }else{
                if(Data::DelData("cms_admin","id=?",array(intval($id)))){
                    Inc::GoUrl(Route::Link("ut-power"),"删除成功!");
                }else{
                    Inc::GoUrl("-1","删除失败!");
                }
            }
        }
    }
}