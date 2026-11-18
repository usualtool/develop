<?php
namespace Model\Ut_ai;
use usualtool\Lib\Data;
use usualtool\Lib\Ai as UAiLib;
class Config{
    private function EnvPath(){
        return UTF_ROOT."/vendor/usualtool/ut-ai/src/.env";
    }
    private function RootPath(){
        return UTF_ROOT."/.ut-ai.env";
    }
    private function ReadEnv($file){
        $env=array();
        if(!file_exists($file)){
            return $env;
        }
        $lines=file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        foreach($lines as $line){
            $line=trim($line);
            if($line==="" || strpos($line,"#")===0 || strpos($line,"=")===false){
                continue;
            }
            $pair=explode("=",$line,2);
            $env[trim($pair[0])]=trim($pair[1]);
        }
        return $env;
    }
    /**
     * 生效配置：包内 .env 为底，根 .ut-ai.env 覆盖（与 Ai::EnvAll 一致）
     */
    private function Effective(){
        $env=$this->ReadEnv($this->EnvPath());
        foreach($this->ReadEnv($this->RootPath()) as $key=>$value){
            if($value!==""){
                $env[$key]=$value;
            }
        }
        return $env;
    }
    public function Get(){
        $env=$this->Effective();
        $models=isset($env["ALLOWED_MODELS"])?array_values(array_filter(array_map("trim",explode(",",(string)$env["ALLOWED_MODELS"])),'strlen')):array();
        $ids=isset($env["KNOWLEDGE_BASE_IDS"])?array_values(array_filter(array_map("trim",explode(",",(string)$env["KNOWLEDGE_BASE_IDS"])),'strlen')):array();
        return array(
            "upstream_base_url"=>(string)($env["UPSTREAM_BASE_URL"]??""),
            "upstream_api_key"=>(string)($env["UPSTREAM_API_KEY"]??""),
            "my_api_key"=>(string)($env["MY_API_KEY"]??""),
            "allowed_models"=>$models,
            "allowed_models_text"=>implode(",",$models),
            "index_model"=>(string)($env["INDEX_MODEL"]??""),
            "system_prompt"=>(string)($env["SYSTEM_PROMPT"]??""),
            "enable_thinking"=>!empty($env["ENABLE_THINKING"]),
            "knowledge_enabled"=>!empty($env["KNOWLEDGE_ENABLED"]),
            "knowledge_base_url"=>(string)($env["KNOWLEDGE_BASE_URL"]??""),
            "knowledge_api_key"=>(string)($env["KNOWLEDGE_API_KEY"]??""),
            "knowledge_base_ids"=>$ids,
            "knowledge_base_ids_text"=>implode(",",$ids),
            "knowledge_top_k"=>(int)($env["KNOWLEDGE_TOP_K"]??8),
            "knowledge_top_n"=>(int)($env["KNOWLEDGE_TOP_N"]??5),
            "knowledge_enable_rerank"=>!empty($env["KNOWLEDGE_ENABLE_RERANK"]),
            "knowledge_model"=>(string)($env["KNOWLEDGE_MODEL"]??""),
            "knowledge_enable_thinking"=>!empty($env["KNOWLEDGE_ENABLE_THINKING"]),
            "image_base_url"=>(string)($env["IMAGE_BASE_URL"]??""),
            "image_api_key"=>(string)($env["IMAGE_API_KEY"]??""),
            "image_model"=>(string)($env["IMAGE_MODEL"]??"cogview-3-flash"),
            "image_size"=>(string)($env["IMAGE_SIZE"]??"1024x1024"),
            "env_file"=>$this->EnvPath(),
            "missing"=>UAiLib::EnvMissing($env)
        );
    }
    public function Save($post){
        $map=array(
            "upstream_base_url"=>"UPSTREAM_BASE_URL",
            "upstream_api_key"=>"UPSTREAM_API_KEY",
            "my_api_key"=>"MY_API_KEY",
            "allowed_models"=>"ALLOWED_MODELS",
            "index_model"=>"INDEX_MODEL",
            "system_prompt"=>"SYSTEM_PROMPT",
            "enable_thinking"=>"ENABLE_THINKING",
            "knowledge_enabled"=>"KNOWLEDGE_ENABLED",
            "knowledge_base_url"=>"KNOWLEDGE_BASE_URL",
            "knowledge_api_key"=>"KNOWLEDGE_API_KEY",
            "knowledge_base_ids"=>"KNOWLEDGE_BASE_IDS",
            "knowledge_top_k"=>"KNOWLEDGE_TOP_K",
            "knowledge_top_n"=>"KNOWLEDGE_TOP_N",
            "knowledge_enable_rerank"=>"KNOWLEDGE_ENABLE_RERANK",
            "knowledge_model"=>"KNOWLEDGE_MODEL",
            "knowledge_enable_thinking"=>"KNOWLEDGE_ENABLE_THINKING",
            "image_base_url"=>"IMAGE_BASE_URL",
            "image_api_key"=>"IMAGE_API_KEY",
            "image_model"=>"IMAGE_MODEL",
            "image_size"=>"IMAGE_SIZE"
        );
        $checks=array("enable_thinking","knowledge_enabled","knowledge_enable_rerank","knowledge_enable_thinking");
        $files=array($this->EnvPath(),$this->RootPath());
        $current=$this->Effective();
        $ok=true;
        foreach($map as $field=>$key){
            if(!array_key_exists($field,$post)){
                continue;
            }
            $raw=$post[$field];
            if(is_string($raw)){
                $raw=trim($raw);
            }
            if(!in_array($field,$checks,true) && $raw===""){
                continue;
            }
            $value=$this->Normalize($field,$raw);
            if($key==="ALLOWED_MODELS"){
                $value=$this->MergeList($current[$key]??"",$value);
            }
            if(isset($current[$key]) && $current[$key]===$value){
                continue;
            }
            foreach($files as $file){
                if(UAiLib::EnvSet($file,$key,$value)===false){
                    $ok=false;
                }
            }
        }
        $this->Mirror($this->Effective());
        return $ok;
    }
    private function Normalize($field,$value){
        if(in_array($field,array("enable_thinking","knowledge_enabled","knowledge_enable_rerank","knowledge_enable_thinking"),true)){
            return empty($value)?"0":"1";
        }
        if($field==="knowledge_top_k" || $field==="knowledge_top_n"){
            return (string)max(0,intval($value));
        }
        if($field==="allowed_models" || $field==="knowledge_base_ids"){
            return $this->JoinList($value);
        }
        return trim((string)$value);
    }
    private function MergeList($current,$value){
        $list=array();
        foreach(array($current,$value) as $item){
            foreach(explode(",",(string)$item) as $one){
                $one=trim($one);
                if($one!=="" && !in_array($one,$list,true)){
                    $list[]=$one;
                }
            }
        }
        return implode(",",$list);
    }
    private function JoinList($value){
        if(is_array($value)){
            return implode(",",array_values(array_filter(array_map("trim",$value),'strlen')));
        }
        return implode(",",array_values(array_filter(array_map("trim",explode(",",(string)$value)),'strlen')));
    }
    private function Mirror($env){
        $row=array(
            "upstream_base_url"=>(string)($env["UPSTREAM_BASE_URL"]??""),
            "upstream_api_key"=>(string)($env["UPSTREAM_API_KEY"]??""),
            "my_api_key"=>(string)($env["MY_API_KEY"]??""),
            "allowed_models"=>(string)($env["ALLOWED_MODELS"]??""),
            "index_model"=>(string)($env["INDEX_MODEL"]??""),
            "system_prompt"=>(string)($env["SYSTEM_PROMPT"]??""),
            "enable_thinking"=>($env["ENABLE_THINKING"]??"")==="1"?1:0,
            "knowledge_enabled"=>($env["KNOWLEDGE_ENABLED"]??"")==="1"?1:0,
            "knowledge_base_url"=>(string)($env["KNOWLEDGE_BASE_URL"]??""),
            "knowledge_api_key"=>(string)($env["KNOWLEDGE_API_KEY"]??""),
            "knowledge_base_ids"=>(string)($env["KNOWLEDGE_BASE_IDS"]??""),
            "knowledge_top_k"=>intval($env["KNOWLEDGE_TOP_K"]??0),
            "knowledge_top_n"=>intval($env["KNOWLEDGE_TOP_N"]??0),
            "knowledge_enable_rerank"=>($env["KNOWLEDGE_ENABLE_RERANK"]??"")==="1"?1:0,
            "knowledge_model"=>(string)($env["KNOWLEDGE_MODEL"]??""),
            "knowledge_enable_thinking"=>($env["KNOWLEDGE_ENABLE_THINKING"]??"")==="1"?1:0,
            "updatetime"=>date("Y-m-d H:i:s")
        );
        $exists=Data::QueryData("cms_ai","id","id=?","","1",array(1))["querydata"];
        if(!empty($exists)){
            $row["id"]=1;
            $values=$this->Values($row);
            $values[]=1;
            return Data::UpdateData("cms_ai",$this->Slots($row),"id=?",$values);
        }
        $row["addtime"]=date("Y-m-d H:i:s");
        return Data::InsertData("cms_ai",$this->Slots($row),$this->Values($row));
    }
    private function Slots($row){
        $slots=array();
        foreach($row as $key=>$value){
            if($key!=="id"){
                $slots[$key]="?";
            }
        }
        return $slots;
    }
    private function Values($row){
        $values=array();
        foreach($row as $key=>$value){
            if($key!="id"){
                $values[]=$value;
            }
        }
        return $values;
    }
}