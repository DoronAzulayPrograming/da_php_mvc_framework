<?php 

    namespace DafCore;

    class Router{
        public $request;
        public $response;

        protected $routes = [];
        protected $method = 'index';

        protected $global_middlewheres = [];

        public function __construct($request,$response){
            $this->request = $request;
            $this->response = $response;
        }

        public function use($callback){
            $this->global_middlewheres[] = $callback;
        }

        public function get($path, ...$callback){
            $this->routes['get'][$path]= $callback;
        }

        public function put($path, ...$callback){
            $this->routes['put'][$path]= $callback;
        }

        public function post($path, ...$callback){
            $this->routes['post'][$path]= $callback;
        }

        public function delete($path, ...$callback){
            $this->routes['delete'][$path]= $callback;
        }

        public function resolve_middlewheres($middlewheres){
            $status = true;
            foreach ($middlewheres as $middlewhere) {
                $func = $middlewhere;
                $middlewhere_params = [];

                $len = count($func);
                if($len > 2){
                    $middlewhere_params = array_pop($func);
                }
                else if($len == 2){
                    if(is_array($func[0])){
                        $middlewhere_params = array_pop($func);
                        $func = $func[0];
                    }
                }

                $func_params = $this->getParamsForCallback($func);
                if(count($middlewhere_params) > 0)
                    array_push($func_params, $middlewhere_params);
                    
                try {
                    if($func(...$func_params) === false) {
                        $status = false;
                        break;
                    }
                } catch (\Throwable $th) {
                    $this->response->badRequest($th->getMessage());
                    $status = false;
                    break;
                }
            }

            return $status;
        }

        public function resolve(){
            $path = $this->request->getPath();
            $method =  $this->request->getMethod();
            $middlewheres = $this->routes[$method][$path] ?? false;

            if($middlewheres === false){
                return $this->response->notFound();
            }
            
            //middlewheres
            $callback = array_pop($middlewheres);
            $middlewheres = array_merge($this->global_middlewheres, $middlewheres);
            if(!$this->resolve_middlewheres($middlewheres)){
                return " ";
            }

            //end point
            if(is_string($callback)){
                return $this->renderView($callback);
            }

            if(is_array($callback)){
                $class_params = $this->getParamsForCallback($callback[0]);
                if(count($class_params) > 0)
                    Application::$app->controller = new $callback[0](...$class_params);
                else 
                    Application::$app->controller = new $callback[0]();
                if(count($callback) == 1){
                    array_push($callback,'index');
                }
                $callback[0] = Application::$app->controller;
            }

            //end point - parameters
            $func_params = $this->get_callback_parameters($callback);
            $callback_params = $this->get_dependencies($func_params);

            $func_params_len = count($func_params);
            $callback_params_len = count($callback_params);

            //end point - validation request
            if($callback_params_len !== $func_params_len){
                return $this->response->badRequset("$callback_params_len passed and exactly $func_params_len expected");
            }

            //$callback_params = $this->getParamsForCallback($callback);
            // run end point
            try {
                return call_user_func($callback, ...$callback_params);
            } catch (\Throwable $th) {
                return $this->response->badRequset($th->getMessage());
            }
        }

        public function get_callback_parameters($callback) : array {
            // Get information about the method using ReflectionMethod
            $reflection = null;
            if(is_string($callback)){
                $reflaction = new \ReflectionClass($callback);
                if(!$reflaction) return [];
                $constractor = $reflaction->getConstructor();
                if(!$constractor) return [];
                return $constractor->getParameters();
            }
            if(is_array($callback))
                $reflection = new \ReflectionMethod($callback[0], $callback[1]);
            else $reflection = new \ReflectionFunction($callback);
            // Get the parameters of the method
            return $reflection->getParameters();
        }

        public function get_dependencies($params){
            $result = array();
            $request_params = $this->request->getBody();
            $services = Application::$app->services;
            foreach ($params as $param) {
                $p_name = $param->getName();
                
                if($services->serviceExists($p_name)){
                    array_push($result, $services->getService($p_name));
                }

                if(array_key_exists($p_name, $request_params)){
                    array_push($result, $request_params[$p_name]);
                }
            }
            return $result;
        }
        
        public function getParamsForCallback($callback){
            $params = $this->get_callback_parameters($callback);
            return $this->get_dependencies($params);
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
            if(file_exists("app/views/_layouts/$layout.php"))
                include_once "app/views/_layouts/$layout.php";
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