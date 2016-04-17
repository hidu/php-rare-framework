<?php
namespace app\action;
class index extends \Rare\Action{
    
    public function executeGet(){
        echo "hello world".time();
        $this->assignLayout("title","index");
        $this->display("index");
    }
}