<?php
namespace  Rare;
abstract class Action{
    protected $layout="default";
    /**
     * @var Template
     */
    protected $templateEngine;
    
    public function setTemplateEngine(Template $tplEng){
        $this->templateEngine=$tplEng;
    }
    
    public function assign($key,$val=null){
        $this->templateEngine->assign($key,$val);
    }
    
    public function assignLayout($key,$val=null){
        $this->templateEngine->assignLayout($key,$val);
    }
    
    public function display($tpl,$vars=array(),$tplParams=array()){
        return $this->templateEngine->display($tpl,$vars,$tplParams);
    }
    
    public function preExecute(){
        
    }
    
    
}
