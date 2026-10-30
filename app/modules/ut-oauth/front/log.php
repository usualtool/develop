<?php
namespace Controller\Ut_oauth\Front;
use usualtool\Lib\Inc;
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
        require_once dirname(__FILE__).'/'.'session.php';
        $pagelink="";
        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("oauth_access_tokens","","user_id='$oauth_uid'","id desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $this->app->Runin(array("total","curpage","listnum","pagelink","data"),array($totalpage,$page,$pagenum,$pagelink,$querydata));
        $this->app->Open("log.cms");
    }
}