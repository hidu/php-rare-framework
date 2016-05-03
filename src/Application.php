<?php
namespace Rare;

include_once __DIR__."/helper/helper.php";
include_once __DIR__."/Core/Autoload.php";
use Rare\Core\Autoload;

Autoload::register(Autoload::createAutoloadHandleByNamespace("Rare", __DIR__));

use \Rare\Exception\NotFound as NotFoundException;
use Rare\Exception\Exception;
class Application{
    
    protected  $appDir;
    
    protected $pathInfo=array();
    
    protected $filter;
    
    protected $config=array();
    
    /**
     * @var \Rare\Core\Template
     */
    protected $tplEng;
    
    /**
     * @var \Rare\Core\ErrorPage
     */
    protected $errorPage;
    
    protected static $appInstance;
    
    /**
     * 
     * @var \Rare\Core\Router
     */
    protected $router;
    
    /**
     * @param string $appDir
     */
    public function __construct($appDir){
        $this->appDir=realpath($appDir);
        define("RARE_APP_DIR", $this->appDir);
        $this->init();
        self::$appInstance=$this;
    }
    
    protected function init(){
        Autoload::register(Autoload::createAutoloadHandleByNamespace("App", rare_pathJoin($this->appDir,"App")));
        
        $this->filter=new \Rare\Core\Filter($this);
        $this->errorPage=new \Rare\Core\ErrorPage();
        
        $routeConf=rare_app_config("/route/",array());
        $routeCache=empty($routeConf["cache"])?new \Rare\Cache\NoCache():$routeConf["cache"];
        $this->router=new \Rare\Core\Router(empty($routeConf["route"])?array():$routeConf["route"], $routeCache);
    }
    
    public static function getInstance(){
        if(empty(self::$appInstance)){
            throw new \Rare\Exception\Exception("app not init");
        }
        return self::$appInstance;
    }
    
    /**
     * @return string
     */
    public function version(){
        return "Rare Framework (2.0.0)";
    }
    
    
    /**
     * @param string $key
     * @param mixd $default
     * @return mixd
     */
    public function getServerVal($key,$default=null){
        return isset($_SERVER[$key])?$_SERVER[$key]:$default;
    }
    
    /**
     * @return string
     */
    protected function getUri(){
        if(isset($_SERVER["PATH_INFO"])){
            return $_SERVER["PATH_INFO"];
        }
        $tmp=explode("/", $_SERVER["SCRIPT_NAME"]);
        $arr=array();
        $flag=false;
        $scriptName="";
        foreach ($tmp as $i=>$v){
            if(!$flag && rare_strEndWith($v, ".php")){
                $flag=true;
                $scriptName=$v;
            }
            if($flag && $scriptName!=$v){
                $arr[]=$v;
            }
        }
        $str=implode("/", $arr);
        return "/".ltrim($str,"/");
    }
    
    protected function getWebRoot(){
        $tmp=explode("?",$_SERVER["REQUEST_URI"]);
        $webRoot=$tmp[0];
        $arr=explode("/", $webRoot);
        foreach ($arr as $i=>$v){
            if(rare_strEndWith($v, ".php")){
                $tmp=array_slice($arr, 0,$i);
                $webRoot=implode("/", $tmp);
                break;
            }
        }
        if($this->pathInfo["uri"]!="/"){
            $webRoot=substr($webRoot, 0,strlen($webRoot)-strlen($this->pathInfo["uri"]));
        }
        
        $subStr="/".$this->pathInfo["script_name"];
        if(rare_strEndWith($webRoot, $subStr)){
            $webRoot=substr($webRoot, 0,strlen($webRoot)-strlen($subStr));
        }
        return rtrim($webRoot,"/")."/";
    }
    
    /**
     */
    protected function getScriptName(){
        foreach (array("DOCUMENT_URI","SCRIPT_NAME") as $name){
            if(isset($_SERVER[$name])){
                $tmpArr=explode("/",$_SERVER[$name]);
                foreach($tmpArr as $i=>$v){
                    if(rare_strEndWith($v, ".php")){
                         return $v;
                    }
                }
            }
        }
        return false;
    }
    /**
     * 获取请求路径相关信息
     * @throws \Exception
     */
    protected function parsePathInfo(){
        $this->pathInfo=array(
            "uri"=>$this->getUri(),
            "script_name"=>$this->getScriptName(),
            "web_root"=>false,
            "action"=>false,
        );
        
        if(empty($this->pathInfo["script_name"])){
            throw new \Exception("wrong request");
        }
        $this->pathInfo["web_root"]=$this->getWebRoot();
        
        $_pathInfo=pathinfo($this->pathInfo["uri"]);
        $_action=trim($_pathInfo["dirname"]."/".$_pathInfo["filename"],"/");
        if(empty($_action)){
            $_action="index";
        }
        $this->pathInfo["action"]=implode("/", array_map("ucfirst", explode("/", $_action)));
    }
    
