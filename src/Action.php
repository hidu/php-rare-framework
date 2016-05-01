<?php
namespace  Rare;
abstract class Action{
    protected $layout="default";
    /**
     * @var \Rare\Core\Template
     */
    protected $templateEngine;
    /**
     * 
     * @var Application
     */
    protected $app;
    
    /**
     * 
     * @param Application $app
     */
    public function __construct(Application $app){
        $this->app=$app;
    }
    
    public function setTemplateEngine(\Rare\Core\Template $tplEng){
        $this->templateEngine=$tplEng;
    }
    
    public function assign($key,$val=null){
        $this->templateEngine->assign($key,$val);
    }
    
    public function assignLayout($key,$val=null){
        $this->templateEngine->assignLayout($key,$val);
    }
    
    public function render($tpl,$vars=array(),$tplParams=array()){
        return $this->templateEngine->render($tpl,$vars,$tplParams);
    }
    
    public function preExecute(){
        
    }
    
    
}
