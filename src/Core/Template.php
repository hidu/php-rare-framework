<?php
namespace Rare\Core;
class Template{
    protected $vars=array();
    protected $layoutVars=array();
    protected $config=array();
    
    public function setConfig($key,$val=null){
        if(is_array($key) && is_null($val)){
            $this->config=array_merge($this->config,$key);
        }else{
            $this->config[$key]=$val;
        }
    }
    
    public function setLayout($name){
       $this->setConfig("layout",$name);
    }
    
    public function getConfig($key,$default=null){
        return isset($this->config[$key])?$this->config[$key]:$default;
    }
    
    public function assign($key,$val=null){
        if(is_array($key) && is_null($val)){
            $this->vars=array_merge($this->vars,$key);
        }else{
            $this->vars[$key]=$val;
        }
    }
    public function assignLayout($key,$val=null){
        if(is_array($key) && is_null($val)){
            $this->layoutVars=array_merge($this->layoutVars,$key);
        }else{
            $this->layoutVars[$key]=$val;
        }
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
        $str=\Rare\Util\Tpl::render($viewFilePath, $vars);
        
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
                    return \Rare\Util\Tpl::render($layoutFilePath, $layoutVars);
                }
            }
        }
        
        return $str;
    }
}


