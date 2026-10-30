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
        $table=Inc::SqlCheck($_POST["table"] ?? "");
        $field=empty($_POST["field"]) ? "" : Inc::SqlCheck($_POST["field"]);
        $where=empty($_POST["where"]) ? "" : $_POST["where"];
        if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
            Inc::GoUrl('','[{"error":"Illegal Where"}]');
        }
        $limit=empty($_POST["limit"]) ? "" : Inc::SqlCheck($_POST["limit"]);
        $order=empty($_POST["order"]) ? "" : Inc::SqlCheck($_POST["order"]);
        $lg=empty($_POST["lang"]) ? 0 : Inc::SqlCheck($_POST["lang"]);
        if(Data::ModTable($table)){
            $data=Data::QueryData(
                $table,
                $field,
                $where,
                $order,
                $limit,
                $lg
            );
            $querydata=$data["querydata"];
            echo json_encode($querydata,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }else{
            echo'[{"error":1}]';
        }
    }
}