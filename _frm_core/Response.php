<?php 
namespace _Frm_core;

class Response {
    public function setStatusCode($code){
        http_response_code($code);
    }
    public function status($code){
        http_response_code($code);
        return $this;
    }
    public function send($obj = ""){
        return $obj;
    }
    public function json_stringify($obj){
        try
        {
            return json_encode($obj, JSON_THROW_ON_ERROR);
        }
        catch (\Throwable $e)
        {
            return "Throwable on json stringify: " . $e->getMessage() . PHP_EOL;
        }
    }
}
?>