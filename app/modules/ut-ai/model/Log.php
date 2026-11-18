<?php
namespace Model\Ut_ai;
use usualtool\Lib\Data;
class Log{
    public function GetList($limit="0,20"){
        return Data::QueryData("cms_ai_log","","","id desc",$limit,array());
    }
    public function GetByTask($taskId,$limit="0,20"){
        return Data::QueryData("cms_ai_log","","task_id=?","id desc",$limit,array(intval($taskId)));
    }
    public function GetOne($id){
        $r=Data::QueryData("cms_ai_log","","id=?","","1",array(intval($id)))["querydata"];
        return !empty($r)?$r[0]:null;
    }
    public function Add($data){
        $now=date("Y-m-d H:i:s");
        return Data::InsertData("cms_ai_log",array(
            "task_id"=>"?","messages"=>"?","model"=>"?","result"=>"?","reasoning"=>"?",
            "token_usage"=>"?","error_msg"=>"?","duration"=>"?","addtime"=>"?",
        ),array(
            intval($data["task_id"]),
            (string)($data["messages"]??""),
            (string)($data["model"]??""),
            (string)($data["result"]??""),
            (string)($data["reasoning"]??""),
            (string)($data["usage"]??""),
            (string)($data["error_msg"]??""),
            intval($data["duration"]??0),
            $now
        ));
    }
    public function ClearBefore($time){
        return Data::DelData("cms_ai_log","addtime<?",array($time));
    }
    public function ClearAll(){
        return Data::DelData("cms_ai_log","1=1",array());
    }
}