<?php
namespace Controller\Ut_api\Admin;
use usualtool\Lib\Data;
class Secret{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $page=empty($_GET["page"]) ? 1 : max(1,intval($_GET["page"]));
        $pagenum=20;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("cms_api_secret","","","id desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        if(!is_array($querydata)){
            $querydata=array();
        }
        $now=time();
        foreach($querydata as $k=>$v){
            $secret=(string)($v["secret"] ?? "");
            $querydata[$k]["secret"]=strlen($secret)>14 ? substr($secret,0,8)."********".substr($secret,-6) : "********";
            $addtime=strtotime((string)($v["addtime"] ?? ""));
            $usetime=(string)($v["usetime"] ?? "");
            $used=$usetime!=="" && strpos($usetime,"0000-00-00")!==0;
            if($used){
                $querydata[$k]["state"]="<span class='text-success'>已使用</span>";
            }elseif(!$addtime || $now-$addtime>300){
                $querydata[$k]["state"]="已过期";
            }else{
                $querydata[$k]["state"]="<span class='text-danger'>待使用</span>";
            }
            $querydata[$k]["appid"]=trim((string)($v["appid"] ?? ""))!=="" ? htmlspecialchars((string)$v["appid"],ENT_QUOTES,"UTF-8") : "-";
            $querydata[$k]["ip"]=trim((string)($v["ip"] ?? ""))!=="" ? $v["ip"] : "-";
            $querydata[$k]["addtime"]=$v["addtime"] ?? "-";
            $querydata[$k]["usetime"]=$used ? $usetime : "-";
        }
        $totalpage=ceil($querynum/$pagenum);
        $pagelink="";
        $this->app->Runin(array("datalist","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
        $this->app->Open("secret.cms");
    }
}