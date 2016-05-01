<?php
namespace Rare\Core;
class Filter{
    protected $app;
    public function __construct($app){
        $this->app=$app;
    }
    
    public function beforeRouter(&$pathInfo){
        
    }
    
    public function afterRouter(&$pathInfo){
        
    }
    
    public function beforeAction($pathInfo){
        
    }
    
    public function afterAction($pathInfo,&$ret){
        
    }
}