<?php
require dirname(dirname(dirname(dirname(__FILE__)))).'/'.'config.php';
use usualtool\Lib\Inc;
use usualtool\Lib\Sockets;
$config=Inc::GetConfig();
$socket=new Sockets($config["SOCKETS_HOST"],$config["SOCKETS_PORT"]);