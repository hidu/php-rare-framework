<?php
namespace App\Action\User;
class InfoAction extends \Rare\Action{
    public function executeGet(){
        $this->tpl->assignLayout("title","index");
        return $this->tpl->render("user/info");
    }
}