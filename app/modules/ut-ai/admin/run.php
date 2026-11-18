<?php
namespace Controller\Ut_ai\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use Model\Ut_ai\Task as TaskModel;
class Run{
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
        if($do==="batch"){
            $limit=intval($_GET["limit"] ?? 10);
            $limit=$limit>100?100:$limit;
            $res=(new TaskModel())->RunReady($limit);
            Inc::GoUrl(Route::Link("ut-ai","task"),"批量执行完成：成功 ".$res["ok"]." 条，失败 ".$res["fail"]." 条");
        }
        $id=intval($_GET["id"] ?? 0);
        if($id<=0){
            Inc::GoUrl(Route::Link("ut-ai","task"),"缺少任务 ID");
        }
        $res=(new TaskModel())->RunOne($id);
        if(!empty($res["ok"])){
            Inc::GoUrl(Route::Link("ut-ai","task"),"任务执行成功");
        }
        Inc::GoUrl(Route::Link("ut-ai","task"),"任务执行失败：".($res["error"]??"未知错误"));
    }
}
