<?php
    namespace _Frm_core;
    
    class Controller{
        private $layout = "main";

        public function getLayout(){
            return $this->layout;
        }

        protected function setLayout($layout){
            $this->layout = $layout;
        }

        public function view($view, $params = []){
            $viewFolder = lcfirst(str_replace("Controller","", explode_class_name(get_called_class())));
            return Application::$app->router->renderView($viewFolder. '/' .$view, $params);
        }
        public function renderView($view, $params = []){
            return Application::$app->router->renderView($view, $params);
        }
        public function redirect($location = ""){
            header('Location: ' . Application::$app->basePath . $location);
        }
        public function redirectBack(){
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
?>