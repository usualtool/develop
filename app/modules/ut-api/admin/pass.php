<?php
namespace Controller\Ut_api\Admin;
use usualtool\Lib\Data;
class Pass{
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
        $data=Data::QueryData("cms_api_log","","","id desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        if(!is_array($querydata)){
            $querydata=array();
        }
        foreach($querydata as $k=>$v){
            $querydata[$k]["act"]=trim((string)($v["act"] ?? ""))!=="" ? $v["act"] : "-";
            $querydata[$k]["utable"]=trim((string)($v["utable"] ?? ""))!=="" ? $v["utable"] : "-";
            $secret=trim((string)($v["usecret"] ?? ""));
            $querydata[$k]["usecret"]=(preg_match('/^[a-f0-9]{48}$/',$secret)) ? substr($secret,0,8)."********".substr($secret,-6) : "-";
            $querydata[$k]["usecretfull"]=htmlspecialchars($secret,ENT_QUOTES,"UTF-8");
            $where=trim((string)($v["uwhere"] ?? ""));
            $querydata[$k]["uwhere"]=$where!=="" ? htmlspecialchars((string)(mb_strlen($where)>60 ? mb_substr($where,0,60)."..." : $where),ENT_QUOTES,"UTF-8") : "-";
            $querydata[$k]["uwherefull"]=htmlspecialchars($where,ENT_QUOTES,"UTF-8");
            $detail=trim((string)($v["udata"] ?? ""));
            $querydata[$k]["udata"]=$detail!=="" ? htmlspecialchars((string)(mb_strlen($detail)>80 ? mb_substr($detail,0,80)."..." : $detail),ENT_QUOTES,"UTF-8") : "-";
            $querydata[$k]["udatafull"]=htmlspecialchars($detail,ENT_QUOTES,"UTF-8");
            $querydata[$k]["ip"]=trim((string)($v["ip"] ?? ""))!=="" ? htmlspecialchars((string)$v["ip"],ENT_QUOTES,"UTF-8") : "-";
            $querydata[$k]["addtime"]=$v["addtime"] ?? "-";
        }
        $totalpage=ceil($querynum/$pagenum);
        $pagelink="";
        $this->app->Runin(array("datalist","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
        $this->app->Open("pass.cms");
    }
}