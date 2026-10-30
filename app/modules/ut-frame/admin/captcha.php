<?php
namespace Controller\Ut_frame\Admin;
use usualtool\Lib\Code;
class Captcha{
    public function __call($name,$args){
        $this->index();
    }
    public function index(){
        $captcha = new Code();
        $captcha->CreateImage();
        $_SESSION['authcode']=$captcha->GetCode();
    }
}