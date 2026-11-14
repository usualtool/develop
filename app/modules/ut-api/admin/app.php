<?php
namespace Controller\Ut_api\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class App{
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
        if($do=="add"){
            $newappid=trim((string)($_POST["appid"] ?? ""));
            $remark=trim((string)($_POST["remark"] ?? ""));
            if(!preg_match('/^[A-Za-z0-9_\-]{1,100}$/',$newappid)){
                Inc::GoUrl("-1","AppID 格式错误（字母数字_-，100位内）!");
            }
            if(Data::QueryData("cms_api_app","id","appid=?","","1",array($newappid))["querynum"]>0){
                Inc::GoUrl("-1","AppID 已存在!");
            }
            Data::InsertData("cms_api_app",array(
                "appid"=>$newappid,
                "appsecret"=>bin2hex(random_bytes(24)),
                "status"=>1,
                "bindip"=>"",
                "remark"=>$remark,
                "addtime"=>date('Y-m-d H:i:s')
            ));
            Inc::GoUrl(Route::Link("ut-api","app"),"应用密钥添加成功!");
        }
        if($do=="mon"){
            $id=intval($_POST["id"] ?? 0);
            $status=intval($_POST["status"] ?? 1)==1 ? 1 : 0;
            $bindip=trim((string)($_POST["bindip"] ?? ""));
            $remark=trim((string)($_POST["remark"] ?? ""));
            Data::UpdateData("cms_api_app",array(
                "status"=>"?",
                "bindip"=>"?",
                "remark"=>"?"
            ),"id=?",array($status,$bindip,$remark,$id));
            Inc::GoUrl(Route::Link("ut-api","app"),"应用密钥保存成功!");
        }
        if($do=="regen"){
            $id=intval($_GET["id"] ?? 0);
            Data::UpdateData("cms_api_app",array("appsecret"=>"?"),"id=?",array(bin2hex(random_bytes(24)),$id));
            Inc::GoUrl(Route::Link("ut-api","app"),"AppSecret 已重置，请同步更新客户端!");
        }
        if($do=="del"){
            $id=intval($_GET["id"] ?? 0);
            Data::DelData("cms_api_app","id=?",array($id));
            Inc::GoUrl(Route::Link("ut-api","app"),"应用密钥删除成功!");
        }
        $page=empty($_GET["page"]) ? 1 : max(1,intval($_GET["page"]));
        $pagenum=20;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("cms_api_app","","","id desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=is_array($data["querydata"]) ? $data["querydata"] : array();
        foreach($querydata as $k=>$v){
            $querydata[$k]["state"]=intval($v["status"])==1 ? "<span class='text-success'>启用</span>" : "<span class='text-danger'>停用</span>";
            $querydata[$k]["bindip"]=trim((string)($v["bindip"] ?? ""))!=="" ? htmlspecialchars((string)$v["bindip"],ENT_QUOTES,"UTF-8") : "";
            $querydata[$k]["remark"]=htmlspecialchars((string)($v["remark"] ?? ""),ENT_QUOTES,"UTF-8");
        }
        $totalpage=ceil($querynum/$pagenum);
        $pagelink="";
        $this->app->Runin(array("datalist","total","curpage","listnum","pagelink"),array($querydata,$totalpage,$page,$pagenum,$pagelink));
        $this->app->Open("app.cms");
    }
}