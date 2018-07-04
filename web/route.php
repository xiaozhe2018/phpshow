<?php
//这文件用于生成了html的前提下，替代index.php入口
require './bone/core/bone.php';
header('Content-Type: text/html; charset=utf-8');

$app_config = array(
    'app_title' => 'phpbone',
    'app_name' => '',
    'purview_config' => '',
    'session_start' => false
);

$app = new bone( $app_config );

$app->run();

//test svn
