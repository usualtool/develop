<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
/**
 * 传递参数过程
 */
$do=Inc::SqlCheck($_GET["do"]);
/**
 * 载入模板
 */
$app->Open("sql.cms");
/**
 * SQL查询
 */
if($do=="sql"){
    $sql=$_POST['sql'];
    if(!empty($sql)){
        $res=Data::RunSql($sql);
        if($res){
            Inc::GoUrl(Route::Link("ut-data","sql"),"SQL执行成功!");
        }else{
            Inc::GoUrl("-1","SQL执行失败!");
        }
    }else{
        Inc::GoUrl("-1","SQL执行失败!关键语句为空!");
    }
}