<?php
namespace Rare;
use Rare\Exception\Exception;

class Util{
    /**
     * 将数据渲染到模板中去
     * @param string $viewFile 模板文件路径
     * @param array $vars
     * @return string
     */
    public static function render($viewFilePath,$vars){
        $viewFile=realpath($viewFilePath);
        if(!$viewFile){
            $viewFile=$viewFilePath;
        }
        if(!file_exists($viewFile)){
            throw new Exception("tpl file [{$viewFilePath}] not exists");
        }
        $rare_currentPWD = getcwd();
        chdir(dirname($viewFile));
        if(is_string($vars)){
            parse_str($vars,$vars);
        }
        $vars['rare_vars']=$vars;
        if(is_array($vars)){
            extract($vars);
        }
        ob_start();
        include $viewFile;
        $content= ob_get_contents();
        ob_end_clean();
        chdir($rare_currentPWD);
        return $content;
    }
}