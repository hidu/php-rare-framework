<?php
namespace Rare;

include_once __DIR__."/helper/helper.php";
include_once __DIR__."/Autoload.php";

Autoload::register(Autoload::createAutoloadHandleByNamespace("Rare", __DIR__));

use \Rare\Exception\NotFound as NotFoundException;
class Application{
    
    protected  $appDir;
    
    protected $webRoot="/";
    
    protected $scriptName="index.php";
    protected $requestPathArr=array();
    
    public function __construct($appDir){
        $this->appDir=realpath($appDir);
        define("RARE_APP_DIR", $this->appDir);
    }
    /**
     * @return string
     */
    public function version(){
        return "Rare Framework (2.0.0)";
    }
    
//     /**
//      * @return string
//      */
//     public function getPathInfo(){
//         if(isset($_SERVER["PATH_INFO"])){
//             return '/'.trim($_SERVER["PATH_INFO"],'/');
//         }
        
//         $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
//         return '/'.trim(str_replace('?'.$query, '', $_SERVER['REQUEST_URI']), '/');
//     }
    
    /**
     * @return string
     */
    protected function getMethod()
    {
        if (isset($_REQUEST['_method'])) {
            return strtoupper($_REQUEST['_method']);
        } else {
            return $_SERVER['REQUEST_METHOD'];
        }
    }
    
    public function getServerVal($key,$default=null){
        return isset($_SERVER[$key])?$_SERVER[$key]:$default;
    }
    
    protected function parseRequestUri(){
        //[SCRIPT_NAME] => /rare/index.php/aaa/ad/d
        //[SCRIPT_NAME] => /rare/index.php/aaa/ad/index.php
        $script_name=$this->getServerVal("SCRIPT_NAME","");
        $script_name_arr=explode("/", $script_name);
        $_scriptNamePos=false;
        foreach($script_name_arr as $i=>$v){
            if(rare_strEndWith($v, ".php")){
                $this->scriptName=$v;
                $_scriptNamePos=$i;
                break;
            }
        }
        if($_scriptNamePos===false){
            throw new \Exception("wrong request");
        }
        if($script_name_arr[count($script_name_arr)-1]==$this->scriptName){
            array_pop($script_name_arr);
        }
        
        //实际请求到应用的路径
        $this->requestPathArr=array_slice($script_name_arr, $_scriptNamePos+1);
        //真实的webroot是  /rare_a/demo/
        //[REQUEST_URI] => /rare_a/demo/aaa/ad/d?d=1
        //[REQUEST_URI] => /rare_a/demo/aaa/ad/?d=1
        $request_uri=$this->getServerVal("REQUEST_URI","");
        $path_info= parse_url($request_uri);
        $_path_arr=explode("/",trim($path_info["path"],"/"));
        
        $this->webRoot="/".implode("/", array_slice($_path_arr, 0,count($_path_arr)-count($this->requestPathArr)));
        if(!rare_strEndWith($this->webRoot, "/")){
            $this->webRoot.="/";
        }
        
        if(empty($this->requestPathArr)){
            $this->requestPathArr[]="index";
        }
        
        $this->parseUriWithRouter();
    }
    
    protected function parseUriWithRouter(){
        $router=new Router(rare_app_config("route"), new \Rare\Cache\NoCache());
        $routeInfo=$router->parseUriPath(implode("/", $this->requestPathArr));
        if($routeInfo){
            $this->requestPathArr=$routeInfo["path_arr"];
        }
    }
    
    /**
     * 
     * @param array $requestPathArr
     * @throws NotFoundException
     * @return Action
     */
    protected function getAction($requestPathArr){
        $actionFile=implode(DIRECTORY_SEPARATOR,array_merge(array($this->appDir,"app","action"),$requestPathArr)).".php";
        if(!file_exists($actionFile)){
           throw new NotFoundException("action file [$actionFile] not exists");
        }
        require_once $actionFile;
        $tmp=$requestPathArr;
        array_unshift($tmp, "app","action");
        $classStr=implode("\\", $tmp);
        if(!class_exists($classStr)){
            throw new NotFoundException("action class [{$classStr}] not exists in file [{$actionFile}]");
        }
        $action=new $classStr();
        $tplEng=new PHPTemplate();
        $tplEng->setConfig(array(
            "view_dir"=>rare_pathJoin($this->appDir,"resource","view"),
            "layout_dir"=>rare_pathJoin($this->appDir,"resource","layout"),
            "layout"=>"default",
        ));
        $action->setTemplateEngine($tplEng);
        return $action;
    }
    
    public function error404($msg=''){
        @header($_SERVER["SERVER_PROTOCOL"].' 404');
        $emsg="The requested URL <b>{$_SERVER['REQUEST_URI']}</b> was not found on this server.";
        $emsg.="<hr/>".$msg;
        $this->_errorPage("404 Not Found",$emsg);
    }
    
    protected  function _errorPage($title,$msg){
        echo Util::render(rare_pathJoin(__DIR__,"resource","error.php"), array("title"=>$title,"msg"=>$msg));
    }
    //500错误
    public function error500($_error=''){
        @header($_SERVER["SERVER_PROTOCOL"].' 500');
        $this->_errorPage("500 Internal Server Error", "");
    }
    
    public function run(){
        try{
            $this->parseRequestUri();
            $action=$this->getAction($this->requestPathArr);
            $fn="execute".ucfirst($this->getMethod());
            $ret=$action->$fn();
            if(is_array($ret)){
                header("Content-Type: application/json");
                echo json_encode($ret);
            }
        }catch(NotFoundException $e){
            $this->error404($e->getMessage());
        }catch(\Exception $e){
            $this->error500();
        }
    }

}