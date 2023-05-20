<?php 

    namespace DafCore;
    
    class Request{

        private $data = [];
    
        public function __set($name, $value) {
            $this->data[$name] = $value;
        }
    
        public function __get($name) {
            return $this->data[$name] ?? null;
        }
        
        public function getPath(){
            $path = $_SERVER['REQUEST_URI']; // ?? '/'; // $_SERVER['REQUEST_URI'] $_GET['url']
            $path = str_replace(Application::$app->baseDir, "", $path);
            $path = str_replace("//", "/", $path);

            $position = strpos($path,'?');
            if($position !== false){
                $path = substr($path, 0, $position);
            }

            if (strlen($path) > 1 && substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }
            
            return $path;
        }

        public function getMethod(){
            return strtolower($_SERVER['REQUEST_METHOD']);
        }

        public function getParams(){
            $params = [];
            foreach($_GET as $key => $value){
                $params[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            return $params;
        }

        public function getPut(){
            return json_decode(file_get_contents("php://input"));
        }

        public function getPost(){
            $params = [];
            if(!count($_POST)){
                return json_decode(file_get_contents("php://input"));
            }
            foreach($_POST as $key => $value){
                $params[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            return $params;
        }

        public function getBody(){
            $method = $this->getMethod();
            $body = [];
            if($method === 'post'){
                $body = (object)$this->getPost();
            }
            else if($method === 'put'){
                $body = $this->getPut();
            }
            return $body;
        }

        public function isGet(){
            return $this->getMethod() == "get";
        }
        
        public function isPost(){
            return $this->getMethod() == "post";
        }
    
        public function parseUri(){
            return filter_var(rtrim($_GET['url'],'/'), FILTER_SANITIZE_URL);
        }
    
        public function parseUrl(){
            if(isset($_GET['url'])){
                return explode('/',filter_var(rtrim($_GET['url'],'/'), FILTER_SANITIZE_URL));
            }
        }
    
        public function parseUrlPath($path){
            if(isset($path)){
                $url = str_replace($path,"",$_GET['url']);
                return explode('/',filter_var(rtrim($url,'/'), FILTER_SANITIZE_URL));
            }
        }
    }
?>