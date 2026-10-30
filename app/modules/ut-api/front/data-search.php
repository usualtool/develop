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
        $keyword=Inc::SqlCheck($_GET["keyword"] ?? "");
        if(Data::ModTable("cms_search")){
            $searchdata=Data::SearchData($keyword);
            echo json_encode($searchdata,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        }else{
            echo'[{"error":1}]';
        }
    }
}