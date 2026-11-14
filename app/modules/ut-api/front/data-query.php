<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use Model\Ut_api\Verify;
class Data_query{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check("query");
        $table=($_POST["table"] ?? "");
        $field=empty($_POST["field"]) ? "" : ($_POST["field"]);
        $where=empty($_POST["where"]) ? "" : $_POST["where"];
        $limit=empty($_POST["limit"]) ? "" : ($_POST["limit"]);
        $order=empty($_POST["order"]) ? "" : ($_POST["order"]);
        $param=$_POST["param"] ?? array();
        if(is_string($param)){
            $decoded=json_decode($param,true);
            $param=is_array($decoded) ? array_values($decoded) : (trim($param)==="" ? array() : array($param));
        }
        if(!is_array($param)){
            $param=array($param);
        }
        $param=array_values($param);
        if(substr_count($where,"?")!==count($param)){
            Inc::GoUrl('','[{"error":"Param Mismatch"}]');
        }
        $limit=trim($limit);
        if($limit===""){
            $limit="0,20";
        }
        if(preg_match('/^\d+$/',$limit)){
            $limit="0,".$limit;
        }
        if(!preg_match('/^\d+\s*,\s*\d+$/',$limit)){
            Inc::GoUrl('','[{"error":"Limit Error"}]');
        }
        $larr=array_map('intval',preg_split('/\s*,\s*/',$limit));
        if($larr[1]<1 || $larr[1]>100){
            Inc::GoUrl('','[{"error":"Limit Error"}]');
        }
        $limit=$larr[0].",".$larr[1];
        $protect=Verify::ProtectList();
        $field=trim($field);
        if($field===""){
            $cols=Data::RunYu("select column_name as c from information_schema.columns where table_schema=database() and table_name=?",array($table));
            $safe=array();
            foreach(($cols["querydata"] ?? array()) as $c){
                $col=(string)$c["c"];
                if($col!=="password" && !in_array($col,$protect,true)){
                    $safe[]=$col;
                }
            }
            if(empty($safe)){
                Inc::GoUrl('','[{"error":"Protected Field"}]');
            }
            $field=implode(",",$safe);
        }else{
            foreach($protect as $pf){
                if(stripos($field,$pf)!==false){
                    Inc::GoUrl('','[{"error":"Protected Field"}]');
                }
            }
        }
        if(Data::ModTable($table)){
            $data=Data::QueryData(
                $table,
                $field,
                $where,
                $order,
                $limit,
                $param
            );
            $querydata=$data["querydata"];
            echo json_encode($querydata,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }else{
            echo'[{"error":1}]';
        }
    }
}