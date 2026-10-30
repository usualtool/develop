<?php
namespace Controller\Ut_frame\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Search{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $keyword=empty($_GET["keyword"]) ? Inc::SqlCheck($_POST["keyword"] ?? "") : Inc::SqlCheck($_GET["keyword"]);
        $page=empty($_GET["page"]) ? 1 : intval($_GET["page"]);
        $pagenum=5;
        $searchdata=array();
        $searchnum=0;
        $totalpage=0;
        $pagelink="?";
        if(!empty($keyword)){
            try{
                $data=Data::SearchData($keyword);
            }catch(\Throwable $e){
                $sets=Data::QueryData("cms_search_set","id,fields","","","","0")["querydata"];
                foreach($sets as $set){
                    $field=trim($set["fields"]);
                    $parts=array_filter(array_map("trim",explode(",",$field)),"strlen");
                    if(count($parts)<2){
                        Data::UpdateData("cms_search_set",array("fields"=>rtrim($field,",").",'' as content"),"id=".intval($set["id"]));
                    }
                }
                try{
                    $data=Data::SearchData($keyword);
                }catch(\Throwable $e2){
                    $data=array("searchdata"=>array(),"searchnum"=>0);
                }
            }
            $searchnum=$data["searchnum"];
            $totalpage=$searchnum>0 ? (int)ceil($searchnum/$pagenum) : 0;
            if($page<1){$page=1;}
            if($totalpage>0 && $page>$totalpage){$page=$totalpage;}
            $searchdata=array_slice($data["searchdata"],$pagenum*($page-1),$pagenum);
            $pagelink=Route::Link("ut-frame","search","keyword=".urlencode($keyword));
        }
        $this->app->Runin(
            array("webplace","keyword","searchdata","searchnum","total","curpage","listnum","pagelink"),
            array("搜索",$keyword,$searchdata,$searchnum,$totalpage,$page,$pagenum,$pagelink)
        );
        $this->app->Open("search.cms");
    }
}