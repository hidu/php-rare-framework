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
     * 获取请求路径相关信息
     * @throws \Exception
     */
    protected function parsePathInfo(){
        $result=array(
            "script_name"=>"index.php",
            "action"=>"index",
            "uri"=>"/",
            "web_root"=>"/",
            "suffix"=>"",
        );
        
        //[SCRIPT_NAME] => /rare/index.php/aaa/ad/d
        //[SCRIPT_NAME] => /rare/index.php/aaa/ad/index.php
        $script_name=$this->getServerVal("SCRIPT_NAME","");
        $script_name_arr=explode("/", $script_name);
        $_scriptNamePos=false;
        foreach($script_name_arr as $i=>$v){
            if(rare_strEndWith($v, ".php")){
                $result["script_name"]=$v;
                $_scriptNamePos=$i;
                break;
            }
        }
        if($_scriptNamePos===false){
            throw new \Exception("wrong request");
        }
        if($script_name_arr[count($script_name_arr)-1]==$result["script_name"]){
            array_pop($script_name_arr);
        }
        
        //实际请求到应用的路径
        $_path_arr=array_slice($script_name_arr, $_scriptNamePos+1);
        //真实的webroot是  /rare_a/demo/
        //[REQUEST_URI] => /rare_a/demo/aaa/ad/d?d=1
        //[REQUEST_URI] => /rare_a/demo/aaa/ad/?d=1
        $request_uri=$this->getServerVal("REQUEST_URI","");
        $path_info= parse_url($request_uri);
        $_real_path_arr=explode("/",trim($path_info["path"],"/"));
        
        $_baseName=pathinfo($path_info["path"],PATHINFO_BASENAME);
        $_potPos=strpos($_baseName, ".");
        $result["suffix"]=$_potPos!==false?substr($_baseName, $_potPos+1):"";
        //when file name is a.tar.gz   suffix is tar.gz 
        
        $result["web_root"]="/".implode("/", array_slice($_real_path_arr, 0,count($_real_path_arr)-count($_path_arr)));
        if(!rare_strEndWith($result["web_root"], "/")){
            $result["web_root"].="/";
        }
        $result["uri"]="/".ltrim(substr($request_uri, strlen($result["web_root"])));
        $result["action"]=$_path_arr?implode("/", $_path_arr):"index";
        return $result;
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
        $this->pathInfo=$this->parsePathInfo();
        
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
