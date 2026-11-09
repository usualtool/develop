<?php
namespace Controller\Ut_data\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
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
        /**
         * 传递参数过程
         */
        $data=array();
        $result=Data::RunYu("select table_name as tbname from information_schema.tables where table_schema=database()");
        foreach($result["querydata"] as $arr){
            foreach($arr as $key=>$val){
                if($key=="_n_"){
                    continue;
                }
                $rows='';
                $type='';
                $null='';
                $key='';
                $def='';
                $extra='';
                $res=Data::RunYu("select column_name as `Field`,column_type as `Type`,is_nullable as `Null`,column_key as `Key`,column_default as `Default`,extra as `Extra` from information_schema.columns where table_schema=database() and table_name=?",array($val));
                foreach($res["querydata"] as $row){
                    $rows.=$row['Field'].",";
                    $type.=$row['Type'].",";
                    $null.=$row['Null'].",";
                    $key.=$row['Key'].",";
                    $def.=$row['Default'].",";
                    $extra.=$row['Extra'].",";
                }
                $cols=rtrim($rows,",");
                $types=rtrim($type,",");
                /**$types=str_replace(")","-T-",str_replace("(","-U-",rtrim($type,",")));*/
                $nulls=rtrim($null,",");
                $keys=rtrim($key,",");
                $defs=rtrim($def,",");
                $extras=rtrim($extra,",");
                $datanum=Data::QueryNum("select * from `".$val."`");
                $data[]=array(
                    "name"=>$val,
                    "field"=>$cols,
                    "type"=>$types,
                    "null"=>$nulls,
                    "key"=>$keys,
                    "def"=>$defs,
                    "extra"=>$extras,
                    "num"=>$datanum);
            }
        }
        $this->app->Runin("data",$data);
        /**
         * 载入模板
         */
        $this->app->Open("index.cms");
    }
}