<?php
    namespace DafCore;
    
    class ApiController extends ControllerBase{
                
        public function ok($obj = null){
            return Application::$app->response->ok($obj);
        }
                
        public function created($obj = null){
            return Application::$app->response->created($obj);
        }
                
        public function noContent(){
            return Application::$app->response->noContent();
        }
                
        public function badRequset($msg = null){
            return Application::$app->response->badRequest($msg);
        }

        public function notFound($msg = null){
            return Application::$app->response->notFound($msg);
        }
    }
?>