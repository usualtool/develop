<?php
namespace Controller\Ut_api\Front;
use usualtool\Lib\Inc;
use usualtool\Lib\Lang;
use Model\Ut_api\Verify;
class Data_lang{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check();
        $words=Inc::SqlCheck($_GET["word"] ?? "");
        $module=Inc::SqlCheck($_GET["module"] ?? "");
        $lg=empty($_GET["lang"]) ? 1 : Inc::SqlCheck($_GET["lang"]);
        if(!file_exists(OPEN_ROOT."/lang/lg-".$lg.".json")){
            echo json_encode(array(array("error"=>"lang not found")),JSON_UNESCAPED_UNICODE);
            return;
        }
        $word=explode(",",$words);
        for($i=0;$i<count($word);$i++){
            if(!empty($module)){
                setcookie("Language",$lg);
                $thisword[]=array("word"=>Lang::ModLangData($word[$i],$module));
            }else{
                $thisword[]=array("word"=>Lang::LangData($word[$i],$lg));
            }
        }
        echo json_encode($thisword,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}