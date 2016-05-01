<?php
namespace App\Action;
class Index extends \Rare\Action{
    
    public function executeGet(){
        $this->assignLayout("title","index");
        return $this->render("index");
    }
}