<?php
namespace Controller\Ut_data\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
class Backup{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        /**
         * 连接数据库
         */
        $db=Data::GetDatabase();
        /**
         * 传递参数过程
         */
        $do=Inc::SqlCheck($_GET["do"] ?? "");
        /**
         * 封装备份数据方法
         */
        function backup(){
            $data=array();
            $fileArr = array();
            $backupdir = UTF_ROOT."/log/sql";
            if(!is_dir($backupdir)){
                return [];
            }else{
            $handle = opendir($backupdir);
            while(($file=readdir($handle))!=false){
                if($file !== '.' && $file != '..'){
                    $filedate = filemtime($backupdir."/".$file."");
                    $fileArr[$file] = $filedate;
                }
            }
            arsort($fileArr);
            $numberOfFiles = sizeOf($fileArr);
            for($i=0;$i<$numberOfFiles;$i++){
                $thisFile = Inc::NewEach($fileArr);
                $thisName = $thisFile[0];
                $data[]=array(
                    "name"=>$thisName,
                    "size"=>Inc::ForBytes(filesize($backupdir."/".$thisName."")),
                    "time"=>date('Y-m-d H:i:s',filemtime($backupdir."/".$thisName.""))
                );
            }
            closedir($handle);
            return $data;
            }
        }
        $this->app->Runin(
            array("data","dbtype"),
            array(backup(),Data::GetDb())
        );
        /**
         * 载入模板
         */
        $this->app->Open("backup.cms");
        /**
         * SQL查询
         */
        if($do=="sql-backup"){
            $to_file_name = UTF_ROOT."/log/sql/".Inc::GetRandomString(16).".sql";
            if(!is_dir(UTF_ROOT."/log/sql/")){
                Inc::MakeDir(UTF_ROOT."/log/sql/");
            }
            $tables = mysqli_query($db,"show tables");
            $tabList = array();
            while($row = mysqli_fetch_row($tables)){
                $tabList[] = $row[0];
            }
            $info = "-- ----------------------------\r\n";
            $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
            $info .= "-- UT在线备份尚不适合处理超大量数据,备份完成后请对比一下!\r\n";
            $info .= "-- ----------------------------\r\n\r\n";
            file_put_contents($to_file_name,$info,FILE_APPEND);
            foreach($tabList as $val){
                $sql = "show create table ".$val;
                $res = mysqli_query($db,$sql);
                $row = mysqli_fetch_array($res);
                $info = "-- ----------------------------\r\n";
                $info .= "-- Table structure for `".$val."`\r\n";
                $info .= "-- ----------------------------\r\n";
                $info .= "DROP TABLE IF EXISTS `".$val."`;\r\n";
                $sqlStr = $info.$row[1].";\r\n\r\n";
                file_put_contents($to_file_name,$sqlStr,FILE_APPEND);
                mysqli_free_result($res);
            }
            foreach($tabList as $val){
                $sql = "select * from ".$val;
                $res = mysqli_query($db,$sql);
                if(mysqli_num_rows($res)<1) continue;
                $info = "-- ----------------------------\r\n";
                $info .= "-- Records for `".$val."`\r\n";
                $info .= "-- ----------------------------\r\n";
                file_put_contents($to_file_name,$info,FILE_APPEND);
                while($row = mysqli_fetch_row($res)){
                    $sqlStr = "INSERT INTO `".$val."` VALUES (";
                    foreach($row as $zd){
                        $sqlStr .= isset($zd) ? "'".$db->real_escape_string($zd)."', " : "NULL, ";
                    }
                    $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                    $sqlStr .= ")|UTSQL|\r\n";
                    file_put_contents($to_file_name,$sqlStr,FILE_APPEND);
                }
                mysqli_free_result($res);
                file_put_contents($to_file_name,"\r\n",FILE_APPEND);
            }
            Inc::GoUrl(Route::Link("ut-data","backup"),"SQL备份成功!");
        }
        /**
         * SQL文件还原
         */
        if($do=="sql-rev"){
            $sqlname=basename($_GET['sql'] ?? "");
            if(!preg_match('/^[\w\-]+\.sql$/',$sqlname)):
                Inc::GoUrl(Route::Link("ut-data","backup"),"SQL文件名非法!");
            endif;
            $sqlfile=UTF_ROOT."/log/sql/".$sqlname;
            $revdir=realpath(UTF_ROOT."/log/sql");
            $realpath=realpath($sqlfile);
            if($revdir===false || $realpath===false || strpos($realpath,$revdir.DIRECTORY_SEPARATOR)!==0):
                Inc::GoUrl(Route::Link("ut-data","backup"),"SQL文件不存在!");
            endif;
            $sql=file_get_contents($sqlfile);
            $arr=explode('|UTSQL|', $sql);
            mysqli_report(\MYSQLI_REPORT_OFF);
            foreach ($arr as $value){
                $value=trim($value);
                if($value===""){
                    continue;
                }
                $db->multi_query($value.';');
                while(mysqli_more_results($db)){
                    $db->next_result();
                    $res=$db->store_result();
                    if($res){
                        $res->free();
                    }
                }
                if($db->errno){
                    Inc::GoUrl(Route::Link("ut-data","backup"),"SQL还原中断: ".$db->error);
                }
            }
            Inc::GoUrl(Route::Link("ut-data","backup"),"SQL还原执行成功!");
        }
        /**
         * SQL文件删除
         */
        if($do=="sql-del"){
            $sql=basename($_GET['sql'] ?? "");
            $sqlbak=UTF_ROOT."/log/sql/".$sql;
                if(file_exists($sqlbak)):
                    Inc::UnlinkFile($sqlbak);
                    Inc::GoUrl(Route::Link("ut-data","backup"),"SQL文件删除成功!");
                else:
                    Inc::GoUrl("-1","SQL文件删除失败!");
                endif;
        }
    }
}