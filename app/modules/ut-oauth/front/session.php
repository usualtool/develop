<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
use usualtool\Lib\Route;
if(isset($_SESSION['oauth_uid'])){
    $oauth_uid=$_SESSION['oauth_uid'];
    $oauth=Data::QueryData("oauth_users","","id='$oauth_uid'");
    if($oauth["querynum"]!=1){
        Inc::GoUrl(Route::Link("ut-oauth","login"));
    }
}else{
    Inc::GoUrl(Route::Link("ut-oauth","login"));
}