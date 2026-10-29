<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
require'data-verify.php';
$table=Inc::SqlCheck($_POST["table"]);
$where=$_POST["where"] ?? "";
if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
    echo'[{"error":"Illegal Where"}]';
    return;
}
$action=Inc::SqlCheck($_POST["action"]);
$data=array_diff_key($_POST,array("table"=>$table,"where"=>$where,"action"=>$action));
$cols=array();
if(Data::ModTable($table)){
    $colres=Data::JoinQuery("SHOW COLUMNS FROM `".$table."`");
    foreach($colres["querydata"] as $c){
        $cols[$c["Field"]]=true;
    }
}
$data=array_intersect_key($data,$cols);
if($action!="del" && empty($data)){
    echo'[{"error":1}]';
    return;
}
if(Data::ModTable($table)){
    if(empty($action) || $action=="add"){
        if(Data::InsertData($table,$data)){
            echo'[{"error":0}]';
        }else{
            echo'[{"error":1}]';
        }
    }elseif($action=="mon"){
        if(Data::UpdateData($table,$data,$where)){
            echo'[{"error":0}]';
        }else{
            echo'[{"error":1}]';
        }
    }elseif($action=="del"){
        if(Data::DelData($table,$where)){
            echo'[{"error":0}]';
        }else{
            echo'[{"error":1}]';
        }
    }
}else{
    echo'[{"error":1}]';
}