<?php
namespace Model\Ut_plugin;
use usualtool\Lib\Inc;
class Plugin{
    public function GetPlugin($type=0){
        $plugin=array();
        if(empty($type) || $type==0){
            $temp=scandir(APP_ROOT."/plugins/");
            foreach($temp as $v){
              if($v!="index.html"){
                $file=APP_ROOT."/plugins/".$v;
                if(is_file($file."/usualtool.config")){
                    $plugins=file_get_contents($file."/usualtool.config");
                    $plugintype=Inc::StrSubstr("<type>","</type>",$plugins);
                    if($plugintype==2){
                        $pid=Inc::StrSubstr("<id>","</id>",$plugins);
                        $title=Inc::StrSubstr("<title>","</title>",$plugins);
                        $auther=Inc::StrSubstr("<auther>","</auther>",$plugins);
                        $description=Inc::StrSubstr("<description>","</description>",$plugins);
                        $plugin[]=array("pid"=>$pid,"title"=>$title,"auther"=>$auther,"description"=>$description);
                    }
                }
              }
            }
        }elseif($type==1){
            $hooks=Inc::Auth("plugin");
            preg_match_all( "/\<hook\>(.*?)\<\/hook\>/s",$hooks,$hookblocks);
            foreach($hookblocks[1] as $hook){
                preg_match_all("/\<pid\>(.*?)\<\/pid\>/",$hook,$pid);
                preg_match_all("/\<type\>(.*?)\<\/type\>/",$hook,$type);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$hook,$title);
                preg_match_all("/\<picurl\>(.*?)\<\/picurl\>/",$hook,$picurl);
                preg_match_all("/\<ver\>(.*?)\<\/ver\>/",$hook,$ver);
                preg_match_all("/\<isfree\>(.*?)\<\/isfree\>/",$hook,$isfree);
                $plugin[]=array("pid"=>$pid[1][0],"type"=>$type[1][0],"title"=>$title[1][0],"picurl"=>$picurl[1][0],"ver"=>$ver[1][0],"isfree"=>$isfree[1][0]);
            }
        }elseif($type==2){
            $hooks=Inc::Auth("pluginorder");
            preg_match_all("/\<hook\>(.*?)\<\/hook\>/s",$hooks,$hookblocks);
            foreach($hookblocks[1] as $hook){
                preg_match_all("/\<hookid\>(.*?)\<\/hookid\>/",$hook,$hookid);
                preg_match_all("/\<orderid\>(.*?)\<\/orderid\>/",$hook,$orderid);
                preg_match_all("/\<pid\>(.*?)\<\/pid\>/",$hook,$pid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$hook,$title);
                preg_match_all("/\<ordertime\>(.*?)\<\/ordertime\>/",$hook,$ordertime);
                $plugin[]=array("hookid"=>$hookid[1][0],"orderid"=>$orderid[1][0],"pid"=>$pid[1][0],"title"=>$title[1][0],"ordertime"=>$ordertime[1][0]);
            }
        }
        return $plugin;
    }
    public function Filter($data){
        $plugin = array_filter($data, function($item) {
            $pid = (string)($item['pid'] ?? '');
            return substr($pid, 0, 2) !== 'f_';
        });
        $plugin = array_values($plugin);
        return $plugin;
    }
}