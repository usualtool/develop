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
            $ai = new Ai();
            echo $ai->Chat($messages, $model, $stream, $sessionid);
        }
    }
}