<?php 

    namespace DafCore;
    
    class Request{
        public function getPath(){
            $path = $_GET['url']; // ?? '/'; // $_SERVER['REQUEST_URI'] $_GET['url']
            //$path = str_replace("mvc/","",$path);
            $position = strpos($path,'?');
            if($position === false){
                return $path;
            }
            return substr($path, 0, $position);
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
            foreach($_POST as $key => $value){
                $params[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            return $params;
        }

        public function getBody(){
            $method = $this->getMethod();
            $body = [];
            if($method === 'get'){
                $body = $this->getParams();
            }
            else if($method === 'post'){
                $body = $this->getPost();
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