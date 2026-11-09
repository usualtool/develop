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
        (new Verify())->Check();
        $table=($_POST["table"] ?? "");
        $field=empty($_POST["field"]) ? "" : ($_POST["field"]);
        $where=empty($_POST["where"]) ? "" : $_POST["where"];
        if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
            Inc::GoUrl('','[{"error":"Illegal Where"}]');
        }
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