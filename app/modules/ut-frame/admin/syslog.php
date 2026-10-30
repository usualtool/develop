<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Syslog{
    private $app;
    public function __construct(){
        global $app;
        $this->app=$app;
    }
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        function GetLog($path){
            if(!file_exists($path) || !is_readable($path)){
                return [];
            }
            $parsedLogs = [];
            $pattern = '/^\[(?P<time>\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})(?:\s+[^\]]+)?\]\s+(?P<content>.*)$/';
            $handle = fopen($path, 'r');
            if($handle){
                while(($line = fgets($handle)) !== false){
                    $line = rtrim($line, "\r\n");
                    if($line==='') continue;
                    if(preg_match($pattern, $line, $matches)){
                        list($fullLine, $timeStr, $contentStr) = $matches;
                        $level="其他";
                        $lvclass="bg-secondary-lt";
                        if(stripos($contentStr,"Fatal error")!==false){
                            $level="Fatal";
                            $lvclass="bg-red-lt";
                        }elseif(stripos($contentStr,"Parse error")!==false){
                            $level="Parse";
                            $lvclass="bg-orange-lt";
                        }elseif(stripos($contentStr,"Warning")!==false){
                            $level="Warning";
                            $lvclass="bg-yellow-lt";
                        }elseif(stripos($contentStr,"Notice")!==false){
                            $level="Notice";
                            $lvclass="bg-azure-lt";
                        }elseif(stripos($contentStr,"Deprecated")!==false){
                            $level="Deprecated";
                            $lvclass="bg-purple-lt";
                        }
                        $parsedLogs[] = [
                            'time'    => $timeStr,
                            'content' => $contentStr,
                            'level'   => $level,
                            'lvclass' => $lvclass
                        ];
                    }
                }
                fclose($handle);
            }
            return $parsedLogs;
        }
        /**
         * 错误日志
         */
        $logs=GetLog(UTF_ROOT."/log/php_errors.log");
        $logs=array_reverse($logs);
        $pagenum=10;
        $querynum=count($logs);
        $totalpage=ceil($querynum/$pagenum);
        $curpage=empty($_GET["page"]) ? 1 : intval($_GET["page"]);
        if($curpage<1){
            $curpage=1;
        }
        if($totalpage>0 && $curpage>$totalpage){
            $curpage=$totalpage;
        }
        $this->app->Runin(array("log_error","total","curpage","listnum","pagelink","querynum"),array(array_slice($logs,($curpage-1)*$pagenum,$pagenum),$totalpage,$curpage,$pagenum,"",$querynum));
        /**
         * 载入模板
         */
        $this->app->Open("syslog.cms");
    }
}