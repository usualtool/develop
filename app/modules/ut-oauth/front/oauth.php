<?php
namespace Controller\Ut_oauth\Front;
use usualtool\Oauth\Oauth as OauthService;
class Oauth{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $oauth=new OauthService();
        $path=$_GET['route'] ?? '';
        switch($path){
            case 'authorize':
                $oauth->Authorize();
                break;
            case 'token':
                $oauth->Token();
                break;
            default:
                http_response_code(404);
                echo "ROUTE ERROR";
        }
    }
}