<?php 

    namespace _Frm_core;

    class Router{
        public $request;
        public $response;

        protected $routes = [];
        protected $method = 'index';

        public function __construct($request,$response){
            $this->request = $request;
            $this->response = $response;

            Application::$app->add_singleton("req", function(){return $this->request;});
            Application::$app->add_singleton("res", function(){return $this->response;});
        }

        public function get($path, $callback){
            $this->routes['get'][$path]= $callback;
        }

        public function put($path, $callback){
            $this->routes['put'][$path]= $callback;
        }

        public function post($path, $callback){
            $this->routes['post'][$path]= $callback;
        }

        public function delete($path, $callback){
            $this->routes['delete'][$path]= $callback;
        }

        public function resolve(){
            $path = $this->request->getPath();
            $method =  $this->request->getMethod();
            $callback = $this->routes[$method][$path] ?? false;

            if($callback === false){
                $this->response->setStatusCode(404);
                return $this->renderView("_404");
            }

            if(is_string($callback)){
                return $this->renderView($callback);
            }

            if(is_array($callback)){
                Application::$app->controller = new $callback[0]();
                if(count($callback) == 1){
                    array_push($callback,'index');
                }
                $callback[0] = Application::$app->controller;
            }

            $callback_params = $this->getParamsForCallback($callback);
            return call_user_func($callback, ...$callback_params);
        }

        public function getParamsForCallback($callback){
            // Get information about the method using ReflectionMethod
            $reflection = null;
            if(is_array($callback))
                $reflection = new \ReflectionMethod($callback[0], $callback[1]);
            else $reflection = new \ReflectionFunction($callback);

            // Get the parameters of the method
            $params = $reflection->getParameters();

            $result = array();
            $request_params = $this->request->getBody();
            $services = Application::$app->get_services();
            // Loop through the parameters and get their names
            foreach ($params as $param) {
                $p_name = $param->getName();

                if(array_key_exists($p_name, $services)){
                    $service = $services[$p_name];
                    if($service->is_singleton)
                        array_push($result, $service->dependency);
                    else array_push($result, $service->dependency());
                }

                if(array_key_exists($p_name, $request_params)){
                    array_push($result, $request_params[$p_name]);
                }
            }
            return $result;
        }

        public function renderView($view, $params = []){
            $layoutContent = $this->getLayoutContent();
            $viewContent = $this->getViewContent($view, $params);
            $layoutContent = str_replace("{{scripts}}", Application::$app->getScripts(), $layoutContent);
            return str_replace("{{content}}", $viewContent, $layoutContent);
        }

        public function getLayoutContent(){
            $layout = Application::$app->controller->getLayout();
            ob_start();
            include_once "app/layouts/$layout.php";
            return ob_get_clean();
        }

        public function getViewContent($view, $params = []){
            if(file_exists("app/views/".$view.".php")){
                if(count($params) > 0){
                    foreach ($params as $key => $value) {
                        $$key = $value;
                    }
                }
                ob_start();
                include_once "app/views/".$view.".php";
                return ob_get_clean();
            }else return $view;
        }
    }
?>