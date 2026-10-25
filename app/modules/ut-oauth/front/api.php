<?php
use usualtool\Lib\Data;
use usualtool\Oauth\Oauth;
$scope=$_POST['scope'];
$where=$_POST['where'];
$order=$_POST['order'];
$limit=$_POST['limit'];
if(preg_match('/\b(union|select|insert|update|delete|drop|alter|truncate|outfile|load_file|information_schema)\b|--|\/\*|\*\/|#|;|\bsleep\s*\(|\bbenchmark\s*\(/i',$where)){
    http_response_code(400);
    echo json_encode(['error' => 'Illegal where']);
    exit;
}
if(!empty($order) && preg_match('/[^a-zA-Z0-9_,\.\s]/',$order)){
    http_response_code(400);
    echo json_encode(['error' => 'Illegal order']);
    exit;
}
if(!empty($limit) && !preg_match('/^\d+(\s*,\s*\d+)?$/',$limit)){
    http_response_code(400);
    echo json_encode(['error' => 'Illegal limit']);
    exit;
}
if(!$scope){
    http_response_code(400);
    echo json_encode(['error' => '没有传递必要参数scope']);
    exit;
}
$oauth=new Oauth();
$token=$oauth->Validate();
$scope_arr=isset($token['scope']) ? explode(' ',$token['scope']) : [];
if(!in_array($scope,$scope_arr)){
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient scope: "'.$scope.'" required']);
    exit;
}else{
    $dbtable=Data::QueryData("oauth_scopes","","scope='$scope'")["querydata"][0]["dbtable"];
    $data=Data::QueryData($dbtable,"",$where,$order,$limit);
}
echo json_encode($data);