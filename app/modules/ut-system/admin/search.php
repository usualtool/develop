<?php
namespace Controller\Ut_system\Admin;
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
        $do=$_GET["do"] ?? "";
        $data=Data::QueryData("cms_search_set","","","","")["querydata"];
        $this->app->Runin("data",$data);
        $this->app->Open("search.cms");
        if($do=="update"){
            $ids=implode("-UT-",$_POST["sid"] ?? array());
            $dbs=implode("-UT-",$_POST["sdb"] ?? array());
            $fields=implode("-UT-",$_POST["sfield"] ?? array());
            $wheres=implode("-UT-",$_POST["swhere"] ?? array());
            $pages=implode("-UT-",$_POST["spage"] ?? array());
            $idx=explode("-UT-",$ids);
            $dbx=explode("-UT-",$dbs);
            $fieldx=explode("-UT-",$fields);
            $wherex=explode("-UT-",$wheres);
            $pagex=explode("-UT-",$pages);
            for($s=0;$s<count($dbx);$s++){
                if($idx[$s]=="x"){
                    Data::InsertData("cms_search_set",array(
                        "dbs"=>$dbx[$s],
                        "fields"=>$fieldx[$s],
                        "wheres"=>$wherex[$s],
                        "pages"=>$pagex[$s]));
                }else{
                    Data::UpdateData("cms_search_set",array(
                        "dbs"=>$dbx[$s],
                        "fields"=>$fieldx[$s],
                        "wheres"=>$wherex[$s],
                        "pages"=>$pagex[$s]),"id='".$idx[$s]."'");
                }
            }
            Inc::GoUrl(Route::Link("ut-system","search"),"设置成功!");
        }
        if($do=="del"){
            $id=Inc::SqlCheck($_GET["id"] ?? "");
            if(Data::DelData("cms_search_set","id='$id'")){
                Inc::GoUrl(Route::Link("ut-system","search"),"删除成功!");
            }else{
                Inc::GoUrl(Route::Link("ut-system","search"),"删除失败!");
            }
        }
    }
}