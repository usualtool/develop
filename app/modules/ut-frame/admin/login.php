<?php
namespace Controller\Ut_frame\Admin;
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
        if($do=="out"){
            unset($_SESSION['admin']);
            unset($_SESSION['admin_id']);
            setcookie("Nav","ut-frame");
            Inc::GoUrl(Route::Link("ut-frame","login"),"登出UT Develop成功!");
        }
        if($do=="login"){
            if(isset($_SESSION["login_fail"]) && $_SESSION["login_fail"]>=5 && time()-$_SESSION["login_fail_time"]<600){
                Inc::GoUrl("-1","失败次数过多,请10分钟后再试!");
            }
            $username=Inc::SqlCheck($_POST["username"] ?? "");
            $password=Inc::SqlCheck($_POST["password"] ?? "");
            $code=Inc::SqlCheck(strtolower($_POST["code"] ?? ""));
            $ip=Inc::SqlCheck(Inc::GetIp());
            if($_SESSION['authcode']==$code){
                if(!empty($username)&&!empty($password)){
                    $data=Data::QueryData("cms_admin","","username='$username'","","");
                    if($data["querynum"]==1){
                        $rows=$data["querydata"][0];
                        if(password_verify($password,$rows['password'])){
                            if($rows["state"]==1):
                                Inc::GoUrl("-1","账户状态异常!");
                            endif;
                            Data::InsertData("cms_admin_log",array("username"=>$username,"ip"=>$ip,"logintime"=>date('Y-m-d H:i:s',time())));
                            $_SESSION['admin']=$rows['username'];
                            $_SESSION['admin_id']=$rows['id'];
                            $_SESSION['admin_roleid']=$rows['roleid'];
                            $_SESSION['admin_avatar']=$rows['avatar'];
                            session_regenerate_id(TRUE);
                            setcookie("Nav","ut-frame");
                            setcookie("Lock",0);
                            unset($_SESSION["login_fail"]);
                            unset($_SESSION["login_fail_time"]);
                            Inc::GoUrl(Route::Link("ut-frame","index"),"登陆UT Develop成功!");
                        }else{
                            $_SESSION["login_fail"]=(time()-(isset($_SESSION["login_fail_time"])?$_SESSION["login_fail_time"]:0)>600)?1:(isset($_SESSION["login_fail"])?$_SESSION["login_fail"]:0)+1;
                            $_SESSION["login_fail_time"]=time();
                            Inc::GoUrl("-1","账户或密码不匹配!");
                        }
                    }else{
                        $_SESSION["login_fail"]=(time()-(isset($_SESSION["login_fail_time"])?$_SESSION["login_fail_time"]:0)>600)?1:(isset($_SESSION["login_fail"])?$_SESSION["login_fail"]:0)+1;
                        $_SESSION["login_fail_time"]=time();
                        Inc::GoUrl("-1","账户不存在!");
                    }
                }else{
                    $_SESSION["login_fail"]=(time()-(isset($_SESSION["login_fail_time"])?$_SESSION["login_fail_time"]:0)>600)?1:(isset($_SESSION["login_fail"])?$_SESSION["login_fail"]:0)+1;
                    $_SESSION["login_fail_time"]=time();
                    Inc::GoUrl("-1","账户或密码不能为空!");
                }
            }else{
                $_SESSION["login_fail"]=(time()-(isset($_SESSION["login_fail_time"])?$_SESSION["login_fail_time"]:0)>600)?1:(isset($_SESSION["login_fail"])?$_SESSION["login_fail"]:0)+1;
                $_SESSION["login_fail_time"]=time();
                Inc::GoUrl("-1","验证码不正确!");
            }
        }
        $this->app->Open("login.cms");
    }
}