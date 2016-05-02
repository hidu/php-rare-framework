<?php
namespace App\Action;
class IndexAction extends \Rare\Action{
    public function executeGet(){
        $this->tpl->assignLayout("title","index");
        return $this->tpl->render("index");
    }
}