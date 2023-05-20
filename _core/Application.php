<?php 
    namespace DafCore;
    
    class Application{
        public $baseDir;
        public $router;
        public $request;
        public $response;
        public $controller;
        public $services = [];
        public $scripts = [];
        private $autoload_dirs = [];

        public static $app;

        public function __construct($dir = ""){
            self::$app = $this;
            
            $this->services = new ServiceCollection;
            $this->services->addSingleton("req", function(){return new Request;});
            $this->services->addScop("res", function(){return new Response;});
            $this->services->addScop("body", function($x){return $x->getService("req")->getBody();});

            $this->controller = new Controller;
            $this->baseDir = $dir;
            $this->request = $this->services->getService("req");
            $this->response = $this->services->getService("res");
            $this->router = new Router($this->request, $this->response);
        }

        public function run(){
            echo $this->router->resolve();
        }

        public function addScript($script){
            array_push($this->scripts, $script);
        }

        public function getScripts(){
            $scriptsText = "";
            foreach ($this->scripts as $script) {
                $scriptsText .= '<script src='.$script.'></script>';
            }
            return $scriptsText;
        }

        public function get_autoload_dirs(){
            return $this->autoload_dirs;
        }

        public function autoload_dirs($dirs){
            $this->autoload_dirs = array_merge($this->autoload_dirs, $dirs);
        }

    }
?>