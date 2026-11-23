<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Data;
use usualtool\Lib\Inc;
use usualtool\Wechat\MiniPro;
use usualtool\Douyin\MiniPro as DouyinMiniPro;
use usualtool\AliOpen\AliOpen;
use Model\Ut_api\Verify;
class Data_auth{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check();
        $l=$_GET["l"] ?? $_POST["l"] ?? "";
        $action=$_GET["action"] ?? $_POST["action"] ?? "";
        /*
         * 小程序相关
         * 支持微信、支付宝、抖音
        **/
        if(empty($l) || $l=="minipro"){
            $mini=Data::QueryData("cms_routine","","","","1")["querydata"][0] ?? "";
            $code=$_GET["code"] ?? "";
            if(strpos($action,"wechat")!==false){
                $wechat=new MiniPro($mini["wxrteid"],$mini["wxrtesecret"]);
            }elseif(strpos($action,"douyin")!==false){
                $douyin=new DouyinMiniPro($mini["ttrteid"],$mini["ttrtesecret"]);
            }else{
                $alipay=new AliOpen($mini["alrteid"],$mini["alapppubkey"],$mini["alpaypubkey"]);
            }
            if($action=="wechat"){
                $result=json_encode($wechat->ChkLogin($code));
            }elseif($action=="wechat-crypt"){
                $errcode=$wechat->DecryptData($_POST["thirdkey"] ?? "",$_POST["encrypteddata"] ?? "",$_POST["iv"] ?? "",$data);
                $result=$errcode==0 ? $data : $errcode;
            }elseif($action=="wechat-phone"){
                $res=$wechat->GetPhone($_POST["code"] ?? "");
                $result=(isset($res["errcode"]) && $res["errcode"]==0 && isset($res["phone_info"])) ? json_encode($res["phone_info"]) : json_encode($res);
            }elseif($action=="douyin"){
                $res=$douyin->ChkLogin($code,$_GET["anonymous_code"] ?? "");
                $result=is_array($res) ? json_encode($res) : '[{"error":1}]';
            }elseif($action=="douyin-phone"){
                $res=$douyin->GetPhone($_POST["code"] ?? "");
                $result=(isset($res["err_no"]) && $res["err_no"]==0 && isset($res["data"])) ? json_encode($res["data"]) : json_encode($res);
            }elseif($action=="alipay"){
                try{
                    $info=$alipay->GetUser($code);
                    $result=!empty($info["user_id"]) ? json_encode($info) : '[{"error":1}]';
                }catch(\Throwable $e){
                    $result=json_encode(array("error_response"=>array("code"=>"local-error","msg"=>"data-auth error","sub_msg"=>$e->getMessage())));
                }
            }else{
                $result=json_encode(["error"=>1]);
            }
            echo $result;
        /*
         * 账号相关
         * 支持可视包超管和会员模块
        **/
        }elseif($l=="account"){
            if($action=="check"){
                $secret=trim((string)($_GET["secret"] ?? $_POST["secret"] ?? ""));
                $result=1;
                $expire=0;
                if(preg_match('/^[a-f0-9]{48}$/',$secret)){
                    $appid=trim((string)($_SERVER["HTTP_APPID"] ?? ""));
                    $row=Data::QueryData("cms_api_secret","addtime,ip","secret=? and usetime is null and appid=?","","1",array($secret,$appid))["querydata"][0] ?? "";
                    if(!empty($row)){
                        $age=time()-strtotime((string)$row["addtime"]);
                        if($age>=0 && $age<86400 && trim((string)$row["ip"])===Inc::GetIp()){
                            $result=0;
                            $expire=86400-$age;
                        }else{
                            Data::RunYu("delete from `cms_api_secret` where secret=? and usetime is null",array($secret));
                        }
                    }
                }
                echo json_encode(["error"=>$result,"expire"=>$expire]);
                return;
            }
            if($action=="logout"){
                $secret=trim((string)($_GET["secret"] ?? $_POST["secret"] ?? ""));
                if(preg_match('/^[a-f0-9]{48}$/',$secret)){
                    $appid=trim((string)($_SERVER["HTTP_APPID"] ?? ""));
                    Data::RunYu("delete from `cms_api_secret` where secret=? and usetime is null and appid=?",array($secret,$appid));
                }
                echo json_encode(["error"=>0]);
                return;
            }
            $username=trim((string)($_GET["username"] ?? $_POST["username"] ?? ""));
            $password=(string)($_GET["password"] ?? $_POST["password"] ?? "");
            $table=$action=="member" ? "cms_member" : ($action=="admin" ? "cms_admin" : "");
            $secret="";
            if($table!=="" && $username!=="" && $password!==""){
                $row=Data::QueryData($table,"","username=?","","1",array($username))["querydata"][0] ?? "";
                if(!empty($row) && password_verify($password,$row["password"]) && ($table=="cms_member" || $row["state"]!=1)){
                    Data::RunYu("delete from `cms_api_secret` where appid=? and ip=? and usetime is null",array(trim((string)($_SERVER["HTTP_APPID"] ?? "")),Inc::GetIp()));
                    if(mt_rand(1,20)===1){
                        Data::RunYu("delete from `cms_api_secret` where usetime is null and addtime<?",array(date("Y-m-d H:i:s",time()-172800)));
                    }
                    $secret=bin2hex(random_bytes(24));
                    Data::RunYu("insert ignore into `cms_api_secret` (secret,addtime,ip,appid) values (?,?,?,?)",array($secret,date("Y-m-d H:i:s"),Inc::GetIp(),trim((string)($_SERVER["HTTP_APPID"] ?? ""))));
                }
            }
            echo json_encode(["error"=>$secret==="" ? 1 : 0,"secret"=>$secret]);
        }
    }
}