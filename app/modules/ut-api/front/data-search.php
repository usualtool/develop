<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Data;
use Model\Ut_api\Verify;
class Data_search{
    private $map=array(
        "article"=>array("cms_article","article"),
        "goods"=>array("cms_goods","pro"),
        "cases"=>array("cms_cases","cases")
    );
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        (new Verify())->Check();
        $keyword=trim($_GET["keyword"] ?? "");
        $mod=$_GET["mod"] ?? "article";
        if($keyword===""){
            echo '[{"error":1}]';
            return;
        }
        if(!isset($this->map[$mod])){
            echo '[{"error":"module not allowed"}]';
            return;
        }
        echo json_encode($this->SearchData($mod,$keyword),JSON_UNESCAPED_UNICODE);
    }
    private function SearchData($mod,$keyword){
        list($table,$page)=$this->map[$mod];
        if(!Data::ModTable($table)){
            return array("searchdata"=>array(),"searchnum"=>0);
        }
        $like="%".$keyword."%";
        $res=Data::QueryData(
            $table,
            "id,title,content,addtime",
            "title like ? or content like ?",
            "addtime desc",
            "0,20",
            array($like,$like)
        );
        $rows=array();
        foreach($res["querydata"] as $row){
            $rows[]=array(
                "thepage"=>"m=".$mod."&p=".$page,
                "id"=>$row["id"],
                "title"=>$row["title"],
                "content"=>$row["content"],
                "addtime"=>$row["addtime"]
            );
        }
        return array("searchdata"=>$rows,"searchnum"=>$res["querynum"]);
    }
}