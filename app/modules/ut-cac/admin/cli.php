<?php
namespace Controller\Ut_cac\Admin;
class Cli{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $o=str_replace("%26","",str_replace("%7C","",str_replace("&","",str_replace("|","",$_POST["o"] ?? ""))));
        if(substr($o,0,2)=="cd" || substr($o,0,3)=="php" || substr($o,0,5)=="nohup" ||substr($o,0,8)=="composer"){
            $results = \usualtool\Lib\Cli::Execute($o);
            echo$results;
        }else{
            echo"Command not supported.";
        }
    }
}