<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
$u=preg_replace('/[^A-Za-z0-9_\-\/\.]/','',$_GET["u"] ?? "");
$app->Open("lock.cms");
if($_GET["do"]=="login"){
    $password=Inc::SqlCheck($_POST["password"]);
    $data=Data::QueryData("cms_admin","","id='".$_SESSION['admin_id']."'","","");
    if($data["querynum"]==1){
        $rows=$data["querydata"][0];
        if(password_verify($password,$rows['password'])){
            setcookie("Lock",0);
            $param=str_replace("____","?",$u);
            $param=str_replace("---","/",$param);
            $param=str_replace("___","&",$param);
            $param=str_replace("__","=",$param);
            if($param=="" || preg_match("#^(https?:|//)#i",$param)){
                $param=Route::Link("ut-frame","index");
            }
            Inc::GoUrl($param,"解锁成功!");
        }else{
            Inc::GoUrl("-1","解锁失败!");
        }
    }
}