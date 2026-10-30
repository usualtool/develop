<?php
namespace Controller\Ut_system\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Route;
class Lang{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        $do=$_GET["do"] ?? "";
        /**
         * 语言数组
         */
        $this->app->Runin("lang",\usualtool\Lib\Lang::GetLang());
        /**
         * 默认语言
         */
        $this->app->Runin("lang_default",$config["LANG"]);
        /**
         * 语言选项
         */
        $this->app->Runin("lang_option",explode(",",$config["LANG_OPTION"]));
        /**
         * 载入模板
         */
        $this->app->Open("lang.cms");
        if($do=="setup"){
            $lang_default = Inc::SqlCheck($_POST["lang_default"] ?? "");
            $lang_option = Inc::SqlCheck(implode(",",$_POST["lang_option"] ?? array()));
            $info = file_get_contents(UTF_ROOT."/.ut.config");
            $info = preg_replace("/LANG=(.*)/","LANG={$lang_default}",$info);
            $info = preg_replace("/LANG_OPTION=(.*)/","LANG_OPTION={$lang_option}",$info);
            file_put_contents(UTF_ROOT."/.ut.config",$info);
            Inc::GoUrl(Route::Link("ut-system","lang"),"保存配置成功!");
        }
    }
}