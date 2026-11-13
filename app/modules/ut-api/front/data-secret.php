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
        (new Verify())->Check();
        $now=time();
        $ip=Inc::GetIp();
        $appid=trim((string)($_GET["appid"] ?? ($_POST["appid"] ?? "")));
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