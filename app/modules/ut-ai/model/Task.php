<?php
namespace Model\Ut_ai;
use usualtool\Lib\Data;
use usualtool\Lib\Ai as UAiLib;
use usualtool\Ai\Ai as UAi;
use Model\Ut_api\Writer;
use Model\Ut_ai\Log as LogModel;
class Task{
    public function GetList($limit="0,20"){
        return Data::QueryData("cms_ai_task","","","id desc",$limit,array());
    }
    public function GetOne($id){
        $r=Data::QueryData("cms_ai_task","","id=?","","1",array(intval($id)))["querydata"];
        return !empty($r)?$r[0]:null;
    }
    public function GetReady($limit="0,10"){
        return Data::QueryData("cms_ai_task","","state in (?,?)","id asc",$limit,array(1,2));
    }
    public function Create($post){
        $title=trim((string)($post["title"]??""));
        if($title===""){
            $title=mb_substr(trim((string)($post["prompt"]??"")),0,30);
        }
        $model=trim((string)($post["model"]??""));
        $prompt=trim((string)($post["prompt"]??""));
        if($prompt===""){
            return false;
        }
        $context=trim((string)($post["context"]??""));
        $options=trim((string)($post["options"]??""));
        if($options!=="" && !json_decode($options)){
            $options="";
        }
        $state=isset($post["state"])?intval($post["state"]):1;
        if($state<0 || $state>4){
            $state=1;
        }
        $type=strtolower(trim((string)($post["task_type"]??"text")));
        if($type!=="image"){
            $type="text";
        }
        $writeDb=intval($post["write_db"]??0)===1?1:0;
        $targetTable=$writeDb===1?trim((string)($post["target_table"]??"")):"";
        $imageCount=intval($post["image_count"]??1);
        if($imageCount<1){
            $imageCount=1;
        }
        if($imageCount>10){
            $imageCount=10;
        }
        $isCron=intval($post["is_cron"]??0)===1?1:0;
        $cronTime=$isCron===1?trim((string)($post["cron_time"]??"")):"";
        if($cronTime!=="" && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$cronTime)){
            $cronTime="";
        }
        $now=date("Y-m-d H:i:s");
        $id=Data::InsertData("cms_ai_task",array(
            "title"=>"?","model"=>"?","task_type"=>"?","write_db"=>"?","target_table"=>"?","image_count"=>"?","prompt"=>"?","context"=>"?","options"=>"?",
            "state"=>"?","is_cron"=>"?","cron_time"=>"?","createtime"=>"?","updatetime"=>"?",
        ),array($title,$model,$type,$writeDb,$targetTable,$imageCount,$prompt,$context,$options,$state,$isCron,$cronTime,$now,$now));
        if(!$id){
            return false;
        }
        return $id;
    }
    /**
     * 确保 app/task 下存在 AI 能力统一任务文件（全局一个，无需按任务生成）
     */
    public function EnsureFile(){
        $dir=APP_ROOT."/task/";
        if(!is_dir($dir) && !@mkdir($dir,0755,true)){
            return false;
        }
        $code=<<<PHP
<?php
/**
 * AI 能力自动任务：由 ut-ai 模块维护，请勿手工修改
 * 统一调度：定时循环任务按 cron_time 执行，就绪任务按状态执行
 */
class AiTask{
    public static function Run(){
        \$task=new \Model\Ut_ai\Task();
        \$res=\$task->RunDue();
        echo "AI 任务：成功 ".\$res["ok"]."，失败 ".\$res["fail"]."\\r\\n";
    }
}
PHP;
        return file_put_contents($dir."AiTask.php",$code)!==false;
    }
    public function Update($id,$data){
        $sets=array();
        $vals=array();
        foreach($data as $k=>$v){
            if($k==="id"){
                continue;
            }
            $sets[$k]="?";
            if(is_bool($v)){
                $vals[]=$v?1:0;
            }else{
                $vals[]=$v;
            }
        }
        $sets["updatetime"]="?";
        $vals[]=date("Y-m-d H:i:s");
        $vals[]=intval($id);
        return Data::UpdateData("cms_ai_task",$sets,"id=?",$vals);
    }
    public function Delete($id){
        Data::DelData("cms_ai_log","task_id=?",array(intval($id)));
        return Data::DelData("cms_ai_task","id=?",array(intval($id)));
    }
    private function CheckConfig(){
        return UAiLib::EnvMissing(UAiLib::EnvAll());
    }
    public function RunOne($id){
        $task=$this->GetOne($id);
        if(!$task){
            return array("ok"=>false,"error"=>"任务不存在");
        }
        $config=new Config();
        $cfg=$config->Get();
        $missing=$this->CheckConfig();
        if(!empty($missing)){
            return array("ok"=>false,"error"=>"配置不完整：".implode(", ",$missing));
        }
        $type=strtolower(trim((string)($task["task_type"]??"text")));
        if($type!=="image"){
            $type="text";
        }
        $writeDb=intval($task["write_db"]??0)===1;
        $model=$task["model"]!==""?$task["model"]:($cfg["index_model"]??"");
        if($model==="" && !empty($cfg["allowed_models"])){
            $model=$cfg["allowed_models"][0];
        }
        if($type!=="image" && $model===""){
            return array("ok"=>false,"error"=>"未指定模型");
        }
        $messages=array();
        $systemPrompt=$cfg["system_prompt"]??"";
        if($systemPrompt!==""){
            $messages[]=array("role"=>"system","content"=>$systemPrompt);
        }
        if(!empty($task["context"])){
            $messages[]=array("role"=>"user","content"=>$task["context"]);
        }
        $messages[]=array("role"=>"user","content"=>$task["prompt"]);
        $options=array();
        if(!empty($task["options"])){
            $tmp=json_decode($task["options"],true);
            if(is_array($tmp)){
                $options=$tmp;
            }
        }
        $start=microtime(true);
        if($writeDb){
            $w=$type==="image"?$this->RunImageWrite($task,$cfg,$options):$this->RunWrite($task,$model,$options);
            $result=array("content"=>$w["content"],"reasoning"=>"","usage"=>array(),"error"=>$w["error"]);
            $messages=$w["messages"];
            if(!empty($w["model"])){
                $model=$w["model"];
            }
        }elseif($type==="image"){
            $imgModel=$task["model"]!==""?$task["model"]:($cfg["image_model"]??"");
            $imgPrompt=trim((string)$task["prompt"]);
            if($imgPrompt===""){
                return array("ok"=>false,"error"=>"提示词不能为空");
            }
            if(trim((string)$task["context"])!==""){
                $imgPrompt.="\n".trim((string)$task["context"]);
            }
            $size=trim((string)($options["size"]??($cfg["image_size"]??"")));
            $imgOptions=array();
            foreach(array("quality","style","n","response_format","user") as $k){
                if(isset($options[$k])){
                    $imgOptions[$k]=$options[$k];
                }
            }
            $n=intval($task["image_count"]??1);
            if($n<1){
                $n=1;
            }
            if($n>10){
                $n=10;
            }
            $imgOptions["n"]=$n;
            if($size!==""){
                $imgOptions["size"]=$size;
            }
            $img=$this->RunImage($imgPrompt,$imgModel,$imgOptions);
            $result=array("content"=>implode("\n",$img["paths"]),"reasoning"=>"","usage"=>array(),"error"=>$img["error"]);
            $model=$imgModel!==""?$imgModel:"image";
            $messages=array(array("role"=>"user","content"=>"生图：".$imgPrompt."（".count($img["paths"])." 张）"));
        }else{
            $result=UAiLib::Fetch($messages,$model);
        }
        $duration=(int)round((microtime(true)-$start)*1000);
        $state=$result["error"]===""?3:4;
        $this->Update($id,array(
            "result"=>$result["content"],
            "reasoning"=>$result["reasoning"],
            "token_usage"=>!empty($result["usage"])?json_encode($result["usage"],JSON_UNESCAPED_UNICODE):"",
            "state"=>$state,
            "error_msg"=>$result["error"],
            "last_run_time"=>date("Y-m-d H:i:s"),
            "run_count"=>(int)($task["run_count"]??0)+1,
            "retry_count"=>$state===4?(int)($task["retry_count"]??0)+1:(int)($task["retry_count"]??0)
        ));
        $log=new LogModel();
        $log->Add(array(
            "task_id"=>$id,
            "messages"=>json_encode($messages,JSON_UNESCAPED_UNICODE),
            "model"=>$model,
            "result"=>$result["content"],
            "reasoning"=>$result["reasoning"],
            "token_usage"=>!empty($result["usage"])?json_encode($result["usage"],JSON_UNESCAPED_UNICODE):"",
            "error_msg"=>$result["error"],
            "duration"=>$duration
        ));
        return array("ok"=>$result["error"]==="","error"=>$result["error"],"content"=>$result["content"],"duration"=>$duration);
    }
    /**
     * 调上游生图并落地到本地，按 n 生成全部图片
     */
    private function RunImage($prompt,$model,$options=array()){
        $want=isset($options["n"])?intval($options["n"]):1;
        if($want<1){
            $want=1;
        }
        if($want>10){
            $want=10;
        }
        $paths=array();
        $lastError="";
        $attempts=0;
        while(count($paths)<$want && $attempts<$want){
            $attempts++;
            $img=UAiLib::Image($prompt,$model,$options);
            if($img["error"]!==""){
                $lastError=$img["error"];
                break;
            }
            $items=!empty($img["items"])?$img["items"]:array(array("url"=>$img["url"],"b64_json"=>$img["b64_json"]));
            $added=0;
            foreach($items as $one){
                if(count($paths)>=$want){
                    break;
                }
                $url=trim((string)($one["url"]??""));
                $b64=(string)($one["b64_json"]??"");
                $local=$this->SaveImage($url,$b64);
                if($local!==""){
                    $paths[]=$local;
                    $added++;
                }elseif($url!==""){
                    $paths[]=$url;
                    $added++;
                }
            }
            if($added===0){
                break;
            }
        }
        if(empty($paths)){
            return array("paths"=>array(),"error"=>$lastError!==""?$lastError:"图片保存失败");
        }
        return array("paths"=>$paths,"error"=>"");
    }
    /**
     * 删除本地已生成的图片（写库失败时回收）
     */
    private function RemoveImages($paths){
        $appurl=rtrim((string)($GLOBALS["config"]["APPURL"]??""),"/");
        foreach((array)$paths as $path){
            $rel=(string)$path;
            if($appurl!=="" && strpos($rel,$appurl)===0){
                $rel=substr($rel,strlen($appurl));
            }
            if(strpos($rel,"/assets/upload/")!==0){
                continue;
            }
            $file=UTF_ROOT."/open".$rel;
            if(is_file($file)){
                @unlink($file);
            }
        }
    }
    /**
     * 保存图片到 open/assets/upload/ai/YYYYMM
     */
    private function SaveImage($url,$b64){
        $binary="";
        if(trim((string)$b64)!==""){
            $binary=base64_decode($b64);
        }elseif(trim((string)$url)!==""){
            $ch=curl_init($url);
            curl_setopt_array($ch,array(
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_FOLLOWLOCATION=>true,
                CURLOPT_TIMEOUT=>120
            ));
            $binary=curl_exec($ch);
            $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
            curl_close($ch);
            if($binary===false || $code>=400){
                $binary="";
            }
        }
        if($binary===""){
            return "";
        }
        $ext="png";
        $info=@getimagesizefromstring($binary);
        if(is_array($info) && !empty($info["mime"])){
            $map=array("image/jpeg"=>"jpg","image/png"=>"png","image/webp"=>"webp","image/gif"=>"gif");
            $ext=$map[$info["mime"]]??"png";
        }
        $sub="ai/".date("Ym")."/";
        $dir=UTF_ROOT."/open/assets/upload/".$sub;
        if(!is_dir($dir) && !@mkdir($dir,0755,true)){
            return "";
        }
        $name=date("YmdHis")."_".substr(md5($binary.microtime(true)),0,8).".".$ext;
        if(@file_put_contents($dir.$name,$binary)===false){
            return "";
        }
        $appurl=rtrim((string)($GLOBALS["config"]["APPURL"]??""),"/");
        return $appurl."/assets/upload/".$sub.$name;
    }
    private function RunWrite($task,$model,$options){
        $fail=array("content"=>"","error"=>"","messages"=>array());
        $table=trim((string)($task["target_table"]??""));
        $writer=new Writer();
        $err=$writer->Check($table);
        if($err!==""){
            $fail["error"]=$err;
            return $fail;
        }
        $schema=$writer->Schema($table);
        $sys="你是数据录入助手。只输出一个 JSON 对象，不要输出解释、Markdown 代码块或 SQL 语句。"
            ."只能使用下列字段名，标记为必填的必须提供，未列出的字段一律不要输出。"
            ."时间类字段（addtime/createtime/qtime 等）由服务端按入库时间自动填充，图片类字段（pic/img/thumb 等）由服务端留空，以上均不要输出：\n".implode("\n",$schema["lines"]);
        $user=trim((string)$task["prompt"]);
        if($user===""){
            $user="请根据上下文生成一条记录";
        }
        $context=trim((string)$task["context"]);
        if($context!==""){
            $user.="\n\n上下文：\n".$context;
        }
        $messages=array(
            array("role"=>"system","content"=>$sys),
            array("role"=>"user","content"=>$user)
        );
        $fail["messages"]=$messages;
        $r=UAiLib::Fetch($messages,$model);
        if($r["error"]!==""){
            $fail["error"]=$r["error"];
            return $fail;
        }
        $data=$this->ParseJson($r["content"]);
        if($data===null){
            $fail["error"]="AI 未返回合法 JSON：".mb_substr((string)$r["content"],0,200);
            return $fail;
        }
        if(isset($options["map"]) && is_array($options["map"])){
            foreach($options["map"] as $from=>$to){
                if(array_key_exists($from,$data)){
                    $data[$to]=$data[$from];
                    unset($data[$from]);
                }
            }
        }
        $saved=$writer->Insert($table,$data);
        if(!$saved["ok"]){
            $fail["error"]=$saved["error"];
            return $fail;
        }
        $fail["content"]="已写入 ".$table."，ID=".$saved["id"]."\n".json_encode($saved["data"],JSON_UNESCAPED_UNICODE);
        return $fail;
    }
    /**
     * 生图后写入业务表：图片落盘路径写入第一个图片字段，其余必填字段由文字模型补充
     */
    private function RunImageWrite($task,$cfg,$options){
        $fail=array("content"=>"","error"=>"","messages"=>array(),"model"=>"");
        $table=trim((string)($task["target_table"]??""));
        $writer=new Writer();
        $err=$writer->Check($table);
        if($err!==""){
            $fail["error"]=$err;
            return $fail;
        }
        $imgFields=$writer->ImageFields($table);
        if(empty($imgFields)){
            $fail["error"]="表 ".$table." 没有可写入的图片字段";
            return $fail;
        }
        $imgField=$imgFields[0];
        $imgModel=trim((string)$task["model"])!==""?trim((string)$task["model"]):trim((string)($cfg["image_model"]??""));
        $fail["model"]=$imgModel!==""?$imgModel:"image";
        $imgPrompt=trim((string)$task["prompt"]);
        if($imgPrompt===""){
            $fail["error"]="提示词不能为空";
            return $fail;
        }
        if(trim((string)$task["context"])!==""){
            $imgPrompt.="\n".trim((string)$task["context"]);
        }
        $size=trim((string)($options["size"]??($cfg["image_size"]??"")));
        $imgOptions=array();
        foreach(array("quality","style","n","response_format","user") as $k){
            if(isset($options[$k])){
                $imgOptions[$k]=$options[$k];
            }
        }
        $n=intval($task["image_count"]??1);
        if($n<1){
            $n=1;
        }
        if($n>10){
            $n=10;
        }
        $imgOptions["n"]=$n;
        if($size!==""){
            $imgOptions["size"]=$size;
        }
        $img=$this->RunImage($imgPrompt,$imgModel,$imgOptions);
        $messages=array(array("role"=>"user","content"=>"生图：".$imgPrompt."（".count($img["paths"])." 张）"));
        $fail["messages"]=$messages;
        if($img["error"]!==""){
            $fail["error"]=$img["error"];
            return $fail;
        }
        $data=array();
        $schema=$writer->Schema($table);
        $required=$writer->RequiredFields($table);
        if(!empty($schema["lines"])){
            $textModel=trim((string)($cfg["index_model"]??""));
            if($textModel==="" && !empty($cfg["allowed_models"])){
                $textModel=$cfg["allowed_models"][0];
            }
            if($textModel===""){
                if(!empty($required)){
                    $fail["error"]="目标表必填字段（".implode("、",$required)."）需要文字模型补充，但未配置 INDEX_MODEL";
                    return $fail;
                }
            }else{
                $sys="你是内容录入助手。只输出一个 JSON 对象，不要输出解释、Markdown 代码块或 SQL 语句。"
                    ."只能使用下列字段名，未列出的字段一律不要输出；时间类、图片类字段由系统处理：\n".implode("\n",$schema["lines"])
                    ."\n标题、摘要、描述等文字字段必须填写，内容要贴合图片主题。";
                $meta=array(
                    array("role"=>"system","content"=>$sys),
                    array("role"=>"user","content"=>"根据以下生图需求，为这条记录生成合适的字段内容：\n".$imgPrompt)
                );
                $fail["messages"]=array_merge($messages,$meta);
                $mr=UAiLib::Fetch($meta,$textModel);
                if($mr["error"]!==""){
                    $this->RemoveImages($img["paths"]);
                    $fail["error"]=$mr["error"];
                    return $fail;
                }
                $map=$this->ParseJson($mr["content"]);
                if($map===null){
                    $this->RemoveImages($img["paths"]);
                    $fail["error"]="AI 未返回合法 JSON：".mb_substr((string)$mr["content"],0,200);
                    return $fail;
                }
                $data=$map;
            }
        }
        $savedIds=array();
        $written=array();
        foreach($img["paths"] as $path){
            $row=$data;
            $row[$imgField]=$path;
            $saved=$writer->Insert($table,$row,array($imgField));
            if(!$saved["ok"]){
                $this->RemoveImages(array_slice($img["paths"],count($savedIds)));
                $fail["error"]=$saved["error"];
                if(!empty($savedIds)){
                    $fail["error"].="（已写入 ID：".implode(",",$savedIds)."）";
                }
                return $fail;
            }
            $savedIds[]=$saved["id"];
            $written[]=$saved["data"];
        }
        $fail["content"]="已生成 ".count($img["paths"])." 张图片并写入 ".$table."，ID=".implode(",",$savedIds)."\n"
            .implode("\n",$img["paths"])."\n".json_encode($written,JSON_UNESCAPED_UNICODE);
        return $fail;
    }
    /**
     * 从回复里提取 JSON
     */
    private function ParseJson($text){
        $text=trim((string)$text);
        $text=preg_replace('/^```(?:json)?\s*/i','',$text);
        $text=preg_replace('/\s*```$/','',$text);
        $start=strpos($text,"{");
        $end=strrpos($text,"}");
        if($start===false || $end===false || $end<$start){
            return null;
        }
        $data=json_decode(substr($text,$start,$end-$start+1),true);
        return is_array($data)?$data:null;
    }
    /**
     * 统一调度：定时任务在计划时间后 1 小时内补执行，成功即停、失败继续重试
     */
    public function RunDue(){
        $ok=0;
        $fail=0;
        $now=time();
        $list=Data::QueryData("cms_ai_task","","","id asc","500")["querydata"]??array();
        foreach($list as $task){
            $id=intval($task["id"]);
            if(intval($task["is_cron"]??0)===1){
                if(intval($task["state"])===0){
                    continue;
                }
                if(!$this->CronDue($task,$now)){
                    continue;
                }
            }elseif(!in_array(intval($task["state"]),array(1,2),true)){
                continue;
            }
            $this->Update($id,array("state"=>2));
            $res=$this->RunOne($id);
            if(!empty($res["ok"])){
                $ok++;
            }else{
                $fail++;
            }
        }
        return array("ok"=>$ok,"fail"=>$fail);
    }
    /**
     * 定时任务是否到期：计划时间起 1 小时内有效；本时段已成功则跳过，失败可重试
     */
    private function CronDue($task,$nowTs){
        $cronTime=trim((string)($task["cron_time"]??""));
        if($cronTime==="" || intval($task["state"]??0)===2){
            return false;
        }
        $slot=0;
        foreach(array(date("Y-m-d"),date("Y-m-d",strtotime("-1 day"))) as $day){
            $ts=strtotime($day." ".$cronTime);
            if($ts!==false && $nowTs>=$ts && $nowTs<$ts+3600){
                $slot=$ts;
                break;
            }
        }
        if($slot<=0){
            return false;
        }
        $fails=Data::QueryData("cms_ai_log","id","task_id=? and addtime>=? and error_msg<>''","","100",array(intval($task["id"]),date("Y-m-d H:i:s",$slot)));
        if(intval($fails["querynum"]??0)>=3){
            return false;
        }
        $last=trim((string)($task["last_run_time"]??""));
        if($last!=="" && intval($task["state"]??0)===3){
            $lastTs=strtotime($last);
            if($lastTs!==false && $lastTs>=$slot){
                return false;
            }
        }
        return true;
    }
    public function RunReady($limit=10){
        $list=$this->GetReady("0,".intval($limit));
        $ok=0;$fail=0;
        if(!empty($list["querydata"])){
            foreach($list["querydata"] as $task){
                $this->Update($task["id"],array("state"=>2));
                $res=$this->RunOne($task["id"]);
                if($res["ok"]){$ok++;}else{$fail++;}
            }
        }
        return array("ok"=>$ok,"fail"=>$fail);
    }
}