<?php 

    namespace DafCore;

    class Router{
        public $request;
        public $response;
        private $route_params = [];

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
                else if($len == 2  && is_array($func[0])){
                    $middlewhere_params = array_pop($func);
                    $func = $func[0];
                }

                $func_params = $this->getParamsForCallback($func);
                if(count($middlewhere_params) > 0)
                    array_push($func_params, $middlewhere_params);
                    
                try {
                    if(call_user_func_array($func, $func_params) === false) {
                        $status = false;
                        break;
                    }
                } catch (\Throwable $th) {
                    $this->badRequest($th->getMessage());
                    $status = false;
                    break;
                }
            }

            return $status;
        }

        public function getRoute($method, $path)
        {
            if (isset($this->routes[$method][$path])) {
                return $this->routes[$method][$path];
            }
        
            foreach ($this->routes[$method] as $key => $value) {
                if (strpos($key, ":") !== false) {
                    $keySegments = explode("/", $key);
                    $pathSegments = explode("/", $path);
                    $routeParams = [];
        
                    if (count($keySegments) === count($pathSegments)) {
                        $match = true;
                        foreach ($keySegments as $index => $segment) {
                            if (strpos($segment, ":") === 0) {
                                $paramKey = substr($segment, 1);
                                $routeParams[$paramKey] = $pathSegments[$index];
                            } elseif ($segment !== $pathSegments[$index]) {
                                $match = false;
                                break;
                            }
                        }
        
                        if ($match) {
                            $this->route_params = $routeParams;
                            return $value;
                        }
                    }
                }
            }
        
            return false;
        }

        public function resolve() {
            // Resolve global middlewheres
            if (!$this->resolve_middlewheres($this->global_middlewheres)) {
                return " ";
            }
        
            $path = $this->request->getPath();
            $method = $this->request->getMethod();
            $middlewheres = $this->getRoute($method, $path);
        
            if ($middlewheres === false) {
                return $this->notFound("the url=$path  request not found");
            }
        
            // Pop endpoint callback from middlewheres
            $callback = array_pop($middlewheres);
        
            // Resolve middlewheres
            if (!$this->resolve_middlewheres($middlewheres)) {
                return " ";
            }

            // Endpoint - start here
            // Render view for string callback
            if (is_string($callback)) {
                return $this->renderView($callback);
            }
        
            // Resolve controller and method for array callback
            if (is_array($callback)) {
                $class_params = $this->getParamsForCallback($callback[0]);
                Application::$app->controller = count($class_params) > 0 ? new $callback[0](...$class_params) : new $callback[0]();
        
                if (count($callback) == 1) {
                    array_push($callback, 'index');
                }
        
                $callback[0] = Application::$app->controller;
            }
        
            // Endpoint - Parameters
            $func_params = $this->get_callback_parameters($callback);
            $callback_params = $this->get_dependencies($func_params);
        
            $func_params_len = count($func_params);
            $callback_params_len = count($callback_params);
        
            // Endpoint - Validate Request
            if ($callback_params_len !== $func_params_len) {
                return $this->badRequest("$callback_params_len passed and exactly $func_params_len expected");
            }
        
            // Run Endpoint
            try {
                return call_user_func($callback, ...$callback_params);
            } catch (\Throwable $th) {
                return $this->badRequest($th->getMessage());
            }
        }

        public function get_callback_parameters($callback): array {
            if (is_string($callback)) {
                $reflection = new \ReflectionClass($callback);
                $constructor = $reflection->getConstructor();
                return $constructor ? $constructor->getParameters() : [];
            } elseif (is_array($callback)) {
                $reflection = new \ReflectionMethod($callback[0], $callback[1]);
            } else {
                $reflection = new \ReflectionFunction($callback);
            }
        
            return $reflection->getParameters();
        }

        public function get_dependencies($params){
            $result = [];
            $services = Application::$app->services;
            $request_params = $this->request->getParams();

            foreach ($params as $param) {
                $p_name = $param->getName();
                
                if($services->serviceExists($p_name)){
                    $result[] = $services->getService($p_name);
                }

                if(array_key_exists($p_name, $request_params)){
                    $result[] =  $request_params[$p_name];
                }

                if(array_key_exists($p_name, $this->route_params)){
                    $result[] =  $this->route_params[$p_name];
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
            if(empty($layoutContent))
                return $viewContent;
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

        private function badRequest($msg = null){
            return $this->response->badRequest(
                "<h1>Bad Requset</h1>".
                ($msg ? "<b>Error:</b><p>$msg</p>" : "")
            );
        }

        private function notFound($msg = null){
            return $this->response->notFound(
                "<h1>Not Found</h1>".
                ($msg ? "<b>Error:</b><p>$msg</p>" : "")
            );
        }
    }
?>