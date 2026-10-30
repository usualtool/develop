<?php
namespace Controller\Ut_template\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
class Template_view{
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
        $d=empty($_POST["d"]) ? ($_GET["d"] ?? "") : $_POST["d"];
        $d=urldecode($d);
        $d=Inc::SqlCheck(str_replace("|","/",str_replace("%7C","/",str_replace("_cms",".cms",str_replace("..","",$d)))));
        $html=file_get_contents(APP_ROOT."/modules/".$d);
        $this->app->Runin(array("d","file","html"),array($d,APP_ROOT."/modules/".$d,htmlspecialchars($html)));
        /**
         * 载入模板
         */
        $this->app->Open("template_view.cms");
        if($do=="save"){
            $file=str_replace("..","",$_POST["d"] ?? "");
            $filex=str_replace("/","|",str_replace(".cms","_cms",$_POST["d"] ?? ""));
            $file_path=APP_ROOT."/modules/".$file;
            $content=htmlspecialchars_decode($_POST["content"] ?? "");
            file_put_contents($file_path,$content);
            Inc::GoUrl(Route::Link("ut-template","template_view","d=".$filex),'更新模板成功!');
        }
    }
}