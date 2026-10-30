<?php
namespace Model\Ut_template;
use usualtool\Lib\Inc;
class Template{
    public function GetTemplate($type=0){
        $template=array();
        if(empty($type) || $type==0){
            $temp=scandir(APP_ROOT."/template/");
            foreach($temp as $v){
              if($v!="index.html"){
                $file=APP_ROOT."/template/".$v;
                if(is_file($file."/usualtool.config")){
                    $temps=file_get_contents($file."/usualtool.config");
                    $temptype=Inc::StrSubstr("<type>","</type>",$temps);
                    if($temptype==2){
                        $tid=Inc::StrSubstr("<id>","</id>",$temps);
                        $title=Inc::StrSubstr("<title>","</title>",$temps);
                        $auther=Inc::StrSubstr("<auther>","</auther>",$temps);
                        $description=Inc::StrSubstr("<description>","</description>",$temps);
                        $template[]=array("tid"=>$tid,"title"=>$title,"auther"=>$auther,"description"=>$description);
                    }
                  }
                }
            }
        }elseif($type==1){
            $temps=Inc::Auth("temp");
            preg_match_all( "/\<temp\>(.*?)\<\/temp\>/s",$temps,$tempblocks);
            foreach($tempblocks[1] as $temp){
                preg_match_all("/\<tid\>(.*?)\<\/tid\>/",$temp,$tid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$temp,$title);
                preg_match_all("/\<picurl\>(.*?)\<\/picurl\>/",$temp,$picurl);
                preg_match_all("/\<module\>(.*?)\<\/module\>/",$temp,$module);
                preg_match_all("/\<lang\>(.*?)\<\/lang\>/",$temp,$lang);
                preg_match_all("/\<isfree\>(.*?)\<\/isfree\>/",$temp,$isfree);
                $template[]=array("tid"=>$tid[1][0],"title"=>$title[1][0],"picurl"=>$picurl[1][0],"module"=>$module[1][0],"lang"=>$lang[1][0],"isfree"=>$isfree[1][0]);
            }
        }elseif($type==2){
            $temps=Inc::Auth("temporder");
            preg_match_all("/\<temp\>(.*?)\<\/temp\>/s",$temps,$tempblocks);
            foreach($tempblocks[1] as $temp){
                preg_match_all("/\<orderid\>(.*?)\<\/orderid\>/",$temp,$orderid);
                preg_match_all("/\<tempid\>(.*?)\<\/tempid\>/",$temp,$tid);
                preg_match_all("/\<title\>(.*?)\<\/title\>/",$temp,$title);
                preg_match_all("/\<ordertime\>(.*?)\<\/ordertime\>/",$temp,$ordertime);
                $template[]=array("orderid"=>$orderid[1][0],"tid"=>$tid[1][0],"title"=>$title[1][0],"ordertime"=>$ordertime[1][0]);
            }
        }
        return $template;
    }
}