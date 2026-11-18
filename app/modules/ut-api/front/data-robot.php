<?php
namespace Controller\Ut_api\Front;
use usualtool\Ai\Ai;
use Model\Ut_api\Verify;
class Data_robot{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        global $config;
        (new Verify())->Check();
        //需启用usualtool/ut-ai扩展
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $messages = $data['messages'] ?? [];
            $model = $data['model'] ?? 'GLM-4.7-FlashX';
            $stream = $data['stream'] ?? false;
            $sessionid = $data['sessionid'] ?? '';
            if(!class_exists('usualtool\\Ai\\Ai')){
                echo json_encode(array(array("error"=>"AI extension not installed")),JSON_UNESCAPED_UNICODE);
                return;
            }
            $ai = new Ai();
            $type = strtolower(trim((string)($data['type'] ?? $data['action'] ?? '')));
            $prompt = trim((string)($data['prompt'] ?? ''));
            if($type === 'image' || ($type === '' && empty($messages) && $prompt !== '')){
                $options = array();
                foreach(array('size','quality','style','n','response_format','user') as $key){
                    if(isset($data[$key])){
                        $options[$key] = $data[$key];
                    }
                }
                echo $ai->Image($prompt, (string)($data['model'] ?? ''), $options);
                return;
            }
            echo $ai->Chat($messages, $model, $stream, $sessionid);
        }
    }
}