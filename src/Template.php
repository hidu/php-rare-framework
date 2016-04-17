<?php
namespace Rare;
abstract class Template{
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
    
    public abstract  function display($tpl,$vars=array(),$tplParams=array());
}


