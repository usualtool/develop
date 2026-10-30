<?php
namespace Model\Ut_module;
use usualtool\Lib\Inc;
class Module{
    public function GetModule($type=0){
        $mod=array();
        if(empty($type) || $type==0){
            $temp=scandir(MODULE_PATH."/");
            foreach($temp as $v){
                if($v!="index.html"){
                    $file=MODULE_PATH."/".$v;
                    if(is_file($file."/usualtool.config")){
                        $mods=file_get_contents($file."/usualtool.config");
                        if(Inc::StrSubstr("<type>","</type>",$mods)==2){
                            $mod[]=array(
                                "mid"=>Inc::StrSubstr("<id>","</id>",$mods),
                                "catid"=>Inc::StrSubstr("<itemid>","</itemid>",$mods),
                                "title"=>Inc::StrSubstr("<title>","</title>",$mods),
                                "auther"=>Inc::StrSubstr("<auther>","</auther>",$mods),
                                "url"=>Inc::StrSubstr("<starturl>","</starturl>",$mods));
                        }
                    }
                }
            }
        }elseif($type==1){
            $modules=Inc::Auth("module");
            preg_match_all("/\<module\>(.*?)\<\/module\>/s",$modules,$moduleblocks);
            foreach($moduleblocks[1] as $module){
                preg_match_all("/\<mid\>(.*?)\<\/mid\>/",$module,$mid);
                preg_match_all("/\<catid\>(.*?)\<\/catid\>/",$module,$catid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$module,$title);
                preg_match_all("/\<isfree\>(.*?)\<\/isfree\>/",$module,$isfree);
                $mod[]=array("mid"=>$mid[1][0],"catid"=>$catid[1][0],"title"=>$title[1][0],"isfree"=>$isfree[1][0]);
            }
        }elseif($type==2){
            $modules=Inc::Auth("moduleorder");
            preg_match_all("/\<module\>(.*?)\<\/module\>/s",$modules,$moduleblocks);
            foreach($moduleblocks[1] as $module){
                preg_match_all("/\<moduleid\>(.*?)\<\/moduleid\>/",$module,$moduleid);
                preg_match_all("/\<orderid\>(.*?)\<\/orderid\>/",$module,$orderid);
                preg_match_all("/\<mid\>(.*?)\<\/mid\>/",$module,$mid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$module,$title);
                preg_match_all("/\<ordertime\>(.*?)\<\/ordertime\>/",$module,$ordertime);
                $mod[]=array("moduleid"=>$moduleid[1][0],"orderid"=>$orderid[1][0],"mid"=>$mid[1][0],"title"=>$title[1][0],"ordertime"=>$ordertime[1][0]);
            }
        }
        return $mod;
    }
}