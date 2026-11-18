<?php
namespace Controller\Ut_ai\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Data;
use Model\Ut_ai\Log as LogModel;
class Log{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $id=intval($_GET["id"] ?? 0);
        if($id>0){
            $row=(new LogModel())->GetOne($id);
            if(!$row){
                Inc::GoUrl(Route::Link("ut-ai","log"),"日志不存在");
            }
            $row["result_html"]=htmlspecialchars((string)($row["result"]??""),ENT_QUOTES,"UTF-8");
            $row["reasoning_html"]=htmlspecialchars((string)($row["reasoning"]??""),ENT_QUOTES,"UTF-8");
            $row["messages_html"]=htmlspecialchars((string)($row["messages"]??""),ENT_QUOTES,"UTF-8");
            $row["is_image"]=preg_match('#^https?://.+\.(png|jpe?g|webp|gif)(\?.*)?$#i',(string)($row["result"]??""))===1;
            $this->app->Runin(array("title","log"),array("执行日志 #".$id,$row));
            $this->app->Open("log_detail.cms");
            return;
        }
        $taskId=intval($_GET["task_id"] ?? 0);
        $curpage=intval($_GET["page"] ?? 1);
        $listnum=20;
        $offset=($curpage-1)*$listnum;
        $log=new LogModel();
        if($taskId>0){
            $total=intval(Data::QueryData("cms_ai_log","id","task_id=?","","0,1",array($taskId))["querynum"] ?? 0);
            $list=$log->GetByTask($taskId,$offset.",".$listnum);
            $title="任务 #".$taskId." 的执行日志";
        }else{
            $total=intval(Data::RunYu("SELECT COUNT(*) AS c FROM cms_ai_log")[0]["c"] ?? 0);
            $list=$log->GetList($offset.",".$listnum);
            $title="AI 执行日志";
        }
        $this->app->Runin(array("title","logs","total","curpage","listnum","pagelink","task_id"),
            array($title,$this->Prepare($list["querydata"]),$total,$curpage,$listnum,"?",$taskId));
        $this->app->Open("log.cms");
    }
    private function Prepare($rows){
        $data=is_array($rows)?$rows:array();
        foreach($data as $k=>$row){
            $data[$k]["result_brief"]=mb_substr((string)($row["result"]??""),0,100);
        }
        return $data;
    }
}
