<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Data;
use usualtool\Lib\Inc;
use Model\Ut_api\Verify;
class Data_secret{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $action=strtolower(trim((string)($_GET["action"] ?? $_POST["action"] ?? "")));
        if($action==="sign"){
            $this->Sign();
        }else{
            $this->Secret();
        }
    }
    /**
     * 领签名
     */
    private function Sign(){
        (new Verify())->Gate();
        $now=time();
        $ip=Inc::GetIp();
        $appid=trim((string)($_SERVER["HTTP_APPID"] ?? ""));
        if($appid===""){
            echo '[{"error":"Appid Empty"}]';
            return;
        }
        if(!preg_match('/^[A-Za-z0-9_\-]{1,100}$/',$appid)){
            echo '[{"error":"Appid Error"}]';
            return;
        }
        $app=Data::QueryData("cms_api_app","appsecret,bindip,signlimit","appid=? and status=1","","1",array($appid));
        if($app["querynum"]<=0){
            echo '[{"error":"Appid Error"}]';
            return;
        }
        $approw=$app["querydata"][0];
        $bindip=trim((string)$approw["bindip"]);
        if($bindip!==""){
            $ips=array_map('trim',explode(",",$bindip));
            if(!in_array($ip,$ips,true)){
                echo '[{"error":"IP Error"}]';
                return;
            }
        }
        $limit=(int)($approw["signlimit"] ?? 60);
        if($limit<=0){
            $limit=60;
        }
        $cnt=Data::RunYu("select count(*) as n from `cms_api_nonce` where ip=? and appid=? and addtime>=?",array($ip,$appid,date('Y-m-d H:i:s',$now-60)));
        if((int)($cnt["querydata"][0]["n"] ?? 0)>=$limit){
            echo '[{"error":"Sign Busy"}]';
            return;
        }
        $time=(string)$now;
        $nonce=bin2hex(random_bytes(16));
        $sign=md5((string)$approw["appsecret"].$time.$nonce);
        if(!Data::InsertData("cms_api_nonce",array("appid"=>$appid,"nonce"=>$nonce,"addtime"=>date('Y-m-d H:i:s',$now),"ip"=>$ip,"usetime"=>null))){
            echo '[{"error":"Sign Failed"}]';
            return;
        }
        echo json_encode(array("time"=>$time,"nonce"=>$nonce,"sign"=>$sign,"expire"=>300));
    }
    /**
     * 领写票据
     */
    private function Secret(){
        (new Verify())->Check();
        $now=time();
        $ip=Inc::GetIp();
        $appid=trim((string)($_SERVER["HTTP_APPID"] ?? ""));
        if($appid===""){
            echo '[{"error":"Appid Empty"}]';
            return;
        }
        if(mb_strlen($appid)>64){
            $appid=mb_substr($appid,0,64);
        }
        $cnt=Data::RunYu("select count(*) as n from `cms_api_secret` where ip=? and addtime>=?",array($ip,date('Y-m-d H:i:s',$now-60)));
        if((int)($cnt["querydata"][0]["n"] ?? 0)>=5){
            echo '[{"error":"Secret Busy"}]';
            return;
        }
        $secret=bin2hex(random_bytes(24));
        if(!Data::InsertData("cms_api_secret",array("secret"=>$secret,"addtime"=>date('Y-m-d H:i:s',$now),"ip"=>$ip,"appid"=>$appid))){
            echo '[{"error":"Secret Failed"}]';
            return;
        }
        echo json_encode(array("secret"=>$secret,"expire"=>300));
    }
}