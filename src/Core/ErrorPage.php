<?php
namespace Rare\Core;
class ErrorPage{
    /**
     * @param string $title
     * @param string $msg
     */
    public function displayError($title,$msg){
        echo \Rare\Core\Util::render(rare_pathJoin(dirname(__DIR__),"resource","error.php"), array("title"=>$title,"msg"=>$msg));
    }
    
    /**
     * @param string $msg
     */
    public function error404($msg=''){
        @header($_SERVER["SERVER_PROTOCOL"].' 404');
        $emsg="The requested URL <b>{$_SERVER['REQUEST_URI']}</b> was not found on this server.";
        $emsg.="<hr/>".$msg;
        $this->displayError("404 Not Found",$emsg);
    }
    
    /**
     * 500错误
     * @param string $_error
     */
    public function error500($_error=''){
        @header($_SERVER["SERVER_PROTOCOL"].' 500');
        $this->displayError("500 Internal Server Error", "");
    }
}