    /**
     * 路由解析
     * @param array $pathInfo
     * @return array
     */
    protected function parseUriWithRouter($pathInfo){
        $tmp=explode("?", $pathInfo["uri"],2);
        $routeInfo=$this->router->parseUriPath($tmp[0]);
        if($routeInfo){
            $pathInfo["action"]=$routeInfo["action"];
        }
        return $pathInfo;
    }
    /**
     * 解析请求
     */
    protected function parseRequest(){
        $this->parsePathInfo();
        
        $this->filter->beforeRouter($this->pathInfo);
        
        $this->pathInfo=$this->parseUriWithRouter($this->pathInfo);
        
        $this->filter->afterRouter($this->pathInfo);
    }
    
    /**
     * @return \Rare\Core\Template
     */
    protected function getTplEng(){
        if(empty($this->tplEng)){
            $this->tplEng=new \Rare\Core\Template();
            $resourceDir=rare_app_config("/app/resource_dir",rare_pathJoin($this->appDir,"resource"));
            $this->tplEng->setConfig(array(
                "view_dir"=>rare_pathJoin($resourceDir,"view"),
                "layout_dir"=>rare_pathJoin($resourceDir,"layout"),
                "layout"=>"default",
            ));
        }
        return $this->tplEng;
    }
    
    /**
     * 
     * @param string $actionName
     * @throws NotFoundException
     * @return Action
     */
    protected function getAction($actionName){
        $tmp=explode("@",$actionName);
        $requestPathArr=explode("/", $tmp[0]);
        $tmp=$requestPathArr;
        array_unshift($tmp, "App","Action");
        $len=count($tmp);
        $tmp[$len-1].="Action";
        $tmp=array_map("ucfirst", $tmp);
        $classStr=implode("\\", $tmp);
        if(!class_exists($classStr)){
            throw new NotFoundException("action class [{$classStr}] not exists");
        }
        $action=new $classStr($this);
        $action->setTemplateEngine($this->getTplEng());
        return $action;
    }
    
    /**
     * 404错误
     * @param string $msg
     */
    public function error404($msg=''){
        return $this->errorPage->error404($msg);
    }
    
    /**
     * 500错误
     * @param string $_error
     */
    public function error500($_error=''){
       $this->errorPage->error500();
    }
    
    /**
     * 
     * @param string $actionName
     * @throws NotFoundException
     */
    public function runAction($actionName){
        $action=$this->getAction($actionName);
        $tmp=explode("@", $actionName);
        $fn=empty($tmp[1])?"execute":$tmp[1];
        if(!method_exists($action, $fn)){
            throw new NotFoundException("method execute not exists");
        }
        return $action->$fn();
    }
    
    /**
     * 执行应用
     * @return boolean
     */
    public function run(){
        try{
            $this->parseRequest();
            $action=$this->getAction($this->pathInfo["action"]);
            $tmp=explode("@", $this->pathInfo["action"]);
            $fn=empty($tmp[1])?"execute":$tmp[1];
            $method=strtolower($_SERVER["REQUEST_METHOD"]);
            $fnRest=$fn.ucfirst($method);
            //默认去执行 executePost executeGet 这样的请求
            if(method_exists($action, $fnRest)){
                $fn=$fnRest;
            }
            if(!method_exists($action, $fn)){
                $this->error404("method {$fn} not exists");
                return false;
            }
            header("Content-Type: text/html;charset=".rare_app_config("/app/charset","utf-8"));
            $this->filter->beforeAction($this->pathInfo);
            $ret=$action->$fn();
            
            $this->filter->afterAction($this->pathInfo,$ret);

            if(is_array($ret)){
                header("Content-Type: application/json");
                echo json_encode($ret);
            }else if (is_string($ret)){
                echo $ret;
            }
        }catch(NotFoundException $e){
            $this->error404($e->getMessage());
        }catch(\Exception $e){
            $this->error500();
        }
    }
    
    /**
     * 设置应用的过滤器
     * @param \Rare\Core\Filter $filter
     */
    public function setFilter(\Rare\Core\Filter $filter){
        $this->filter=$filter;
    }
    
    /**
     * 设置异常处理handler
     * @param \Rare\Core\ErrorPage $errorPage
     */
    public function setErrorPage(\Rare\Core\ErrorPage $errorPage){
        $this->errorPage=$errorPage;
    }
    /**
     * 设置修改默认的模板引擎
     * @param \Rare\Core\Template $tpl
     */
    public function setTplEng(\Rare\Core\Template $tpl){
        $this->tplEng=$tpl;
    }
    
    /**
     * 
     * @return array
     */
    public function getPathInfo(){
        return $this->pathInfo;
    }
    
    /**
     * @return \Rare\Core\Router
     */
    public function getRouter(){
        return $this->router;
    }
}
