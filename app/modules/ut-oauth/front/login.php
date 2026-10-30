<?php
namespace Controller\Ut_oauth\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Login{
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
        $redirect=empty($_GET['redirect']) ? Route::Link("ut-oauth") : urldecode($_GET['redirect']);
        $this->app->Runin("redirect",$redirect);
        $this->app->Open("login.cms");
        if($do=="login"){
            $gourl=urldecode($_POST["gourl"] ?? "");
            $username=Inc::SqlCheck($_POST["username"] ?? "");
            $password=md5(Inc::SqlCheck($_POST["password"] ?? ""));
            if(!empty($username)&&!empty($password)){
                $data=Data::QueryData("oauth_users","","username='$username' and password='$password'");
                if($data["querynum"]==1){
                    $_SESSION['oauth_uid']=$data["querydata"][0]["id"];
                    session_regenerate_id(TRUE);
                    Inc::GoUrl($gourl,"登录成功");
                }else{
                    Inc::GoUrl("-1","账户或密码不匹配");
                }
            }else{
                Inc::GoUrl("-1","账户或密码不能为空");
            }
        }
        if($do=="out"){
            unset($_SESSION['oauth_uid']);
            Inc::GoUrl(Route::Link("ut-oauth"),"退出成功!");
        }
    }
}