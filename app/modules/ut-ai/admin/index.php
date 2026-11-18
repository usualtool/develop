<?php
namespace Controller\Ut_ai\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
use usualtool\Lib\Data;
use Model\Ut_ai\Config as ConfigModel;
use Model\Ut_ai\Task as TaskModel;
class Index{
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
        if($do==="save"){
            $cfg=new ConfigModel();
            if($cfg->Save($_POST)){
                (new TaskModel())->EnsureFile();
                Inc::GoUrl(Route::Link("ut-ai"),"配置保存成功");
            }else{
                Inc::GoUrl("-1","配置保存失败");
            }
        }
        $cfg=new ConfigModel();
        $config=$cfg->Get();
        $this->app->Runin(array("title","config"),array("AI 配置",$config));
        $this->app->Open("index.cms");
    }
}