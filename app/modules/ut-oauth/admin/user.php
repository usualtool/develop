<?php
namespace Controller\Ut_oauth\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class User{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $pagelink="";
        $page=empty($_GET["page"]) ? 1 : max(1,intval($_GET["page"]));
        $pagenum=10;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("oauth_users","","","addtime desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $this->app->Runin(array("total","curpage","listnum","pagelink","data"),array($totalpage,$page,$pagenum,$pagelink,$querydata));
        $this->app->Open("user.cms");
        if(($_GET["do"] ?? "")=="creat"){
            $username=($_POST["username"] ?? "");
            $password=md5(($_POST["password"] ?? ""));
            $fullname=($_POST["fullname"] ?? "");
            $email=($_POST["email"] ?? "");
            $scope=($_POST["scope"] ?? "");
            if(empty($username) || empty($password) || empty($email)){
                Inc::GoUrl("-1","必填项不能为空");
            }
            if(Data::QueryData("oauth_users","","username=? or email=?","","",array($username,$email))["querynum"]>0){
                Inc::GoUrl("-1","用户名或邮件已存在");
            }else{
                if(Data::InsertData("oauth_users",array(
                    "username"=>"?",
                    "password"=>$password,
                    "fullname"=>"?",
                    "email"=>"?",
                    "addtime"=>date('Y-m-d H:i:s',time())),array($username,$fullname,$email)
                )){
                    Inc::GoUrl(Route::Link("ut-oauth","user"),"创建用户成功");
                }else{
                    Inc::GoUrl("-1","创建用户失败");
                }
            }
        }
    }
}