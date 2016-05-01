<?php
namespace Rare;

class Config{
    protected static $configs=array();
    
    /**
     * @param string $name
     * @param string $key
     * @param mixed $default
     * @return mixd
     */
    public static function get($name,$default=null){
        if(empty($name)){
            return $default;
        }
        $keyArr=explode("/",trim($name,"/"));
        $fileName=$keyArr[0];
        if(!isset(self::$configs[$fileName])){
            $configFilePath=rare_pathJoin(RARE_APP_DIR,"config",$fileName.".php");
            if(file_exists($configFilePath)){
                self::$configs[$fileName]=include $configFilePath;
            }else{
                self::$configs[$fileName]=array();
            }
        }
        return rare_array_get(self::$configs, $name,$default);
    }
}