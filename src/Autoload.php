<?php
namespace Rare;

use Rare\Exception\Autoload as AutoLoadException;

class Autoload{
    protected static $mapping=array();
    protected static $registered=false;
    protected static $handlers=array();
    
    /**
     * 自动加载注册
     * @param function $handle
     * @throws AutoLoadException
     */
    public static function register($handle=null) {
        if(!is_null($handle)){
            if(!is_callable($handle)){
                throw new AutoLoadException("handle must callbale");
            }
            self::$handlers[]=$handle;
        }
        
        if (self::$registered){
            return;
        }
        if (false === spl_autoload_register(__NAMESPACE__."\Autoload::autoload")){
           throw new AutoLoadException("register autoload failed");
        }
        self::$registered = true;
    }
    
    /**
     * 添加文件类名和路径的映射关系
     * @param string $class
     * @param string $filePath
     */
    public  static function addClassMapping($class,$filePath=null){
        if(is_array($class) && is_null($filePath)){
            self::$mapping=array(self::$mapping,$class);
        }else{
            self::$mapping[$class]=$filePath;
        }
    }
    
    /**
     * 实际的自动加载逻辑
     * @param string $class
     * @return boolean
     */
    public static function autoload($class){
        if(class_exists($class, false) || interface_exists($class, false)){
            return true;
        }
        if(PHP_VERSION_ID>=50400 && trait_exists($class,false)){
            return true;
        }
        
        if (self::$mapping && isset(self::$mapping[$class]) ){
            $filePath=self::$mapping[$class];
            if(!$filePath || !file_exists($filePath)){
                return false;
            }
            require_once ($filePath);
            return true;
        }
        
        foreach (self::$handlers as $fn){
            if($fn($class)){
                return true;
            }
        }
    }
    
    /**
     * 创建一个按照namespace 自动加载的handler
     * @param string $rootNamespace 根namespace
     * @param string $rootDir 代码所在目录
     * @return function
     */
    public static function createAutoloadHandleByNamespace($rootNamespace,$rootDir){
        
        return function($class) use($rootNamespace,$rootDir){
            $arr=explode("\\", $class);
            if($arr[0]!=$rootNamespace){
                return false;
            }
            $filePath=rare_pathJoin($rootDir,array_slice($arr,1)).".php";
            if(file_exists($filePath)){
                require_once $filePath;
                return true;
            }
            return false;
        };
    }
}

