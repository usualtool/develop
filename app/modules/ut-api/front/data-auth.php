<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Data;
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
        /*
         * 小程序授权登录等相关
         * 支持微信、支付宝、抖音
        **/
        if(Data::ModTable("cms_routine")){
            $set=Data::QueryData("cms_routine","","","","1")["querydata"][0] ?? "";
            if(empty($set)){
                echo'[{"error":1}]';
                return;
            }
            $code=($_GET["code"] ?? "");
            $action=($_GET["action"] ?? "");
            if($action=="wechat"){
                $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
                $result=json_encode($minipro->ChkLogin($code));
            }elseif($action=="wechat-crypt"){
                $encrypteddata=$_POST["encrypteddata"] ?? "";
                $iv=$_POST["iv"] ?? "";
                $openid=($_POST["openid"] ?? "");
                $thirdkey=$_POST["thirdkey"] ?? "";
                $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
                $errcode=$minipro->DecryptData($thirdkey,$encrypteddata,$iv,$data);
                if($errcode==0){
                    $result=$data;
                }else{
                    $result=$errcode;
                }
            }elseif($action=="wechat-phone"){
                $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
                $res=$minipro->GetPhone($_POST["code"] ?? "");
                $result=(isset($res["errcode"]) && $res["errcode"]==0 && isset($res["phone_info"])) ? json_encode($res["phone_info"]) : json_encode($res);
            }elseif($action=="tt"){
                $minipro=new DouyinMiniPro($set["ttrteid"],$set["ttrtesecret"]);
                $res=$minipro->ChkLogin($code,($_GET["anonymous_code"] ?? ""));
                $result=is_array($res) ? json_encode($res) : '[{"error":1}]';
            }elseif($action=="alipay"){
                try{
                    $ali=new AliOpen($set["alrteid"],$set["alapppubkey"],$set["alpaypubkey"]);
                    $info=$ali->GetUser($code);
                    if(!empty($info["user_id"])){
                        $result=json_encode($info);
                    }else{
                        $result='[{"error":1}]';
                    }
                }catch(\Throwable $e){
                    $result=json_encode(array("error_response"=>array("code"=>"local-error","msg"=>"data-auth error","sub_msg"=>$e->getMessage())));
                }
            }else{
                $appid="";
                $appkey="";
                $result="0";
            }
            echo$result;
        }else{
            echo'[{"error":1}]';
        }
    }
}