<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Search{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        header('content-type:application/json;charset=utf8');
        $key=$_POST["key"] ?? "";
        $data=Data::QueryData("cms_module","mid,modname,modurl","mid<>'ut-frame' and (mid like ? or modname like ? or backitem like ?)","","",array("%".$key."%","%".$key."%","%".$key."%"))["querydata"];
        echo json_encode($data);
    }
}