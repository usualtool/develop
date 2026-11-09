<?php
namespace Controller\Ut_oauth\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Role{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $list=Data::RunYu("select table_name as tablename from information_schema.tables where table_schema=database()");
        $table=array_column($list['querydata'],'tablename');
        $data=Data::QueryData("oauth_scopes","","")["querydata"];
        $this->app->Runin(
            array("table","data"),
            array($table,$data)
        );
        $this->app->Open("role.cms");
        if(($_GET["do"] ?? "")=="creat"){
            $title=($_POST["title"] ?? "");
            $scope=($_POST["scope"] ?? "");
            $dbtable=($_POST["dbtable"] ?? "");
            if(empty($title) || empty($scope)){
                Inc::GoUrl("-1","必填项不能为空");
            }
            if(Data::QueryData("oauth_scopes","","title=? or scope=? or dbtable=?","","",array($title,$scope,$dbtable))["querynum"]>0){
                Inc::GoUrl("-1","中英文权限名称或数据表已使用过");
            }else{
                if(Data::InsertData("oauth_scopes",array(
                    "title"=>"?",
                    "scope"=>"?",
                    "dbtable"=>"?"),array($title,$scope,$dbtable)
                )){
                    Inc::GoUrl(Route::Link("ut-oauth","role"),"新增成功");
                }else{
                    Inc::GoUrl("-1","新增失败");
                }
            }
        }
    }
}