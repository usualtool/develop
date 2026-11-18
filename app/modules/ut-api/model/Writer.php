<?php
/**
 * data-mcp专属写数据
 */
namespace Model\Ut_api;
use usualtool\Lib\Data;
use usualtool\Lib\Inc;
class Writer{
    private static $systemTables=array(
        "cms_admin","cms_api","cms_api_app","cms_api_secret","cms_api_log","cms_api_nonce",
        "cms_module","cms_ai","cms_ai_task","cms_ai_log"
    );
    /**
     * ut-api 开放表白名单
     */
    public function OpenTables(){
        $row=Data::QueryData("cms_api","opentable","","","1")["querydata"][0]??array();
        return array_values(array_filter(array_map("trim",explode(",",(string)($row["opentable"]??""))),'strlen'));
    }
    /**
     * 受保护字段
     */
    public function ProtectFields(){
        $row=Data::QueryData("cms_api","protect","","","1")["querydata"][0]??array();
        $list=array_values(array_filter(array_map("trim",explode(",",(string)($row["protect"]??""))),'strlen'));
        if(!in_array("password",$list,true)){
            array_unshift($list,"password");
        }
        return $list;
    }
    /**
     * 写入前检查，返回空字符串表示通过
     */
    public function Check($table){
        $table=trim((string)$table);
        if($table===""){
            return "未指定目标表";
        }
        if(!in_array($table,$this->OpenTables(),true)){
            return "表 ".$table." 不在 ut-api 白名单（cms_api.opentable）中";
        }
        if(in_array($table,self::$systemTables,true)){
            return "表 ".$table." 被保护，不可写入";
        }
        if(empty($this->Columns($table))){
            return "表 ".$table." 不存在或没有字段";
        }
        return "";
    }
    /**
     * 可写字段描述，供构造 AI 提示词
     */
    public function Schema($table){
        $cols=$this->Columns($table);
        $protect=$this->ProtectFields();
        $lines=array();
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || $this->IsTimeColumn($col) || $this->IsImageField($name)){
                continue;
            }
            $note=array($col["COLUMN_TYPE"]);
            if($this->IsRequired($col)){
                $note[]="必填";
            }elseif($col["COLUMN_DEFAULT"]!==null && $col["COLUMN_DEFAULT"]!==""){
                $note[]="默认=".$col["COLUMN_DEFAULT"];
            }
            $lines[]="- ".$name."（".implode("，",$note)."）";
        }
        return array("columns"=>$cols,"lines"=>$lines,"protect"=>$protect);
    }
    /**
     * 校验并写入一条记录
     */
    public function Insert($table,$data,$allowImage=array()){
        $table=trim((string)$table);
        $err=$this->Check($table);
        if($err!==""){
            return array("ok"=>false,"id"=>0,"error"=>$err,"data"=>array());
        }
        $schema=$this->Schema($table);
        $cols=$schema["columns"];
        $protect=$schema["protect"];
        if(!is_array($data)){
            $data=array();
        }
        $data=array_intersect_key($data,$cols);
        $data=array_diff_key($data,array_flip($protect));
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || !$this->IsTimeColumn($col)){
                continue;
            }
            $data[$name]=$this->NowForType($col);
        }
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || !$this->IsImageField($name)){
                continue;
            }
            if(in_array($name,$allowImage,true) && isset($data[$name]) && trim((string)$data[$name])!==""){
                continue;
            }
            unset($data[$name]);
            if($this->IsRequired($col)){
                $data[$name]=$this->EmptyForType($col);
            }
        }
        $missing=array();
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || !$this->IsRequired($col) || $this->IsImageField($name)){
                continue;
            }
            if(!isset($data[$name]) || (is_string($data[$name]) && trim($data[$name])==="")){
                $missing[]=$name;
            }
        }
        if(!empty($missing)){
            return array("ok"=>false,"id"=>0,"error"=>"缺少必填字段：".implode("、",$missing),"data"=>array());
        }
        if(empty($data)){
            return array("ok"=>false,"id"=>0,"error"=>"没有可写入的字段","data"=>array());
        }
        $clean=array();
        foreach($data as $name=>$value){
            if(!$this->IsRequired($cols[$name]) && ($value===null || (is_string($value) && trim($value)===""))){
                continue;
            }
            $e="";
            $clean[$name]=$this->NormalizeValue($cols[$name],$value,$e);
            if($e!==""){
                return array("ok"=>false,"id"=>0,"error"=>$e,"data"=>array());
            }
        }
        $slots=array();
        $values=array();
        foreach($clean as $name=>$value){
            $slots[$name]="?";
            $values[]=$value;
        }
        $newid=Data::InsertData($table,$slots,$values);
        if(!$newid){
            return array("ok"=>false,"id"=>0,"error"=>"写入失败，请检查必填字段与字段类型","data"=>array());
        }
        $this->Log($table,$newid,$clean);
        return array("ok"=>true,"id"=>intval($newid),"error"=>"","data"=>$clean);
    }
    /**
     * 写入审计
     */
    private function Log($table,$id,$data){
        $detail=json_encode(array("id"=>intval($id),"data"=>$data),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if(strlen((string)$detail)>2000){
            $detail=substr((string)$detail,0,2000);
        }
        Data::InsertData("cms_api_log",array(
            "act"=>"add",
            "utable"=>(string)$table,
            "uwhere"=>"",
            "udata"=>(string)$detail,
            "usecret"=>"",
            "ip"=>Inc::GetIp(),
            "addtime"=>date("Y-m-d H:i:s")
        ));
    }
    /**
     * 可写入的图片字段（按表结构顺序，限字符串类型）
     */
    public function ImageFields($table){
        $cols=$this->Columns($table);
        $protect=$this->ProtectFields();
        $out=array();
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || $this->IsTimeColumn($col) || !$this->IsImageField($name)){
                continue;
            }
            $type=strtolower((string)$col["DATA_TYPE"]);
            if(in_array($type,array("varchar","char","text","mediumtext","longtext"),true)){
                $out[]=$name;
            }
        }
        return $out;
    }

    /**
     * 必填的可写字段（排除时间/图片/系统字段）
     */
    public function RequiredFields($table){
        $cols=$this->Columns($table);
        $protect=$this->ProtectFields();
        $out=array();
        foreach($cols as $name=>$col){
            if($this->SkipColumn($name,$col,$protect) || $this->IsTimeColumn($col) || $this->IsImageField($name)){
                continue;
            }
            if($this->IsRequired($col)){
                $out[]=$name;
            }
        }
        return $out;
    }

    /**
     * 读取真实表结构
     */
    public function Columns($table){
        $res=Data::RunYu("SELECT COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA,CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION",array((string)$table));
        $out=array();
        foreach((array)($res["querydata"]??array()) as $col){
            $out[$col["COLUMN_NAME"]]=$col;
        }
        return $out;
    }
    private function SkipColumn($name,$col,$protect){
        if(in_array($name,$protect,true)){
            return true;
        }
        return stripos((string)$col["EXTRA"],"auto_increment")!==false;
    }
    private function IsRequired($col){
        return $col["IS_NULLABLE"]==="NO" && $col["COLUMN_DEFAULT"]===null;
    }
    /**
     * 时间类型字段（datetime/timestamp/date/time）
     */
    private function IsTimeColumn($col){
        return in_array(strtolower((string)$col["DATA_TYPE"]),array("datetime","timestamp","date","time"),true);
    }
    /**
     * 图片/附件类字段
     */
    private function IsImageField($name){
        return (bool)preg_match('/pic|img|image|thumb|photo|avatar|logo|banner|cover|icon/i',(string)$name);
    }
    /**
     * 必填字段的空值（按类型）
     */
    private function EmptyForType($col){
        $type=strtolower((string)$col["DATA_TYPE"]);
        if(in_array($type,array("tinyint","smallint","mediumint","int","bigint","decimal","float","double"),true)){
            return 0;
        }
        return "";
    }
    /**
     * 当前时间按字段类型格式化
     */
    private function NowForType($col){
        $type=strtolower((string)$col["DATA_TYPE"]);
        if($type==="date"){
            return date("Y-m-d");
        }
        if($type==="time"){
            return date("H:i:s");
        }
        return date("Y-m-d H:i:s");
    }
    /**
     * 按字段类型归一化并校验
     */
    private function NormalizeValue($col,$value,&$err){
        $err="";
        $type=strtolower((string)$col["DATA_TYPE"]);
        $name=$col["COLUMN_NAME"];
        if(in_array($type,array("tinyint","smallint","mediumint","int","bigint"),true)){
            if(is_bool($value)){
                return $value?1:0;
            }
            if(!is_numeric($value)){
                $err="字段 ".$name." 需要整数";
                return "";
            }
            return (int)$value;
        }
        if(in_array($type,array("decimal","float","double"),true)){
            if(!is_numeric($value)){
                $err="字段 ".$name." 需要数字";
                return "";
            }
            return (float)$value;
        }
        if($type==="enum" || $type==="set"){
            $opts=array();
            if(preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/",(string)$col["COLUMN_TYPE"],$m)){
                $opts=$m[1];
            }
            foreach(explode(",",(string)$value) as $one){
                if(!in_array($one,$opts,true)){
                    $err="字段 ".$name." 不在枚举范围：".implode("/",$opts);
                    return "";
                }
            }
            return (string)$value;
        }
        if(in_array($type,array("datetime","timestamp","date","time"),true)){
            if(is_numeric($value)){
                return $type==="date"?date("Y-m-d",(int)$value):date("Y-m-d H:i:s",(int)$value);
            }
            if(strtotime((string)$value)===false){
                $err="字段 ".$name." 不是合法时间";
                return "";
            }
            return (string)$value;
        }
        if($type==="json"){
            return is_string($value)?$value:json_encode($value,JSON_UNESCAPED_UNICODE);
        }
        if(is_array($value) || is_object($value)){
            $value=json_encode($value,JSON_UNESCAPED_UNICODE);
        }
        $value=(string)$value;
        $len=intval($col["CHARACTER_MAXIMUM_LENGTH"]??0);
        if($len>0 && mb_strlen($value)>$len){
            $err="字段 ".$name." 超过长度限制 ".$len;
            return "";
        }
        return $value;
    }
}