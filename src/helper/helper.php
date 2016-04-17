<?php

include_once __DIR__.DIRECTORY_SEPARATOR."common.php";

function url($path){
    
}

/**
 * 输出web 相对根目录的地址
 * 如 public_path('js/hello.js') 输出为http://127.0.0.1/appName/js/hello.js
 * @param string $uri
 */
function public_path($uri,$full=false){
}



function rare_render($file,$vars=array()){
    return \Rare\Util::render($file, $vars);
}