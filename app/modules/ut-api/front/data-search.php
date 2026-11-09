<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use Model\Ut_api\Verify;
class Data_search{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check();
        $keyword=($_GET["keyword"] ?? "");
        if(Data::ModTable("cms_search")){
            $searchdata=$this->SearchData($keyword);
            echo json_encode($searchdata,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }else{
            echo'[{"error":1}]';
        }
    }
    private function SearchData($keyword){
        global $_lang_;
        $lang=isset($_lang_) ? $_lang_ : "";
        $sexist=false;
        if(!empty($keyword) && Data::ModTable("cms_search")){
            $sdata=Data::QueryData("cms_search","id","keyword=?","","",array($keyword));
            $sexist=$sdata["querynum"]>0;
            if($sexist){
                Data::RunYu("update cms_search set hit=hit+1 where keyword=? and lang=?",array($keyword,$lang));
            }
        }
        $sets=Data::QueryData("cms_search_set","id,dbs,fields,wheres,pages")["querydata"];
        foreach($sets as $set){
            $field=trim($set["fields"]);
            $parts=array_filter(array_map("trim",explode(",",$field)),"strlen");
            if(count($parts)<2){
                Data::UpdateData("cms_search_set",array("fields"=>"?"),"id=?",array(rtrim($field,",").",'' as content",intval($set["id"])));
            }
        }
        $param=array();
        $sql="select 'search' as thepage,id,'0' as title,'0' as content from cms_search where id<0";
        $sets=Data::QueryData("cms_search_set","dbs,fields,wheres,pages")["querydata"];
        foreach($sets as $set){
            if(Data::ModTable($set["dbs"])){
                $w=str_replace("[keyword]","?",$set["wheres"]);
                $c=substr_count($w,"?");
                $sql.=" union select '".$set["pages"]."' as thepage,id,".$set["fields"]." from `".$set["dbs"]."` where ".$w;
                for($i=0;$i<$c;$i++){
                    $param[]="%".$keyword."%";
                }
            }
        }
        $res=Data::RunYu($sql,$param);
        $searchnum=$res["curnum"];
        $searchdata=$res["querydata"];
        if(!empty($keyword) && $searchnum>0 && !$sexist){
            Data::InsertData("cms_search",array("lang"=>"?","keyword"=>"?"),array($lang,$keyword));
        }
        return array("searchdata"=>$searchdata,"searchnum"=>$searchnum);
    }
}