<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Wechat\MiniPro;
use usualtool\AliOpen\AliOpen;
require'data-verify.php';
/*
 * 小程序授权登录等相关
 * 支持微信、支付宝
**/
if(Data::ModTable("cms_routine")){
    $set=Data::QueryData("cms_routine","","","","1")["querydata"][0];
    $code=Inc::SqlCheck($_GET["code"]);
    $action=Inc::SqlCheck($_GET["action"]);
    if($action=="wechat"){
        $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
        $result=json_encode($minipro->ChkLogin($code));
    }elseif($action=="wechat-crypt"){
        $encrypteddata=$_POST["encrypteddata"];
        $iv=$_POST["iv"];
        $openid=Inc::SqlCheck($_POST["openid"]);
        $thirdkey=$_POST["thirdkey"];
        $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
        $errcode=$minipro->DecryptData($thirdkey,$encrypteddata,$iv,$data);
        if($errcode==0){
            $result=$data;
        }else{
            $result=$errcode;
        }
    }elseif($action=="wechat-phone"){
        $minipro=new MiniPro($set["wxrteid"],$set["wxrtesecret"]);
        $res=$minipro->GetPhone($_POST["code"]);
        $result=(isset($res["errcode"]) && $res["errcode"]==0 && isset($res["phone_info"])) ? json_encode($res["phone_info"]) : json_encode($res);
    }elseif($action=="tt"){
        $body=json_encode(array("appid"=>$set["ttrteid"],"secret"=>$set["ttrtesecret"],"code"=>$code));
        $ch=curl_init("https://developer.toutiao.com/api/apps/v2/jscode2session");
        curl_setopt_array($ch,array(
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$body,
            CURLOPT_HTTPHEADER=>array("Content-Type: application/json"),
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_TIMEOUT=>10
        ));
        $out=curl_exec($ch);
        if(PHP_VERSION_ID<80000){curl_close($ch);}
        $result=$out;
    }elseif($action=="alipay"){
        try{
            $privkey=trim((string)$set["alapppubkey"]);
            $isPem = strpos($privkey,"PRIVATE KEY")!==false;
            $isRawBase64 = !$isPem && preg_match('/^[A-Za-z0-9+\/=\s]{300,}$/',$privkey);
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