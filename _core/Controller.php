<?php
    namespace DafCore;
    
    interface IController{
        function getLayout();
    }
    
    class ControllerBase implements IController{
        private $layout = "main";

        public function getLayout(){
            return $this->layout;
        }

        protected function setLayout($layout){
            $this->layout = $layout;
        }

        protected function redirect($location = ""){
            header('Location: ' . Application::$app->basePath . $location);
        }
        
        protected function redirectBack(){
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
    
    class Controller extends ControllerBase{
                 
        public function badRequset($msg = null, $view = "_400"){
            Application::$app->response->setStatus(400);
            if(!empty($view))
                return Application::$app->router->getViewContent($view, ["msg"=>$msg]);

            return Application::$app->response->badRequest($msg);
        }

        public function notFound($msg = null, $view = "_404"){
            Application::$app->response->setStatus(404);
            if(!empty($view))
                return Application::$app->router->getViewContent($view, ["msg"=>$msg]);

            return Application::$app->response->notFound($msg);
        }

        public function view($view, $params = []){
            $viewFolder = lcfirst(str_replace("Controller","", explode_class_name(get_called_class())));
            return Application::$app->router->renderView($viewFolder. '/' .$view, $params);
        }

        public function renderView($view, $params = []){
            return Application::$app->router->renderView($view, $params);
        }

    }
?>