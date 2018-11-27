<?php
define("CURL_TIMEOUT",      20); 
define("API_SERV",          'http://openapi.youdao.com/'); 
define("API_PATH",          json_encode([
        'trans'         => 'api',
        'sti'           => 'speechtransapi',
        'oti'           => 'ocrtransapi'
    ]));
define("APP_KEY",           "1d20c773390066ce"); //替换为您的应用ID
define("SEC_KEY",           "xOD5kS6Y4T2HZAdeW5zXlSlYnBIjvQDW");//替换为您的密钥