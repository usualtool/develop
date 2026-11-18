<?php
namespace Controller\Ut_ai\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Data;
use Model\Ut_ai\Task as TaskModel;
use Model\Ut_ai\Config as ConfigModel;
class Task{
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
        if($do==="creat" || $do==="create"){
            $task=new TaskModel();
            if($task->Create($_POST)){
                Inc::GoUrl(Route::Link("ut-ai","task"),"任务创建成功");
            }else{
                Inc::GoUrl("-1","任务创建失败");
            }
        }
        if($do==="del"){
            $id=intval($_GET["id"] ?? 0);
            if($id>0){
                (new TaskModel())->Delete($id);
            }
            Inc::GoUrl(Route::Link("ut-ai","task"),"已删除");
        }
        $curpage=intval($_GET["page"] ?? 1);
        $listnum=20;
        $total=intval(Data::RunYu("SELECT COUNT(*) AS c FROM cms_ai_task")[0]["c"] ?? 0);
        $offset=($curpage-1)*$listnum;
        $list=(new TaskModel())->GetList($offset.",".$listnum);
        $cfg=(new ConfigModel())->Get();
        $this->app->Runin(array("title","tasks","total","curpage","listnum","pagelink","allowed_models"),
            array("AI 任务",$this->Prepare($list["querydata"]),$total,$curpage,$listnum,"?",$cfg["allowed_models"]));
        $this->app->Open("task.cms");
    }
    private function Prepare($rows){
        $labels=array(0=>"草稿",1=>"就绪",2=>"执行中",3=>"成功",4=>"失败");
        $data=is_array($rows)?$rows:array();
        foreach($data as $k=>$row){
            $state=intval($row["state"]??0);
            $data[$k]["state_text"]=isset($labels[$state])?$labels[$state]:"未知";
        }
        return $data;
    }
}