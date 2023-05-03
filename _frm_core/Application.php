<?php 
    namespace _Frm_core;
    
    class Application{
        public $baseDir;
        public $basePath;
        public $router;
        public $request;
        public $response;
        public $controller;
        public $scripts = [];
        private $services = [];

        public function get_services() : array {
            return $this->services;
        }

        public function add_scop($key, $callback){
            $service = new \stdClass;
            $service->is_singleton = false;
            $service->key = $key;
            $service->dependency = $callback;
            $this->services[$service->key] = $service;
        }

        public function add_singleton($key, $callback){
            $service = new \stdClass;
            $service->is_singleton = true;
            $service->key = $key;
            $service->dependency = $callback();
            $this->services[$service->key] = $service;
        }

        public static $app;

        public function __construct($dir){
            self::$app = $this;
            $this->controller = new Controller;
            $this->baseDir = $dir;
            //$this->basePath = str_replace("home/doron/domains/david.systal.co.il/public_html/","",$dir);
            $this->basePath = "";
            $this->request = new Request;
            $this->response = new Response;
            $this->router = new Router($this->request, $this->response);
        }

        public function run(){
            echo $this->router->resolve();
        }

        public function addScript($script){
            array_push($this->scripts, $this->basePath.$script);
        }

        public function getScripts(){
            $scriptsText = "";
            foreach ($this->scripts as $script) {
                $scriptsText .= '<script src='.$script.'></script>';
            }
            return $scriptsText;
        }

    }
?>