<?php
namespace Controller\Ut_oauth\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
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
        $pagelink="";
        $page=empty($_GET["page"]) ? 1 : $_GET["page"];
        $pagenum=10;
        $minid=$pagenum*($page-1);
        $data=Data::QueryData("oauth_clients","","","id desc","$minid,$pagenum");
        $querynum=$data["querynum"];
        $querydata=$data["querydata"];
        $totalpage=ceil($querynum/$pagenum);
        $this->app->Runin(array("total","curpage","listnum","pagelink","data"),array($totalpage,$page,$pagenum,$pagelink,$querydata));
        $this->app->Open("index.cms");
    }
}