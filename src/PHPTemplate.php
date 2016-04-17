<?php
namespace Rare;

class PHPTemplate extends Template{

    public function display($tpl,$vars=array(),$tplParams=array()){
        echo $this->render($tpl,$vars,$tplParams);
    }
    
    public function render($tpl,$vars=array(),$tplParams=array()){
        if(empty($vars) && !is_array($vars)){
            $vars=array();
        }
        if(empty($tplParams) && !is_array($tplParams)){
            $tplParams=array();
        }
        $vars=array_merge($this->vars,$vars);
        $viewFilePath=rare_pathJoin($this->getConfig("view_dir"),$tpl).".php";
        $str=Util::render($viewFilePath, $vars);
        
        $layout=isset($tplParams["layout"])?$tplParams["layout"]:null;
        $layoutDefault=$this->getConfig("layout");
        
        $isAjax=rare_isAjax();
        foreach (array($layout,$layoutDefault) as $_layout){
            if($_layout===false){
                return $str;
            }
            if(is_string($_layout)){
                if($isAjax){
                    return $str;
                }else{
                    $layoutFilePath=rare_pathJoin($this->getConfig("layout_dir"),$_layout).".php";
                    $layoutVars=$this->layoutVars;
                    $layoutVars["body"]=$str;
                    return Util::render($layoutFilePath, $layoutVars);
                }
            }
        }
        
        
        
        return $str;
    }
}