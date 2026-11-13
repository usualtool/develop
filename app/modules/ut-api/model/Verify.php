<?php
namespace Model\Ut_api;
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
class Verify{
    public function Check($act=""){
        global $config;
        header('Content-Type: application/json;charset=utf-8');
        $headers=$_SERVER;
        $origin=isset($headers['HTTP_ORIGIN']) ? $headers['HTTP_ORIGIN'] : '';
        $referer=$headers["HTTP_REFERER"] ?? "";
        $api=Data::QueryData("cms_api","","","","1")["querydata"][0];
        $white=explode(",",$api["white"]);
        $opentable=explode(",",$api["opentable"]);
        if(Inc::Contain(parse_url($origin,PHP_URL_HOST),$white)){
            header('Access-Control-Allow-Origin: '.$origin);
        }
        if(!Inc::Contain($config["APPURL"],$referer) && !Inc::Contain(parse_url($referer,PHP_URL_HOST),$white)){
            Inc::GoUrl('','[{"error":"Origin Error"}]');
        }
        if(!isset($headers["HTTP_TOKEN"])){
            Inc::GoUrl('','[{"error":"Token Empty"}]');
        }
        if(!isset($headers["HTTP_TIME"])){
            Inc::GoUrl('','[{"error":"Time Empty"}]');
        }
        $token=$headers["HTTP_TOKEN"];
        $time=$headers["HTTP_TIME"];
        $t=preg_match('/^\d{9,11}$/',trim($time)) ? (int)trim($time) : strtotime(trim($time));
        if($t===false || $t<=0 || abs(time()-$t)>300){
            Inc::GoUrl('','[{"error":"Time Error"}]');
        }
        $vtoken=md5(md5($config["UTCODE"]).$t);
        if($token!=$vtoken){
            Inc::GoUrl('','[{"error":"Token Error"}]');
        }
        $consume="";
        $table="";
        $action=($_POST["action"] ?? "");
        if($action=="add" || $action=="mon" || $action=="del"){
            if(!isset($headers["HTTP_SECRET"])){
                Inc::GoUrl('','[{"error":"Secret Empty"}]');
            }
            $secret=trim($headers["HTTP_SECRET"]);
            if(!preg_match('/^[a-f0-9]{48}$/',$secret)){
                Inc::GoUrl('','[{"error":"Secret Error"}]');
            }
            $data=Data::QueryData("cms_api_secret","id,addtime,usetime","secret=?","","1",array($secret));
            if($data["querynum"]<=0){
                Inc::GoUrl('','[{"error":"Secret Error"}]');
            }
            $row=$data["querydata"][0];
            if(!empty($row["usetime"])){
                Inc::GoUrl('','[{"error":"Secret Used"}]');
            }
            if(strtotime($row["addtime"])<time()-300){
                Inc::GoUrl('','[{"error":"Secret Expired"}]');
            }
            $consume=$secret;
        }
        if(!empty($_POST["table"]) || !empty($_GET["table"])){
            $table=empty($_GET["table"]) ? ($_POST["table"]) : ($_GET["table"]);
            if(!in_array($table,$opentable)){
                Inc::GoUrl('','[{"error":"Not Open Table"}]');
            }
            if(in_array($table,array("cms_admin"))){
                Inc::GoUrl('','[{"error":"Protected Table"}]');
            }
            $fields=isset($_POST["field"]) ? $_POST["field"] : (isset($_GET["field"]) ? $_GET["field"] : "");
            if(stripos($fields,"password")!==false){
                Inc::GoUrl('','[{"error":"Protected Field"}]');
            }
        }
        $where=(string)($_POST["where"] ?? ($_GET["where"] ?? ""));
        if($where!==""){
            if(($action=="mon" || $action=="del") && substr_count($where,"?")<1){
                Inc::GoUrl('','[{"error":"Illegal Where"}]');
            }
            if(self::CheckPart($where)){
                Inc::GoUrl('','[{"error":"Illegal Where"}]');
            }
        }
        if($consume!==""){
            Data::RunYu("update `cms_api_secret` set usetime=? where secret=? and usetime is null and addtime>=?",array(date('Y-m-d H:i:s'),$consume,date('Y-m-d H:i:s',time()-300)));
            $rc=Data::RunYu("select row_count() as n",array());
            if((int)($rc["querydata"][0]["n"] ?? 0)!==1){
                Inc::GoUrl('','[{"error":"Secret Used"}]');
            }
        }
        self::Log($act,$table,$where,$consume);
    }
    private static function Log($act,$table,$where,$secret=""){
        if(!in_array($act,array("query","add","mon","del")) || trim((string)$table)===""){
            return;
        }
        $param=$_POST["param"] ?? array();
        if(is_string($param)){
            $decoded=json_decode($param,true);
            $param=is_array($decoded) ? $decoded : $param;
        }
        $keep=array("table","action","where","param");
        $data=array();
        foreach($_POST as $k=>$v){
            if(!in_array($k,$keep) && !is_array($v)){
                $data[$k]=$v;
            }
        }
        $detail=json_encode(array("param"=>$param,"data"=>$data),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if(strlen((string)$detail)>2000){
            $detail=substr((string)$detail,0,2000);
        }
        Data::InsertData("cms_api_log",array(
            "act"=>$act,
            "utable"=>(string)$table,
            "uwhere"=>(string)$where,
            "udata"=>(string)$detail,
            "usecret"=>(string)$secret,
            "ip"=>Inc::GetIp(),
            "addtime"=>date('Y-m-d H:i:s')
        ));
    }
    public static function CheckPart($where){
        if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
            return true;
        }
        if(preg_match('/(?:^|[^\w.])\d+\s*=\s*\d+|[\'"]|\b(?:true|false)\b|\bor\s+(?:\?|\d+)/i',$where)){
            return true;
        }
        if(!preg_match('/\b(?:or|xor)\b|\|\|/i',$where)){
            return false;
        }
        $seg=preg_split('/\b(?:and|or|xor)\b|\|\||&&/i',$where);
        foreach($seg as $s){
            $s=trim(str_replace(array('(',')'),' ',$s));
            if($s==="" || strpos($s,"?")!==false){
                continue;
            }
            if(preg_match('/^[A-Za-z_][A-Za-z0-9_.`]*\s*(?:=|<>|!=|>=|<=|>|<)\s*\d+$/',$s)){
                continue;
            }
            if(preg_match('/^[A-Za-z_][A-Za-z0-9_.`]*\s+is\s+null$/i',$s)){
                continue;
            }
            return true;
        }
        return false;
    }
}