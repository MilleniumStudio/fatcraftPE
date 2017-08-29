<?php
namespace hungergames\api\scripts;
use hungergames\lib\utils\exc;
use hungergames\Loader;
class HGAPIScriptManager{
    /** @var HGAPIScript[] */
    protected $scripts = [];
    /** @var Loader */
    private $HGApi;

    public function __construct(Loader $main){
        $this->HGApi = $main;
    }
    /**
     * Loads script
     *
     * @param HGAPIScript $script
     * @return bool
     */
    public function loadScript($script){
        if($script instanceof HGAPIScript){
            $script->onLoad();
            $this->scripts[$script->getName()] = $script;
            return true;
        }else{
            return false;
        }
    }
    /**
     * Loads all scripts
     */
    public function loadScripts(){
        foreach(glob($this->HGApi->dataPath()."scripts/*.php", GLOB_BRACE) as $f){
            /** @noinspection PhpIncludeInspection */
            include_once $f;
            foreach(exc::getFileClasses($f) as $class){
                $class = new $class();
                if(!$class instanceof HGAPIScript) continue;
                if(isset($this->scripts[$class->getName()])) continue;
                $this->loadScript($class);
            }
        }
    }
    /**
     * Reloads all scripts
     */
    public function reloadScripts(){
        $this->scripts = [];
        $this->loadScripts();
    }
    /**
     * returns script by name
     *
     * @param $name
     * @return HGAPIScript|null
     */
    public function getScript($name){
        if(isset($this->scripts[$name])){
            return $this->scripts[$name];
        }
        return null;
    }
    /**
     * returns all loaded scripts
     *
     * @return HGAPIScript[]
     */
    public function getScripts(){
        return $this->scripts;
    }
}