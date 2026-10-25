<?php
use usualtool\Lib\Inc;
use usualtool\Lib\Data;
/**
 * 在建项目
 */
$app->Runin("task",array(Inc::Auth("task")));
/**
 * AD
 */
$app->Runin("ad",explode("^",explode("|",Inc::Auth("upapi"))[1]));
/**
 * 载入模板
 */
$app->Open("task.cms");