<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
/**
 * 官方更新
 */
$app->Runin("updatelog",explode("|",Inc::Auth("update")));
/**
 * AD
 */
$app->Runin("ad",explode("^",explode("|",Inc::Auth("upapi"))[1]));
/**
 * 在线更新
 */
$t=Inc::sqlcheck($_GET["t"]);
$i=Inc::sqlcheck(str_replace("..","",$_GET["i"]));
if($t=="update"){
    $url=$config["UPDATEURL"]."/".$i.".zip";
    $save_dir=UTF_ROOT."/update";  
    $filename=basename($url); 
    $res=Inc::SaveFile($url,$save_dir,$filename,1);
    if(!empty($res)){
        $zip=new ZipArchive;
        if($zip->open(UTF_ROOT."/update/".$i.".zip")===TRUE){
            $zip->extractTo(UTF_ROOT."/update/");
            $zip->close();
            if(file_exists(UTF_ROOT."/update/".$i."/usualtool.config")){
                $up=file_get_contents(UTF_ROOT."/update/".$i."/usualtool.config");
                $thesql=Inc::StrSubstr("<sql><![CDATA[","]]></sql>",$up);
                $resx=Data::RunSql($thesql); 
            }else{
                $resx=1;
            }
            if($resx){
                $olddir=UTF_ROOT."/update/".$i."/";
                Inc::movedir($olddir,UTF_ROOT);
                Data::insertData("cms_update",array("updateid"=>$i,"updatetime"=>date('Y-m-d H:i:s',time())));    
                Inc::deldir(UTF_ROOT."/update/".$i."/");
                unlink(UTF_ROOT."/update/".$i.".zip");
                Inc::GoUrl(Route::Link("ut-frame","update"),"Online update complete!");
            }
        }else{
            Inc::GoUrl("-1","Decompression failed!");
        }
    }else{
        Inc::GoUrl("-1","Download failed!");
    }
}
/**
 * 载入模板
 */
$app->Open("update.cms");