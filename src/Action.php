<?php
namespace  Rare;
abstract class Action{
    /**
     * @var \Rare\Core\Template
     */
    protected $tpl;
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
        $this->tpl=$tplEng;
    }
    
    public function preExecute(){
        
    }
    
    
}
