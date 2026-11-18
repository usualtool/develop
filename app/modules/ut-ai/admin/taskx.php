<?php
namespace Controller\Ut_ai\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Data;
use Model\Ut_ai\Task as TaskModel;
use Model\Ut_ai\Config as ConfigModel;
class Taskx{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $id=intval($_POST["id"] ?? ($_GET["id"] ?? 0));
        $task=new TaskModel();
        $back=Route::Link("ut-ai","taskx",$id>0?"id=".$id:"");
        if(($_GET["do"] ?? "")==="save"){
            $task_type=strtolower(trim((string)($_POST["task_type"] ?? "text")));
            if($task_type!=="image"){
                $task_type="text";
            }
            $write_db=intval($_POST["write_db"] ?? 0)===1?1:0;
            $is_cron=intval($_POST["is_cron"] ?? 0)===1?1:0;
            $data=array(
                "title"=>trim((string)($_POST["title"] ?? "")),
                "model"=>trim((string)($_POST["model"] ?? "")),
                "task_type"=>$task_type,
                "write_db"=>$write_db,
                "target_table"=>$write_db===1?trim((string)($_POST["target_table"] ?? "")):"",
                "image_count"=>max(1,min(10,intval($_POST["image_count"] ?? 1))),
                "is_cron"=>$is_cron,
                "cron_time"=>$is_cron===1?trim((string)($_POST["cron_time"] ?? "")):"",
                "prompt"=>trim((string)($_POST["prompt"] ?? "")),
                "context"=>trim((string)($_POST["context"] ?? "")),
                "options"=>trim((string)($_POST["options"] ?? "")),
                "state"=>intval($_POST["state"] ?? 1)
            );
            if($data["prompt"]===""){
                Inc::GoUrl($back,"提示词不能为空");
            }
            if($data["is_cron"]===1 && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$data["cron_time"])){
                Inc::GoUrl($back,"定时循环任务必须填写执行时间（HH:MM）");
            }
            if($data["write_db"]===1 && $data["target_table"]===""){
                Inc::GoUrl($back,"勾选写表时必须选择目标表");
            }
            if($data["write_db"]===1 && $task_type==="image"){
                $writer=new \Model\Ut_api\Writer();
                if(empty($writer->ImageFields($data["target_table"]))){
                    Inc::GoUrl($back,"目标表没有可写入的图片字段，无法保存生图写表任务");
                }
            }
            if($data["title"]===""){
                $data["title"]=mb_substr($data["prompt"],0,30);
            }
            if($data["options"]!=="" && !json_decode($data["options"])){
                Inc::GoUrl($back,"Options 不是合法的 JSON");
            }
            if($data["state"]<0 || $data["state"]>4){
                $data["state"]=0;
            }
            if($id>0){
                $task->Update($id,$data);
                Inc::GoUrl(Route::Link("ut-ai","task"),"任务已保存");
            }
            $newid=$task->Create($data);
            Inc::GoUrl(Route::Link("ut-ai","task"),$newid?"任务创建成功":"任务创建失败");
        }
        if($id>0){
            $row=$task->GetOne($id);
            if(!$row){
                Inc::GoUrl(Route::Link("ut-ai","task"),"任务不存在");
            }
            $pagetitle="编辑 AI 任务";
        }else{
            $row=array(
                "id"=>0,"title"=>"","model"=>"","task_type"=>"text","write_db"=>0,"target_table"=>"","image_count"=>1,"is_cron"=>0,"cron_time"=>"","prompt"=>"","context"=>"","options"=>"","state"=>1
            );
            $pagetitle="创建 AI 任务";
        }
        $cfg=(new ConfigModel())->Get();
        $models=$cfg["allowed_models"];
        $imgModel=trim((string)($cfg["image_model"]??""));
        if($imgModel!=="" && !in_array($imgModel,$models,true)){
            $models[]=$imgModel;
        }
        if($row["model"]!=="" && !in_array($row["model"],$models,true)){
            array_unshift($models,$row["model"]);
        }
        $api=Data::QueryData("cms_api","opentable","","","1")["querydata"][0]??array();
        $tables=array_values(array_filter(array_map("trim",explode(",",(string)($api["opentable"]??""))),'strlen'));
        $exist=array();
        foreach($tables as $t){
            if(Data::ModTable($t)){
                $exist[]=$t;
            }
        }
        $this->app->Runin(array("title","task","allowed_models","open_tables"),array($pagetitle,$row,$models,$exist));
        $this->app->Open("taskx.cms");
    }
}
