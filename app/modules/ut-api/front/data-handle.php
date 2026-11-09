<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use Model\Ut_api\Verify;
class Data_handle{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check();
        $table=($_POST["table"] ?? "");
        $where=$_POST["where"] ?? "";
        if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
            echo'[{"error":"Illegal Where"}]';
            return;
        }
        $action=($_POST["action"] ?? "");
        $param=$_POST["param"] ?? array();
        if(is_string($param)){
            $decoded=json_decode($param,true);
            $param=is_array($decoded) ? array_values($decoded) : (trim($param)==="" ? array() : array($param));
        }
        if(!is_array($param)){
            $param=array($param);
        }
        $param=array_values($param);
        if(($action=="mon" || $action=="del") && substr_count($where,"?")!==count($param)){
            echo'[{"error":"Param Mismatch"}]';
            return;
        }
        $data=array_diff_key($_POST,array("table"=>$table,"where"=>$where,"action"=>$action,"param"=>$param));
        $cols=array();
        if(Data::ModTable($table)){
            $colres=Data::RunYu("select column_name as `Field` from information_schema.columns where table_schema=database() and table_name=?",array($table));
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
                if(Data::InsertData($table,$data,$param)){
                    echo'[{"error":0}]';
                }else{
                    echo'[{"error":1}]';
                }
            }elseif($action=="mon"){
                if(Data::UpdateData($table,$data,$where,$param)){
                    echo'[{"error":0}]';
                }else{
                    echo'[{"error":1}]';
                }
            }elseif($action=="del"){
                if(Data::DelData($table,$where,$param)){
                    echo'[{"error":0}]';
                }else{
                    echo'[{"error":1}]';
                }
            }
        }else{
            echo'[{"error":1}]';
        }
    }
}