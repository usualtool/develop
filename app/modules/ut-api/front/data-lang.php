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
        global $config,$_lang_;
        (new Verify())->Check();
        $words=($_GET["word"] ?? "");
        $module=($_GET["module"] ?? "");
        $lg=empty($_GET["lang"]) ? 1 : ($_GET["lang"]);
        if(!file_exists(OPEN_ROOT."/lang/lg-".$lg.".json")){
            echo json_encode(array(array("error"=>"lang not found")),JSON_UNESCAPED_UNICODE);
            return;
        }
        $word=explode(",",$words);
        $activelang=isset($_lang_) ? $_lang_ : "zh";
        for($i=0;$i<count($word);$i++){
            if(!empty($module)){
                if(!is_file(APP_ROOT."/modules/".$module."/lang/lg-".$activelang.".json")){
                    $thisword[]=array("word"=>$word[$i]);
                    continue;
                }
                setcookie("Language",$lg);
                $thisword[]=array("word"=>Lang::ModLangData($word[$i],$module));
            }else{
                $thisword[]=array("word"=>Lang::LangData($word[$i],$lg));
            }
        }
        echo json_encode($thisword,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}