<?php
namespace Controller\Ut_power\Admin;
use usualtool\Lib\Data;
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
        /**
         * 获取数据
         */
        $pagelink="";
        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("cms_admin_log","","","logintime desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        /**
         * 写入数据
         */
        $this->app->Runin("datalist",$querydata);
        $this->app->Runin(array("total","curpage","listnum","pagelink"),array($totalpage,$page,$pagenum,$pagelink));
        /**
         * 载入模板
         */
        $this->app->Open("log.cms");
    }
}